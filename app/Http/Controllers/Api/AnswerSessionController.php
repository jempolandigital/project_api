<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\AnswerSession;
// use Illuminate\Support\Facades\Auth;
// use Carbon\Carbon;

// class AnswerSessionController extends Controller
// {
//     /**
//      * Start a new session or return existing one if already started today.
//      */
//     public function store(Request $request)
//     {
//         $request->validate([
//             'modul_id' => 'required|exists:moduls,id',
//             'tenant_id' => 'required|exists:tenants,id'
//         ]);

//         $user = $request->user(); // via Sanctum

//         // Cek session hari ini untuk user dan modul yang sama
//         $existingSession = AnswerSession::where('user_id', $user->id)
//             ->where('modul_id', $request->modul_id)
//             ->whereDate('created_at', Carbon::today())
//             ->first();

//         if ($existingSession) {
//             return response()->json([
//                 'message' => 'Session already exists for today',
//                 'session_id' => $existingSession->id
//             ], 200);
//         }

//         // Buat session baru
//         $newSession = AnswerSession::create([
//             'user_id' => $user->id,
//             'tenant_id' => $request->tenant_id,
//             'modul_id' => $request->modul_id,
//             'started_at' => now(),
//         ]);

//         return response()->json([
//             'message' => 'Session created',
//             'session_id' => $newSession->id
//         ], 201);
//     }

//     /**
//      * Optional: list session milik user (history)
//      */
//     public function index(Request $request)
//     {
//         $user = $request->user();

//         $sessions = AnswerSession::where('user_id', $user->id)
//             ->orderBy('created_at', 'desc')
//             ->get();

//         return response()->json($sessions);
//     }

//     /**
//      * Optional: get detail of a specific session
//      */
//     public function show($id)
//     {
//         $session = AnswerSession::findOrFail($id);

//         return response()->json($session);
//     }
// }



namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AnswerSession;

class AnswerSessionController extends Controller
{
    /**
     * Selalu buat session baru untuk user, tenant, dan modul
     */
    public function store(Request $request)
    {
        $request->validate([
            'modul_id'  => 'required|exists:moduls,id',
            'tenant_id' => 'required|exists:tenants,id'
        ]);

        $user = $request->user(); // via Sanctum

        // 🚀 Selalu buat session baru
        $newSession = AnswerSession::create([
            'user_id'    => $user->id,
            'tenant_id'  => $request->tenant_id,
            'modul_id'   => $request->modul_id,
            'started_at' => now(),
        ]);

        return response()->json([
            'message'    => 'Session created',
            'session_id' => $newSession->id
        ], 201);
    }

    /**
     * History session milik user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $sessions = AnswerSession::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($sessions);
    }

    /**
     * Detail session
     */
    public function show($id)
    {
        $session = AnswerSession::findOrFail($id);

        return response()->json($session);
    }
}
