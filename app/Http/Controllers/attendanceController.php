<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{SesiPresensi, Absensi, Jadwal, Siswa};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller {
    public function createSesi(Request $request) {
        // Memastikan input 'jadwal_id' ada dan terdaftar di tabel jadwal
        $request->validate(['jadwal_id' => 'required|exists:jadwal,id']);
        
        // Mengambil data jadwal berdasarkan ID untuk mendapatkan info kelas dan lokasi
        $jadwal = Jadwal::find($request->jadwal_id);
        // Mengambil tanggal hari ini dalam format YYYY-MM-DD
        $today = now()->toDateString();

        // Membuat sesi baru atau memperbarui jika sudah ada untuk jadwal & tanggal yang sama
        // bin2hex(random_bytes) digunakan untuk membuat token unik sepanjang 32 karakter untuk QR Code
        $sesi = SesiPresensi::updateOrCreate(
            ['jadwal_id' => $request->jadwal_id, 'tanggal' => $today],
            ['token_qr'  => bin2hex(random_bytes(16))]
        );

        // --- LOGIKA OTOMATISASI IZIN ---
        // Mencari data di tabel perizinan yang tanggalnya hari ini dan statusnya sudah disetujui admin/guru
        $siswaIzin = \App\Models\Perizinan::where('tgl_izin', $today)
            ->where('status_izin', 'disetujui')
            // Memastikan siswa yang izin tersebut memang benar anggota kelas dari jadwal ini
            ->whereHas('siswa.anggotaKelas', function($q) use ($jadwal) {
                $q->where('kelas_id', $jadwal->kelas_id);
            })->get();

        // Melakukan loop untuk setiap siswa yang izinnya disetujui
        foreach ($siswaIzin as $izin) {
            // Memasukkan data mereka langsung ke tabel absensi (otomatis hadir via jalur izin)
            Absensi::updateOrCreate(
                ['sesi_id' => $sesi->id, 'siswa_id' => $izin->siswa_id],
                [
                    // Jika jenis izinnya 'sakit' maka status 'sakit', selain itu (izin biasa) maka status 'izin'
                    'status'   => ($izin->jenis_izin === 'sakit') ? 'sakit' : 'izin',
                    'is_valid' => true, // Dianggap valid karena sudah disetujui
                    'waktu_scan' => now() // Mencatat waktu pemrosesan sistem
                ]
            );
        }

        // Mengembalikan token QR dalam bentuk JSON untuk ditampilkan sebagai gambar QR di sisi Frontend
        return response()->json(['status' => 'success', 'data' => ['token_qr' => $sesi->token_qr]]);
    }

    public function scanQR(Request $request) {
        /** @var \App\Models\User $user */ 
        // Mengambil data user (siswa) yang sedang login/terautentikasi
        $user = Auth::user();

        // 1. Validasi QR: Mencari sesi di database yang memiliki token sesuai dengan yang discan HP siswa
        $sesi = SesiPresensi::where('token_qr', $request->token_qr)->first();
        if (!$sesi) {
            // Jika token tidak ditemukan atau salah, kirim pesan error 404
            return response()->json(['message' => 'QR Code tidak valid atau sudah kadaluarsa'], 404);
        }

        // 2. Cek Duplikasi: Memastikan siswa ID tersebut belum absen di sesi yang sama hari ini
        $already = Absensi::where('siswa_id', $user->siswa->id)->where('sesi_id', $sesi->id)->exists();
        if ($already) {
            // Jika sudah ada recordnya, kirim error 422 (Unprocessable Entity)
            return response()->json(['message' => 'Anda sudah melakukan absensi di mapel ini!'], 422);
        }

        // 3. Cek Keanggotaan: Memastikan siswa memang murid di kelas yang dijadwalkan tersebut
        $isMember = DB::table('anggota_kelas')
            ->where('siswa_id', $user->siswa->id)
            ->where('kelas_id', $sesi->jadwal->kelas_id)
            ->exists();

        if (!$isMember) {
            // Jika siswa dari kelas lain mencoba scan, kirim error 403 (Forbidden)
            return response()->json(['message' => 'Absensi Gagal: Anda bukan siswa di kelas ini!'], 403);
        }

        // 4. Validasi Jarak: Mengambil titik koordinat sekolah yang terdaftar di jadwal
        $lokasi = $sesi->jadwal->lokasi;
        // Menghitung jarak antara koordinat HP siswa dengan koordinat sekolah menggunakan rumus Haversine
        $jarak = $this->haversine($request->lat_siswa, $request->long_siswa, $lokasi->latitude, $lokasi->longitude);
        
        // Membandingkan hasil hitung jarak dengan batas radius maksimal yang diizinkan (dalam meter)
        if ($jarak > $lokasi->radius) {
            return response()->json([
                'message' => "Gagal: Jarak Anda " . round($jarak) . "m. Maksimal radius " . $lokasi->radius . "m."
            ], 422);
        }

        // 5. Simpan Absensi: Jika semua validasi lolos, data kehadiran resmi disimpan
        Absensi::create([
            'sesi_id' => $sesi->id,
            'siswa_id' => $user->siswa->id,
            'waktu_scan' => now(), 
            'status' => 'hadir',
            'is_valid' => true,
            'lat_siswa' => $request->lat_siswa, 
            'long_siswa' => $request->long_siswa 
        ]);

        return response()->json(['status' => 'success', 'data' => ['message' => 'Absensi Berhasil Terdaftar!']]);
    }

    // Fungsi matematika untuk menghitung jarak antara dua koordinat GPS di permukaan bumi
    private function haversine($lat1, $lon1, $lat2, $lon2) {
        $r = 6371000; // Jari-jari bumi dalam satuan meter
        $dLat = deg2rad($lat2 - $lat1); // Selisih latitude diubah ke radian
        $dLon = deg2rad($lon2 - $lon1); // Selisih longitude diubah ke radian
        // Rumus inti Haversine untuk mencari jarak garis lengkung (Great Circle Distance)
        $a = sin($dLat/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2)**2;
        return $r * (2 * atan2(sqrt($a), sqrt(1-$a)));
    }

    public function historySiswa() {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Memastikan data profil siswa terkait user ini tersedia di database
            if (!$user->siswa) {
                return response()->json(['message' => 'Siswa tidak ditemukan'], 404);
            }

            // Mengambil semua data absensi siswa beserta info sesi, jadwal, dan nama mata pelajarannya
            $history = Absensi::with(['sesi.jadwal.mapel'])
                ->where('siswa_id', $user->siswa->id)
                ->orderBy('waktu_scan', 'desc') // Diurutkan dari yang paling baru
                ->get();

            // Menghitung jumlah masing-masing status (Hadir, Sakit, Izin, Alpa) untuk ringkasan di dashboard HP
            $summary = [
                'total_hadir'   => $history->where('status', 'hadir')->count(),
                'total_sakit'   => $history->where('status', 'sakit')->count(),
                'total_izin'    => $history->where('status', 'izin')->count(),
                'total_alpa'    => $history->where('status', 'alpa')->count(),
            ];

            return response()->json([
                'status' => 'success',
                'summary' => $summary,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            // Menangkap error tak terduga (seperti database down atau error kode) agar aplikasi tidak force close
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getTeacherClasses() {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Proteksi agar data hanya bisa diakses oleh user dengan role guru yang datanya valid
        if (!$user || $user->role !== 'guru' || !$user->guru) {
            return response()->json(['message' => 'Akses ditolak, Anda bukan guru'], 403);
        }

        $guruId = $user->guru->id;
        
        // Mencari daftar kelas unik yang diajar oleh guru ini berdasarkan tabel Jadwal
        $classes = Jadwal::where('guru_id', $guruId)
            ->with('kelas')
            ->get()
            ->pluck('kelas') // Mengambil objek kelas saja
            ->unique('id')   // Menghapus duplikasi jika guru mengajar banyak mapel di kelas yang sama
            ->values();      // Mereset index array agar rapi (0,1,2...)

        return response()->json(['status' => 'success', 'data' => $classes]);
    }

    public function getAttendanceReport(Request $request) {
        $kelasId = $request->kelas_id;
        $tanggal = $request->tanggal; 

        // Mencari semua siswa yang terdaftar di kelas yang dipilih melalui relasi anggotaKelas
        $students = Siswa::whereHas('anggotaKelas', function($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        })->get();

        // Membuat laporan dengan memetakan (mapping) setiap siswa dengan status absennya
        $report = $students->map(function($student) use ($tanggal) {
            // Mencari record absensi untuk siswa ini pada tanggal yang diminta
            $absensi = Absensi::where('siswa_id', $student->id)
                ->whereHas('sesi', function($q) use ($tanggal) {
                    $q->where('tanggal', $tanggal);
                })->first();

            return [
                'nisn' => $student->nisn,
                'nama_siswa' => $student->nama_siswa,
                // Jika data ditemukan tampilkan statusnya, jika kosong maka otomatis dianggap 'alpa'
                'status' => $absensi ? $absensi->status : 'alpa', 
                'waktu' => $absensi ? $absensi->waktu_scan : '-',
            ];
        });

        return response()->json(['status' => 'success', 'data' => $report]);
    }
}