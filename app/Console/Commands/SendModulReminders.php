<?php
// app/Console/Commands/SendModulReminders.php
namespace App\Console\Commands;

use App\Models\UserDevice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\FcmToken;
use App\Services\FcmService;

class SendModulReminders extends Command
{
    protected $signature = 'notif:modul-reminders';
    protected $description = 'Send FCM when modul opens and 15 minutes before close';

    public function handle()
    {
        // Pastikan zona waktu sesuai kebutuhan
        date_default_timezone_set('Asia/Jakarta');

        $now     = now();
        $today   = $now->toDateString();     // contoh: 2025-08-18
        $openHm  = $now->format('H:i');      // menit sekarang
        $closeHm = $now->copy()->addMinutes(15)->format('H:i'); // 15 menit dari sekarang

        // === Ambil modul yang open persis di menit ini (HH:mm) ===
        $openModuls = DB::table('moduls')
            ->whereRaw("TIME_FORMAT(open_at, '%H:%i') = ?", [$openHm])
            ->get();

        // === Ambil modul yang akan close 15 menit lagi (HH:mm) ===
        $closingModuls = DB::table('moduls')
            ->whereRaw("TIME_FORMAT(closed_at, '%H:%i') = ?", [$closeHm])
            ->get();

        // Kirim notif "dibuka" (satu kali per modul per hari per menit)
        foreach ($openModuls as $m) {
            // key cache berisi modulId + tanggal + jam:menit → “cek hari ini”
            $cacheKey = "reminder:open:{$m->id}:{$today}:{$openHm}";
            // Cache::add -> hanya set kalau belum ada (mencegah dobel kirim)
            if (Cache::add($cacheKey, 1, now()->addMinutes(10))) {
                $this->notifyTenantUsers(
                    $m->id,
                    "Modul Dibuka",
                    "Modul {$m->name} sudah bisa diisi sekarang.",
                    ['type' => 'modul_open', 'modul_id' => $m->id]
                );
                Log::info("FCM OPEN sent", ['modul_id' => $m->id, 'time' => $openHm, 'date' => $today]);
                $this->info("Kirim notif OPEN untuk modul {$m->name}");
            } else {
                // Sudah pernah dikirim di menit/tanggal ini → skip
                $this->info("Skip OPEN (sudah terkirim hari ini) modul {$m->name}");
            }
        }

        // Kirim notif "segera tutup" (satu kali per modul per hari per menit)
        foreach ($closingModuls as $m) {
            $cacheKey = "reminder:closeSoon:{$m->id}:{$today}:{$closeHm}";
            if (Cache::add($cacheKey, 1, now()->addMinutes(20))) {
                $this->notifyTenantUsers(
                    $m->id,
                    "Modul Segera Ditutup",
                    "Modul {$m->name} akan tutup 15 menit lagi.",
                    ['type' => 'modul_close_soon', 'modul_id' => $m->id]
                );
                Log::info("FCM CLOSE_SOON sent", ['modul_id' => $m->id, 'time' => $closeHm, 'date' => $today]);
                $this->info("Kirim notif CLOSE SOON untuk modul {$m->name}");
            } else {
                $this->info("Skip CLOSE SOON (sudah terkirim hari ini) modul {$m->name}");
            }
        }

        $this->info("Sekarang jam {$openHm}, cek modul open & closing selesai.");
        return 0; // sukses
    }

    /**
     * Ambil user yang terkait modul → ambil token FCM → kirim via FcmService
     */

    private function notifyTenantUsers(int $modulId, string $title, string $body, array $data)
{
    // Ambil semua user_id yang terkait dengan modul ini lewat tenant
    $userIds = DB::table('moduls')
        ->join('questionnaire_mappings', 'questionnaire_mappings.modul_id', '=', 'moduls.id')
        ->join('tenant_mapping', 'tenant_mapping.tenant_id', '=', 'questionnaire_mappings.tenant_id')
        ->where('moduls.id', $modulId)
        ->pluck('tenant_mapping.user_id')
        ->unique()
        ->values();

    if ($userIds->isEmpty()) {
        $this->info("Tidak ada user untuk modul {$modulId}");
        return;
    }

    $tokens = UserDevice::whereIn('user_id', $userIds)
        ->pluck('fcm_token')
        ->filter()     // buang null/empty
        ->unique()
        ->values()
        ->all();

    if (empty($tokens)) {
        $this->info("Tidak ada FCM token untuk modul {$modulId}");
        return;
    }

    // Kirim FCM ke banyak token
    FcmService::sendToTokens($tokens, $title, $body, $data);
}

}
