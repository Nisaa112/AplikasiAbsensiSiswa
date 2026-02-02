<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Perizinan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller {
    public function store(Request $request) {
        $request->validate([
            'jenis_izin' => 'required|in:sakit,izin,dispen',
            'bukti' => 'required|image|max:2048',
            'validator_id' => 'required' 
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || $user->role !== 'siswa' || !$user->siswa) {
            return response()->json(['message' => 'Hanya siswa yang dapat mengunggah izin'], 403);
        }

        $path = $request->file('bukti')->store('perizinan', 'public');

        $izin = Perizinan::create([
            'siswa_id' => $user->siswa->id,
            'tgl_izin' => now(),
            'jenis_izin' => $request->jenis_izin,
            'alasan' => $request->alasan ?? '-',
            'bukti_gambar' => $path,
            'validator_id' => $request->validator_id,
            'status_izin' => 'pending'
        ]);

        return response()->json(['status' => 'success', 'data' => $izin]);
    }

    public function validateIzin(Request $request, $id) {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || $user->role !== 'guru' || !$user->guru) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $izin = Perizinan::findOrFail($id);
        
        if ($izin->validator_id != $user->guru->id) {
            return response()->json(['message' => 'Anda bukan validator izin ini'], 403);
        }

        $request->validate(['status' => 'required|in:disetujui,ditolak']);

        $izin->update(['status_izin' => $request->status]); 
        
        return response()->json(['status' => 'success', 'message' => 'Status diperbarui']);
    }
}