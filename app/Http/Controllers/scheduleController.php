<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller {
    public function mySchedule() {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        \Carbon\Carbon::setLocale('id');
        $hari = now()->translatedFormat('l'); 

        $query = Jadwal::with([
            'kelas:id,tingkat,jurusan,nomor_kelas', 
            'mapel:id,nama_mapel', 
            'lokasi:id,nama_lokasi,radius'
        ])->where('hari', $hari);

        if ($user->role === 'guru') {
            $data = $query->where('guru_id', $user->guru->id)->get();
        } else {
            $kelasId = $user->siswa->anggotaKelas->pluck('kelas_id'); 
            $data = $query->whereIn('kelas_id', $kelasId)->get();
        }

        return response()->json([
            'status' => 'success',
            'hari_ini' => $hari,
            'data' => $data
        ]);
    }
}