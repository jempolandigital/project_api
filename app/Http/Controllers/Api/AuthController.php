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
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (!Auth::attempt($credentials)) {
        throw ValidationException::withMessages([
            'email' => ['Email atau password salah.'],
        ]);
    }

    $user = Auth::user();
    $token = $user->createToken('mobile_token')->plainTextToken;

    // Ambil tenant_id dari relasi tenantMappings
    $tenantId = $user->tenantMappings()->first()?->tenant_id;

    return response()->json([
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'tenant_id' => $tenantId, // tambahkan ini
        ],
        'token' => $token,
    ]);
}
}