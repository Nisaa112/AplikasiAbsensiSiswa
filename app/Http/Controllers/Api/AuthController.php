<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, DB};

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string',
            'password' => 'required|string',
            'device_id' => 'required|string'
        ]);

        $credentials = $request->only('serial_number', 'password');
        
        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['status' => 'error', 'message' => 'Kredensial salah'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Logika Penguncian Device ID untuk Siswa
        if ($user->role === 'siswa') {
            if (is_null($user->device_id)) {
                $user->update(['device_id' => $request->device_id]);
            } elseif ($user->device_id !== $request->device_id) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Akun ini sudah terikat pada perangkat lain!'
                ], 403);
            }
        }

        return response()->json([
            'status' => 'success',
            'user' => $user->load($user->role === 'guru' ? 'guru' : 'siswa'),
            'authorization' => [
                'token' => $token,
                'type' => 'bearer'
            ]
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['status' => 'success', 'message' => 'Berhasil logout']);
    }
}