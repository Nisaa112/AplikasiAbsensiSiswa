<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{SesiPresensi, Absensi};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class AttendanceController extends Controller {
    public function createSesi(Request $request) {
        // 1. Validasi input
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal,id'
        ]);

        $token = bin2hex(random_bytes(16));

        // 2. Simpan ke database
        $sesi = SesiPresensi::create([
            'jadwal_id' => $request->jadwal_id,
            'tanggal'   => now()->toDateString(),
            'token_qr'  => $token
        ]);

        // 3. Respon dibungkus dalam 'data' agar terbaca oleh ApiService Flutter
        return response()->json([
            'status' => 'success',
            'data' => [
                'status'   => 'success',
                'token_qr' => $sesi->token_qr
            ]
        ]);
    }

    public function scanQR(Request $request) {
        /** @var \App\Models\User $user */ 
        $user = Auth::user();

        if (!$user || $user->role !== 'siswa' || !$user->siswa) {
            return response()->json(['message' => 'Hanya siswa yang dapat melakukan absensi!'], 403);
        }

        // Cari sesi berdasarkan token
        $sesi = SesiPresensi::where('token_qr', $request->token_qr)->first();

        if (!$sesi) {
            return response()->json(['message' => 'QR Code tidak valid atau sudah kadaluarsa'], 404);
        }

        $lokasi = $sesi->jadwal->lokasi;

        $jarak = $this->haversine(
            $request->lat_siswa, 
            $request->long_siswa, 
            $lokasi->latitude, 
            $lokasi->longitude
        );
        
        $isValid = $jarak <= $lokasi->radius;

        // Simpan Absensi
        $absensi = Absensi::create([
            'sesi_id'    => $sesi->id,
            'siswa_id'   => $user->siswa->id,
            'waktu_scan' => now(), 
            'status'     => 'hadir',
            'is_valid'   => $isValid,
            'lat_siswa'  => $request->lat_siswa, 
            'long_siswa' => $request->long_siswa 
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'status'      => $isValid ? 'success' : 'error',
                'message'     => $isValid ? 'Absen Berhasil' : 'Anda di luar radius sekolah!',
                'jarak_meter' => round($jarak)
            ]
        ]);
    }

    private function haversine($lat1, $lon1, $lat2, $lon2) {
        $r = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2)**2;
        return $r * (2 * atan2(sqrt($a), sqrt(1-$a)));
    }

    public function historySiswa() {
        $user = Auth::user();
        
        $history = Absensi::with(['sesi.jadwal.mapel'])
            ->where('siswa_id', $user->siswa->id)
            ->orderBy('waktu_scan', 'desc')
            ->get();

        $summary = [
            'total_hadir' => $history->where('status', 'hadir')->count(),
            'total_izin'  => $history->where('status', 'izin')->count(),
            'total_invalid' => $history->where('is_valid', false)->count(),
        ];

        return response()->json([
            'status' => 'success',
            'summary' => $summary,
            'data' => $history
        ]);
    }
}