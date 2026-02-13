<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Perizinan;
use App\Models\Kelas;
use App\Models\SesiPresensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 

class PermissionController extends Controller {
    
    public function store(Request $request) {
        $request->validate([
            'jenis_izin' => 'required|in:sakit,izin,dispen',
            'bukti' => 'required|image|max:2048',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || $user->role !== 'siswa' || !$user->siswa) {
            return response()->json(['message' => 'Hanya siswa yang dapat mengunggah izin'], 403);
        }

        $dataKelas = DB::table('anggota_kelas')
            ->join('kelas', 'anggota_kelas.kelas_id', '=', 'kelas.id')
            ->where('anggota_kelas.siswa_id', $user->siswa->id)
            ->select('kelas.wali_kelas_id')
            ->first();

        if (!$dataKelas) {
            return response()->json(['message' => 'Siswa belum terdaftar di kelas manapun'], 404);
        }

        $validatorId = $dataKelas->wali_kelas_id;

        $path = $request->file('bukti')->store('perizinan', 'public');

        $izin = Perizinan::create([
            'siswa_id' => $user->siswa->id,
            'tgl_izin' => now(),
            'jenis_izin' => $request->jenis_izin,
            'alasan' => $request->alasan ?? '-',
            'bukti_gambar' => $path,
            'validator_id' => $validatorId, 
            'status_izin' => 'pending'
        ]);

        return response()->json(['status' => 'success', 'data' => $izin]);
    }

    public function validateIzin(Request $request, $id) {
        $user = Auth::user();

        if (!$user || $user->role !== 'guru' || !$user->guru) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $izin = Perizinan::findOrFail($id);
        
        if ($izin->validator_id != $user->guru->id) {
            return response()->json(['message' => 'Anda bukan wali kelas siswa ini'], 403);
        }

        $request->validate(['status' => 'required|in:disetujui,ditolak']);

        DB::beginTransaction();
        try {
            $izin->update(['status_izin' => $request->status]);

            if ($request->status === 'disetujui') {
                
                // Cari Kelas ID siswa tersebut
                $kelasId = DB::table('anggota_kelas')
                    ->where('siswa_id', $izin->siswa_id)
                    ->value('kelas_id');

                if ($kelasId) {
                    // Cari semua sesi presensi untuk kelas tersebut pada tanggal perizinan
                    $sesiHariIni = SesiPresensi::where('tanggal', $izin->tgl_izin)
                        ->whereHas('jadwal', function($q) use ($kelasId) {
                            $q->where('kelas_id', $kelasId);
                        })->get();

                    foreach ($sesiHariIni as $sesi) {
                        // Update atau Create: Jika sudah ada (mungkin siswa sempat scan lalu sakit), 
                        // kita timpa statusnya menjadi Izin/Sakit sesuai surat.
                        Absensi::updateOrCreate(
                            [
                                'sesi_id'  => $sesi->id,
                                'siswa_id' => $izin->siswa_id,
                            ],
                            [
                                'waktu_scan' => now(),
                                'status'     => ($izin->jenis_izin === 'sakit') ? 'sakit' : 'izin',
                                'lat_siswa'  => null,
                                'long_siswa' => null,
                                'is_valid'   => true, // Valid karena diverifikasi Wali Kelas
                            ]
                        );
                    }
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success', 
                'message' => 'Status izin diperbarui dan data absensi telah disinkronkan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => 'Gagal memproses data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index() {
        $user = Auth::user();
        if (!$user || $user->role !== 'guru' || !$user->guru) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $perizinan = Perizinan::with('siswa')
            ->where('validator_id', $user->guru->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $perizinan]);
    }
}