<?php
// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\UserDevice;

// class UserDeviceController extends Controller {
//   public function store(Request $request) {
//     $request->validate([
//         'fcm_token' => 'required|string',
//         'device_type' => 'nullable|string'
//     ]);

//     $user = $request->user();

//     UserDevice::updateOrCreate(
//         [
//             'user_id'   => $user->id,
//             'fcm_token' => $request->fcm_token
//         ],
//         [
//             'device_type'  => $request->device_type,
//             'last_used_at' => now()
//         ]
//     );

//     return response()->json(['ok' => true]);
//   }
// }


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserDevice;

class UserDeviceController extends Controller
{
    // ✅ Simpan device token saat login
    public function store(Request $request)
    {
        $request->validate([
            'fcm_token'  => 'required|string',
            'device_type'=> 'nullable|string',
        ]);

        $user = $request->user();

        UserDevice::create([
            'user_id'     => $user->id,
            'fcm_token'   => $request->fcm_token,
            'device_type' => $request->device_type,
            'last_used_at'=> now()
        ]);

        return response()->json(['ok' => true]);
    }

    // ✅ Hapus device token saat logout
    public function destroy(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();

        UserDevice::where('user_id', $user->id)
            ->where('fcm_token', $request->fcm_token)
            ->delete();

        return response()->json(['ok' => true]);
    }
}
