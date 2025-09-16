<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenantMapping;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;



class TenantController extends Controller
{
    // public function myTenant(Request $request)
    // {
    //     $user = $request->user();

    //     // Ambil semua tenant yang dimiliki user
    //     $tenantMappings = TenantMapping::with('tenant')
    //         ->where('user_id', $user->id)
    //         ->get();

    //     if ($tenantMappings->isEmpty()) {
    //         return response()->json(['message' => 'Tidak ada tenant ditemukan untuk user ini'], 404);
    //     }

    //     // Ubah ke bentuk array JSON
    //     $tenants = $tenantMappings->map(function ($mapping) {
    //         return [
    //             'tenant_id' => $mapping->tenant_id,
    //             'tenant'    => $mapping->tenant,
    //         ];
    //     });

    //     return response()->json([
    //         'tenants' => $tenants,
    //     ]);
    // }

    // public function getTenant(Request $request)
    // {
    //     try {
    //         $user = $request->user();

    //         // Ambil semua mapping, bukan cuma satu
    //         $mappings = TenantMapping::where('user_id', $user->id)->with('tenant')->get();

    //         if ($mappings->isEmpty()) {
    //             return response()->json(['message' => 'Tenant tidak ditemukan untuk user ini'], 404);
    //         }

    //         // Map ke array
    //         $tenants = $mappings->map(function ($mapping) {
    //             return [
    //                 'tenant_id' => $mapping->tenant_id,
    //                 'tenant'    => $mapping->tenant,
    //             ];
    //         });

    //         return response()->json([
    //             'tenants' => $tenants,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Internal Server Error',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function myTenant(Request $request)
    {
        $user = $request->user();
        $tenants = collect();

        if ($user->hasRole('spv')) {
            // SPV: ambil semua tenant
            $tenants = Tenant::all()->map(fn($tenant) => [
                'tenant_id' => $tenant->id,
                'name'      => $tenant->name,
                'address'   => $tenant->address,
                'city'      => $tenant->city,
                'province'  => $tenant->province,
                'latitude'  => $tenant->latitude,
                'longitude' => $tenant->longitude,
                'radius'    => $tenant->radius,
            ]);

            Log::info('SPV Tenant List', [
                'user_id' => $user->id,
                'roles' => $user->getRoleNames(),
                'tenant_ids' => $tenants->pluck('tenant_id')->toArray()
            ]);
        } else {
            // User biasa: ambil tenant dari mapping
            $tenantMappings = TenantMapping::with('tenant')
                ->where('user_id', $user->id)
                ->get();

            if ($tenantMappings->isEmpty()) {
                Log::info('No Tenant Mapping', [
                    'user_id' => $user->id,
                    'roles' => $user->getRoleNames()
                ]);

                return response()->json([
                    'message' => 'Tidak ada tenant ditemukan untuk user ini'
                ], 404);
            }

            $tenants = $tenantMappings->map(fn($mapping) => [
                'tenant_id' => $mapping->tenant_id,
                'name'      => $mapping->tenant->name,
                'address'   => $mapping->tenant->address,
                'city'      => $mapping->tenant->city,
                'province'  => $mapping->tenant->province,
                'latitude'  => $mapping->tenant->latitude,
                'longitude' => $mapping->tenant->longitude,
                'radius'    => $mapping->tenant->radius,
            ]);

            Log::info('User Tenant List', [
                'user_id' => $user->id,
                'roles' => $user->getRoleNames(),
                'tenant_ids' => $tenants->pluck('tenant_id')->toArray()
            ]);
        }

        return response()->json(['tenants' => $tenants]);
    }
}

