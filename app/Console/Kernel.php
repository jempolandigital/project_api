<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SendModulReminders;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        SendModulReminders::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('notif:modul-reminders')->everyMinute();
        \Log::info('Schedule dipanggil dari Kernel.php dengan SendModulReminders');

        $schedule->command('test:schedule')->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
