<?php

namespace App\Http\Controllers\Api;


use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantMapping;
use App\Models\QuestionnaireMapping;
use App\Models\AnswerSession;


class UserProgressController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Ambil tenant-tenant milik user
        $tenantIds = $user->tenantMappings()->pluck('tenant_id');
        \Log::info('Tenant IDs:', $tenantIds->toArray());

        // Ambil modul-modul yang di-assign ke tenant tersebut
        $moduleIds = QuestionnaireMapping::whereIn('tenant_id', $tenantIds)->pluck('modul_id');
        \Log::info('Modul IDs:', $moduleIds->toArray());

        $totalModules = $moduleIds->count();

        // Ambil modul yang sudah disubmit oleh user HARI INI
        $today = Carbon::today();

        $answeredModules = AnswerSession::where('user_id', $user->id)
            ->whereIn('modul_id', $moduleIds)
            ->whereNotNull('submitted_at')
            ->whereDate('submitted_at', $today) // hanya yang disubmit hari ini
            ->distinct('modul_id')
            ->pluck('modul_id');

        $completedModules = $answeredModules->count();

        $percentage = $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0;

        return response()->json([
            'completed' => $completedModules,
            'total' => $totalModules,
            'percentage' => $percentage
        ]);
    }
}

