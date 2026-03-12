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
    
    /**
     * Fungsi store: Digunakan siswa untuk mengirim form izin/sakit.
     */
    public function store(Request $request) {
        // Validasi: Memastikan jenis izin sesuai kategori dan file bukti adalah gambar maksimal 2MB.
        $request->validate([
            'jenis_izin' => 'required|in:sakit,izin,dispen',
            'bukti' => 'required|image|max:2048',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Keamanan: Memastikan hanya user dengan role 'siswa' yang punya profil siswa yang bisa mengunggah.
        if (!$user || $user->role !== 'siswa' || !$user->siswa) {
            return response()->json(['message' => 'Hanya siswa yang dapat mengunggah izin'], 403);
        }

        // Mencari Wali Kelas siswa tersebut melalui tabel pivot 'anggota_kelas'.
        // Tujuannya agar izin ini otomatis "terkirim" ke akun Wali Kelas yang bersangkutan.
        $dataKelas = DB::table('anggota_kelas')
            ->join('kelas', 'anggota_kelas.kelas_id', '=', 'kelas.id')
            ->where('anggota_kelas.siswa_id', $user->siswa->id)
            ->select('kelas.wali_kelas_id')
            ->first();

        // Jika siswa belum dimasukkan ke kelas manapun, pengajuan izin ditolak sistem.
        if (!$dataKelas) {
            return response()->json(['message' => 'Siswa belum terdaftar di kelas manapun'], 404);
        }

        // ID Wali Kelas disimpan sebagai 'validator_id'.
        $validatorId = $dataKelas->wali_kelas_id;

        // Menyimpan file gambar bukti ke dalam folder 'storage/app/public/perizinan'.
        $path = $request->file('bukti')->store('perizinan', 'public');

        // Membuat record perizinan baru dengan status awal 'pending'.
        $izin = Perizinan::create([
            'siswa_id' => $user->siswa->id,
            'tgl_izin' => now(), // Tanggal izin adalah hari ini.
            'jenis_izin' => $request->jenis_izin,
            'alasan' => $request->alasan ?? '-',
            'bukti_gambar' => $path,
            'validator_id' => $validatorId, 
            'status_izin' => 'pending'
        ]);

        return response()->json(['status' => 'success', 'data' => $izin]);
    }

    /**
     * Fungsi validateIzin: Digunakan Guru/Wali Kelas untuk menyetujui atau menolak izin.
     */
    public function validateIzin(Request $request, $id) {
        $izin = Perizinan::findOrFail($id);
        $request->validate(['status' => 'required|in:disetujui,ditolak']);

        // Menggunakan Database Transaction agar jika salah satu proses gagal, data tidak berantakan.
        DB::beginTransaction();
        try {
            // Memperbarui status izin (disetujui/ditolak).
            $izin->update(['status_izin' => $request->status]);

            // LOGIKA OTOMATISASI: Jika izin disetujui, sistem akan mengisi daftar hadir siswa secara otomatis.
            if ($request->status === 'disetujui') {
                // 1. Mencari ID Kelas siswa yang bersangkutan.
                $kelasId = DB::table('anggota_kelas')
                    ->where('siswa_id', $izin->siswa_id)
                    ->value('kelas_id');

                if ($kelasId) {
                    // 2. Mencari semua sesi absensi (Mapel) yang sudah dibuka guru-guru lain di kelas tersebut hari ini.
                    $sessions = \App\Models\SesiPresensi::where('tanggal', $izin->tgl_izin)
                        ->whereHas('jadwal', function($q) use ($kelasId) {
                            $q->where('kelas_id', $kelasId);
                        })->get();

                    // 3. Untuk setiap sesi yang ditemukan, masukkan status 'izin' atau 'sakit' ke tabel Absensi.
                    foreach ($sessions as $sesi) {
                        \App\Models\Absensi::updateOrCreate(
                            [
                                'sesi_id'  => $sesi->id,
                                'siswa_id' => $izin->siswa_id,
                            ],
                            [
                                'waktu_scan' => now(), 
                                'status'     => ($izin->jenis_izin === 'sakit') ? 'sakit' : 'izin',
                                'is_valid'   => true, // Ditandai valid karena sudah divalidasi Wali Kelas.
                            ]
                        );
                    }
                }
            }

            // Jika semua proses update sukses, simpan perubahan ke database.
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Izin divalidasi dan absensi diperbarui']);
        } catch (\Exception $e) {
            // Jika terjadi error (misal koneksi database putus), batalkan semua perubahan di atas.
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Fungsi index: Digunakan Guru untuk melihat daftar pengajuan izin dari muridnya.
     */
    public function index() {
        $user = Auth::user();
        // Hanya guru yang punya relasi data guru yang bisa melihat daftar ini.
        if (!$user || $user->role !== 'guru' || !$user->guru) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        // Mengambil data perizinan yang ditujukan khusus untuk guru yang login (sebagai validator/wali kelas).
        $perizinan = Perizinan::with('siswa')
            ->where('validator_id', $user->guru->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $perizinan]);
    }
}