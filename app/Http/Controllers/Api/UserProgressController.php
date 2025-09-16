<?php

// namespace App\Http\Controllers\Api;


// use Carbon\Carbon;
// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\TenantMapping;
// use App\Models\QuestionnaireMapping;
// use App\Models\AnswerSession;


// class UserProgressController extends Controller
// {
//     public function index(Request $request)
//     {
//         $user = $request->user();

//         // Ambil tenant-tenant milik user
//         $tenantIds = $user->tenantMappings()->pluck('tenant_id');
//         \Log::info('Tenant IDs:', $tenantIds->toArray());

//         // Ambil modul-modul yang di-assign ke tenant tersebut
//        // $moduleIds = QuestionnaireMapping::whereIn('tenant_id', $tenantIds)->pluck('modul_id');
//         $moduleIds = QuestionnaireMapping::whereIn('tenant_id', $tenantIds)
//             ->distinct()
//             ->pluck('modul_id');
//         \Log::info('Modul IDs:', $moduleIds->toArray());

//         $totalModules = $moduleIds->count();

//         // Ambil modul yang sudah disubmit oleh user HARI INI
//         $today = Carbon::today();

//         $answeredModules = AnswerSession::where('user_id', $user->id)
//             ->whereIn('modul_id', $moduleIds)
//             ->whereNotNull('submitted_at')
//             ->whereDate('submitted_at', $today) // hanya yang disubmit hari ini
//             ->distinct('modul_id')
//             ->pluck('modul_id');

//         $completedModules = $answeredModules->count();

//         $percentage = $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0;

//         return response()->json([
//             'completed' => $completedModules,
//             'total' => $totalModules,
//             'percentage' => $percentage
//         ]);
//     }
// }

// // ini spv masih kosong
// namespace App\Http\Controllers\Api;

// use Carbon\Carbon;
// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\TenantMapping;
// use App\Models\QuestionnaireMapping;
// use App\Models\AnswerSession;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;

// class UserProgressController extends Controller
// {
//     public function index(Request $request)
//     {
//         $user = $request->user();
//         $userRole = $user->getRoleNames()->first(); // cek role user

//         // Ambil tenant-tenant milik user
//         $tenantIds = $user->tenantMappings()->pluck('tenant_id');
//         Log::info('Tenant IDs:', $tenantIds->toArray());

//         // Ambil modul sesuai tenant + filter is_spv
//         $moduleIds = QuestionnaireMapping::whereIn('tenant_id', $tenantIds)
//             ->join('moduls', 'questionnaire_mappings.modul_id', '=', 'moduls.id')
//             ->when($userRole === 'spv', function ($query) {
//                 $query->where('moduls.is_spv', 0);
//             })
//             ->when($userRole !== 'spv', function ($query) {
//                 $query->where('moduls.is_spv', 1);
//             })
//             ->pluck('modul_id')
//             ->unique();

//         Log::info('Filtered Modul IDs:', $moduleIds->toArray());

//         $totalModules = $moduleIds->count();

//         // Ambil modul yang sudah disubmit oleh user HARI INI
//         $today = Carbon::today();

//         $answeredModules = AnswerSession::where('user_id', $user->id)
//             ->whereIn('modul_id', $moduleIds)
//             ->whereNotNull('submitted_at')
//             ->whereDate('submitted_at', $today)
//             ->distinct('modul_id')
//             ->pluck('modul_id');

//         $completedModules = $answeredModules->count();

//         $percentage = $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0;

//         // 🔥 Log hasil progress
//         Log::info('User Progress', [
//             'user_id' => $user->id,
//             'role' => $userRole,
//             'totalModules' => $totalModules,
//             'completedModules' => $completedModules,
//             'percentage' => $percentage,
//         ]);

//         return response()->json([
//             'completed' => $completedModules,
//             'total' => $totalModules,
//             'percentage' => $percentage
//         ]);
//     }
// }

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Log;
// use App\Models\TenantMapping;
// use App\Models\QuestionnaireMapping;
// use App\Models\AnswerSession;
// use Illuminate\Support\Facades\Auth;

// class UserProgressController extends Controller
// {
//     /**
//      * Ambil progres user berdasarkan modul di tenant
//      */
//     public function getProgress(Request $request)
//     {
//         $user = Auth::user();
//         $userId = $user->id;

//         // Ambil semua tenant yang dimiliki user
//         $tenantIds = TenantMapping::where('user_id', $userId)->pluck('tenant_id')->toArray();

//         Log::info('USER MODUL FETCH', [
//             'user_id'   => $userId,
//             'roles'     => $user->getRoleNames(),
//             'tenant_id' => $tenantIds,
//         ]);

//         if (empty($tenantIds)) {
//             return response()->json([
//                 'completed' => 0,
//                 'total'     => 0,
//             ]);
//         }

//         // Ambil modul-modul dari tenant (mapping modul <-> tenant)
//         $query = QuestionnaireMapping::whereIn('tenant_id', $tenantIds)
//             ->join('moduls', 'questionnaire_mappings.modul_id', '=', 'moduls.id');

//         // Filter modul sesuai role
//         if ($user->hasRole('spv')) {
//             $query->where('moduls.is_spv', '1');
//             $roleType = 'spv';
//         } else {
//             $query->where('moduls.is_spv', 0);
//             $roleType = 'user';
//         }

//         $moduleIds = $query->distinct()->pluck('modul_id');

//         Log::info('USER PROGRESS MODULE IDS', [
//             'user_id'   => $userId,
//             'role_type' => $roleType,
//             'modul_ids' => $moduleIds,
//         ]);

//         // Hitung total modul
//         $total = $moduleIds->count();

//         // Hitung modul yang sudah dikerjakan (ada AnswerSession submitted)
//         $completed = AnswerSession::where('user_id', $userId)
//             ->whereIn('modul_id', $moduleIds)
//             ->whereNotNull('submitted_at')
//             ->distinct('modul_id')
//             ->count('modul_id');

//         return response()->json([
//             'completed' => $completed,
//             'total'     => $total,
//         ]);
//     }
// }





// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\Modul;
// use App\Models\TenantMapping;
// use App\Models\AnswerSession;
// use Carbon\Carbon;
// use Illuminate\Support\Facades\Log;

// class UserProgressController extends Controller
// {
//     public function index(Request $request)
//     {
//         $user = $request->user();
//         $role = $user->getRoleNames()->first();
//         $today = Carbon::today();

//         $tenantIds = collect();
//         $moduleIds = collect();
//         $completed = 0;
//         $totalModules = 0;

//         if ($role === 'spv') {
//             // ✅ SPV → total semua modul SPV
//             $moduleIds = Modul::where('is_spv', "0")->pluck('id');
//             $totalModules = $moduleIds->count();

//             $completed = AnswerSession::where('user_id', $user->id)
//                 ->whereIn('modul_id', $moduleIds)
//                 ->whereNotNull('submitted_at')
//                 ->whereDate('submitted_at', $today)
//                 ->distinct('modul_id')
//                 ->count('modul_id');
//         } else {
//             // ✅ User biasa → modul di tenant dia dengan is_spv=0
//             $tenantIds = TenantMapping::where('user_id', $user->id)->pluck('tenant_id');
//             $moduleIds = Modul::whereIn('tenant_id', $tenantIds)
//                 ->where('is_spv', "1")
//                 ->pluck('id');
//             $totalModules = $moduleIds->count();

//             $completed = AnswerSession::where('user_id', $user->id)
//                 ->whereIn('modul_id', $moduleIds)
//                 ->whereNotNull('submitted_at')
//                 ->whereDate('submitted_at', $today)
//                 ->distinct('modul_id')
//                 ->count('modul_id');
//         }

//         $percentage = $totalModules > 0 ? round(($completed / $totalModules) * 100) : 0;

//         // ✅ Logging detail progress
//         Log::info('USER PROGRESS FETCH', [
//             'user_id'   => $user->id,
//             'role'      => $role,
//             'tenant_ids'=> $tenantIds,
//             'module_ids'=> $moduleIds,
//             'completed' => $completed,
//             'total'     => $totalModules,
//             'percentage'=> $percentage,
//         ]);

//         return response()->json([
//             'completed'  => $completed,
//             'total'      => $totalModules,
//             'percentage' => $percentage
//         ]);
//     }
// }

// cuman masalah di spv masih 0/1
// namespace App\Http\Controllers\Api;

// use Carbon\Carbon;
// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\TenantMapping;
// use App\Models\QuestionnaireMapping;
// use App\Models\AnswerSession;
// use App\Models\Modul;
// use Illuminate\Support\Facades\Log;

// class UserProgressController extends Controller
// {
//     public function index(Request $request)
//     {
//         $user = $request->user();
//         $roles = $user->roles()->pluck('name')->toArray();

//         Log::info('USER PROGRESS START', [
//             'user_id' => $user->id,
//             'roles'   => $roles,
//         ]);

//         $today = Carbon::today();
//         $moduleIds = collect();

//         if (in_array('spv', $roles)) {
//             // 🔹 SPV → semua modul is_spv=1
//             $moduleIds = Modul::where('is_spv', '1')->pluck('id');
//             Log::info('SPV Modul IDs', $moduleIds->toArray());
//         } else {
//             // 🔹 Outlet → tenant_mapping → questionnaire_mappings → modul is_spv=0
//             $tenantIds = TenantMapping::where('user_id', $user->id)->pluck('tenant_id');
//             Log::info('Tenant IDs', $tenantIds->toArray());

//             $moduleIds = QuestionnaireMapping::whereIn('tenant_id', $tenantIds)
//                 ->join('moduls', 'questionnaire_mappings.modul_id', '=', 'moduls.id')
//                 ->where('moduls.is_spv', '0')
//                 ->distinct()
//                 ->pluck('questionnaire_mappings.modul_id');
//             Log::info('Outlet Modul IDs', $moduleIds->toArray());
//         }

//         $totalModules = $moduleIds->count();

//         // 🔹 Hitung modul yang sudah disubmit user HARI INI
//         $answeredModules = AnswerSession::where('user_id', $user->id)
//             ->whereIn('modul_id', $moduleIds)
//             ->whereNotNull('submitted_at')
//             ->whereDate('submitted_at', $today)
//             ->distinct('modul_id')
//             ->pluck('modul_id');

//         $completedModules = $answeredModules->count();

//         $percentage = $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0;

//         Log::info('USER PROGRESS RESULT', [
//             'user_id'   => $user->id,
//             'role'      => implode(',', $roles),
//             'total'     => $totalModules,
//             'completed' => $completedModules,
//             'percent'   => $percentage,
//         ]);

//         return response()->json([
//             'completed'  => $completedModules,
//             'total'      => $totalModules,
//             'percentage' => $percentage,
//         ]);
//     }
// }



namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\TenantMapping;
use App\Models\QuestionnaireMapping;
use App\Models\AnswerSession;
use App\Models\Modul;
use Illuminate\Support\Facades\Log;

class UserProgressController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $roles = $user->roles()->pluck('name')->toArray();

        Log::info('USER PROGRESS START', [
            'user_id' => $user->id,
            'roles'   => $roles,
        ]);

        $today          = Carbon::today();
        $totalModules   = 0;
        $completed      = 0;

        /**
         * ==========================================
         *  SPV
         * ==========================================
         */
        if (in_array('spv', $roles)) {
            $tenantIds  = Tenant::pluck('id'); // semua tenant
            $moduleIds  = Modul::where('is_spv', '1')->pluck('id'); // modul khusus SPV

            Log::info('SPV Tenant IDs', $tenantIds->toArray());
            Log::info('SPV Modul IDs', $moduleIds->toArray());

            // total = tenant_count × modul_count
            $totalModules = $tenantIds->count() * $moduleIds->count();

            // hitung yang completed hari ini
            $completed = AnswerSession::where('user_id', $user->id)
                ->whereIn('tenant_id', $tenantIds)
                ->whereIn('modul_id', $moduleIds)
                ->whereNotNull('submitted_at')
                ->whereDate('submitted_at', $today)
                ->selectRaw('COUNT(DISTINCT CONCAT(modul_id, "-", tenant_id)) as total')
                ->value('total');

            Log::info('USER PROGRESS RESULT', [
                'user_id'   => $user->id,
                'role'      => 'spv',
                'total'     => $totalModules,
                'completed' => $completed,
                'percent'   => $totalModules > 0 ? round(($completed / $totalModules) * 100, 2) : 0,
            ]);
        }

        /**
         * ==========================================
         *  OUTLET
         * ==========================================
         */
        else {
            $tenantIds = TenantMapping::where('user_id', $user->id)->pluck('tenant_id');
            Log::info('Tenant IDs', $tenantIds->toArray());

            $moduleIds = QuestionnaireMapping::whereIn('tenant_id', $tenantIds)
                ->join('moduls', 'questionnaire_mappings.modul_id', '=', 'moduls.id')
                ->where('moduls.is_spv', '0')
                ->distinct()
                ->pluck('questionnaire_mappings.modul_id');

            Log::info('Outlet Modul IDs', $moduleIds->toArray());

            $totalModules = $moduleIds->count();

            $answeredModules = AnswerSession::where('user_id', $user->id)
                ->whereIn('tenant_id', $tenantIds)
                ->whereIn('modul_id', $moduleIds)
                ->whereNotNull('submitted_at')
                ->whereDate('submitted_at', $today)
                ->distinct('modul_id', 'tenant_id')
                ->pluck('modul_id');

            $completed = $answeredModules->count();

            Log::info('USER PROGRESS RESULT', [
                'user_id'   => $user->id,
                'role'      => 'Outlet',
                'total'     => $totalModules,
                'completed' => $completed,
                'percent'   => $totalModules > 0 ? round(($completed / $totalModules) * 100, 2) : 0,
            ]);
        }

        return response()->json([
            'completed'  => $completed,
            'total'      => $totalModules,
            'percentage' => $totalModules > 0 ? round(($completed / $totalModules) * 100, 2) : 0,
        ]);
    }
}
