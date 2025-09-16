<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
//     public function login(Request $request)
//     {
//         $credentials = $request->validate([
//             'email'    => ['required', 'email'],
//             'password' => ['required'],
//         ]);

//         if (!Auth::attempt($credentials)) {
//             throw ValidationException::withMessages([
//                 'email' => ['Email atau password salah.'],
//             ]);
//         }

//         $user = Auth::user();
//         $token = $user->createToken('mobile_token')->plainTextToken;

//         return response()->json([
//             'user'  => $user,
//             'token' => $token,
//         ]);
//     }
// }


public function login(Request $request)
{
    try {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::guard('web')->attempt($credentials)) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        $user = Auth::guard('web')->user();
        $token = $user->createToken('mobile_token')->plainTextToken;

        $tenantId = $user->tenantMappings()->first()?->tenant_id;

        return response()->json([
            'user' => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'tenant_id' => $tenantId,
                'roles'     => $user->getRoleNames(),
            ],
            'token' => $token,
        ]);
    } catch (\Throwable $e) {
        \Log::error('Login mobile error: '.$e->getMessage());
        return response()->json([
            'message' => 'Internal server error',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}