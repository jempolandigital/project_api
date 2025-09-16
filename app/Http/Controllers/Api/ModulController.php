<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\Tenant;

// class ModulController extends Controller
// {
//     public function getModulByTenant(Request $request, $tenantId)
//     {
//         $tenant = Tenant::with('moduls')->find($tenantId);

//         if (!$tenant) {
//             return response()->json(['message' => 'Tenant tidak ditemukan'], 404);
//         }

// //         return response()->json([
// //             'tenant_id' => $tenant->id,
// //             'moduls' => $tenant->moduls,
// //         ]);
// //     }
// // }

//  $user = $request->user(); // pastikan pakai Sanctum atau auth user

//     $moduls = $tenant->moduls->map(function ($modul) use ($user) {
//         $isFilledToday = \App\Models\AnswerSession::where('user_id', $user->id)
//             ->where('modul_id', $modul->id)
//             ->whereDate('submitted_at', now())
//             ->exists();

//         return [
//             'id' => $modul->id,
//             'name' => $modul->name,
//             'description' => $modul->description,
//             'is_filled_today' => $isFilledToday,
//         ];
//     });

//     return response()->json([
//         'tenant_id' => $tenant->id,
//         'moduls' => $moduls,
//     ]);
// }
// }



// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\Tenant;
// use App\Models\AnswerSession;

// class ModulController extends Controller
// {
//     public function getModulByTenant(Request $request, $tenantId)
//     {
//         $tenant = Tenant::with('moduls')->find($tenantId);

//         if (!$tenant) {
//             return response()->json(['message' => 'Tenant tidak ditemukan'], 404);
//         }

//         $user = $request->user(); // via Sanctum

//         $moduls = $tenant->moduls->map(function ($modul) use ($user,$tenant) {
//             $isFilledToday = AnswerSession::where('user_id', $user->id)
//                 ->where('modul_id', $modul->id)
//                 ->where('tenant_id', $tenant->id)
//                 ->whereDate('submitted_at', now())
//                 ->exists();

//             return [
//                 'id' => $modul->id,
//                 'name' => $modul->name,
//                 'description' => $modul->description,
//                 'open_at' => $modul->open_at,
//                 'closed_at' => $modul->closed_at,
//                 'is_filled_today' => $isFilledToday,
//                 'tenant' => [
//                     'latitude' => $tenant->latitude,
//                     'longitude' => $tenant->longitude,
//                     'radius' => $tenant->radius,
//                 ],
//             ];
//         });
        
//         return response()->json([
//             'tenant_id' => $tenant->id,
//             'moduls' => $moduls,
//         ]);
//     }
// }


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Modul;
use App\Models\AnswerSession;
use Illuminate\Support\Facades\Log;

class ModulController extends Controller
{
    public function getModulByTenant(Request $request, $tenantId)
    {
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return response()->json(['message' => 'Tenant tidak ditemukan'], 404);
        }

        $user = $request->user();
        $userRole = $user->getRoleNames()->first(); // role pertama

        if ($userRole === 'spv') {
            // ✅ Ambil modul SPV langsung dari tabel, pakai Eloquent biar properti aman
            $moduls = Modul::where('is_spv', '1')->get();

            Log::info('SPV MODUL FETCH', [
                'user_id'     => $user->id,
                'role'        => $userRole,
                'modul_ids'   => $moduls->pluck('id')->toArray(),
                'is_spv_flags'=> $moduls->pluck('is_spv')->toArray(),
            ]);
        } else {
            // ✅ User biasa → ambil modul dari tenant relasi
            $moduls = $tenant->moduls()
                ->where('is_spv', '0')
                ->get();

            Log::info('USER MODUL FETCH', [
                'user_id'     => $user->id,
                'role'        => $userRole,
                'tenant_id'   => $tenant->id,
                'modul_ids'   => $moduls->pluck('id')->toArray(),
                'is_spv_flags'=> $moduls->pluck('is_spv')->toArray(),
            ]);
        }

        // Mapping response
        $moduls = $moduls->map(function ($modul) use ($user, $tenant) {
            $isFilledToday = AnswerSession::where('user_id', $user->id)
                ->where('modul_id', $modul->id)
                ->where('tenant_id', $tenant->id)
                ->whereDate('submitted_at', now())
                ->exists();

            return [
                'id'             => $modul->id,
                'name'           => $modul->name,
                'description'    => $modul->description,
                'open_at'        => $modul->open_at,
                'closed_at'      => $modul->closed_at,
                'is_filled_today'=> $isFilledToday,
                'tenant'         => [
                    'latitude'  => $tenant->latitude,
                    'longitude' => $tenant->longitude,
                    'radius'    => $tenant->radius,
                ],
            ];
        });

        return response()->json([
            'tenant_id' => $tenant->id,
            'moduls'    => $moduls,
        ]);
    }
}
