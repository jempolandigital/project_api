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



namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\AnswerSession;

class ModulController extends Controller
{
    public function getModulByTenant(Request $request, $tenantId)
    {
        $tenant = Tenant::with('moduls')->find($tenantId);

        if (!$tenant) {
            return response()->json(['message' => 'Tenant tidak ditemukan'], 404);
        }

        $user = $request->user(); // via Sanctum

        $moduls = $tenant->moduls->map(function ($modul) use ($user,$tenantId) {
            $isFilledToday = AnswerSession::where('user_id', $user->id)
                ->where('modul_id', $modul->id)
                ->where('tenant_id', $tenantId)
                ->whereDate('submitted_at', now())
                ->exists();

            return [
                'id' => $modul->id,
                'name' => $modul->name,
                'description' => $modul->description,
                'open_at' => $modul->open_at,
                'closed_at' => $modul->closed_at,
                'is_filled_today' => $isFilledToday,
            ];
        });
        
        return response()->json([
            'tenant_id' => $tenant->id,
            'moduls' => $moduls,
        ]);
    }
}
