<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\HariLibur;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    /**
     * Logika Pintar menentukan apakah sebuah tanggal masuk Minggu 1 atau Minggu 2.
     * Logika: Menghitung selisih minggu dari awal semester. 
     * Jika selisihnya 0, 2, 4 (genap) maka Minggu 1. Jika 1, 3, 5 (ganjil) maka Minggu 2.
     */
    private function getWeekType($date)
    {
        // Mencari tahun ajaran yang sedang aktif
        $tahunAktif = TahunAjaran::where('status', true)->first();
        
        // Proteksi jika data tahun ajaran tidak ada, sistem tidak akan error dan menganggap Minggu 1
        if (!$tahunAktif || !$tahunAktif->tgl_mulai) {
            return 1;
        }

        // Menentukan hari Senin pada minggu dimulainya semester
        $startDate = Carbon::parse($tahunAktif->tgl_mulai)->startOfWeek();
        // Menentukan hari Senin pada minggu dari tanggal yang sedang dicek
        $targetDate = Carbon::parse($date)->startOfWeek();

        // Menghitung berapa minggu jarak antara tanggal mulai semester dengan tanggal target
        $diffInWeeks = $targetDate->diffInWeeks($startDate);

        // Menggunakan operator Modulo (%) untuk menentukan ganjil atau genap
        return ($diffInWeeks % 2 == 0) ? 1 : 2;
    }

    /**
     * Mengambil jadwal milik user yang sedang login untuk hari ini.
     * Digunakan untuk tampilan Dashboard Utama.
     */
    public function mySchedule()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user(); // Ambil data siapa yang login
        $now = now(); // Ambil waktu sekarang
        $tglHariIni = $now->toDateString(); // Format: YYYY-MM-DD
        
        // Array pembantu untuk menerjemahkan nama hari Inggris ke Indonesia
        $daysIndo = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        
        $namaHariIni = $daysIndo[$now->format('l')]; // Contoh: "Senin"
        $mingguKe = $this->getWeekType($now); // Menentukan apakah sekarang jadwal Minggu 1 atau 2

        // CEK HARI LIBUR: Jika tanggal hari ini terdaftar di tabel HariLibur, KBM ditiadakan
        $libur = HariLibur::where('tanggal', $tglHariIni)->first();
        if ($libur) {
            return response()->json([
                'status' => 'holiday',
                'message' => 'Hari Libur: ' . $libur->keterangan,
                'current_week_type' => $mingguKe,
                'data' => [] // Kirim data kosong karena libur
            ]);
        }

        // Query dasar: Ambil jadwal beserta data Mapel, Guru, Kelas, dan Ruangan (Lokasi)
        $query = Jadwal::with(['mapel', 'guru', 'kelas', 'lokasi'])
            ->where('hari', $namaHariIni)
            ->where('minggu', $mingguKe); // Filter berdasarkan siklus ganjil/genap

        // Filter tambahan: Jika dia siswa, hanya ambil jadwal kelasnya sendiri.
        // Jika dia guru, hanya ambil jadwal di mana dia mengajar.
        if ($user->role == 'siswa') {
            $kelasId = DB::table('anggota_kelas')->where('siswa_id', $user->siswa->id)->value('kelas_id');
            $query->where('kelas_id', $kelasId);
        } else if ($user->role == 'guru') {
            $query->where('guru_id', $user->guru->id);
        }

        return response()->json([
            'status' => 'success',
            'current_week_type' => $mingguKe,
            'data' => $query->orderBy('jam_mulai', 'asc')->get() // Urutkan dari jam pelajaran pertama
        ]);
    }

    /**
     * Mengambil jadwal berdasarkan tanggal yang dipilih pada kalender.
     */
    public function getScheduleByDate(Request $request)
    {
        $request->validate(['date' => 'required|date']); // Validasi input tanggal
        $tglTarget = $request->date;
        $date = Carbon::parse($tglTarget);

        $daysIndo = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        
        $namaHari = $daysIndo[$date->format('l')];
        $mingguKe = $this->getWeekType($date); // Penting: Minggu 1/2 dihitung berdasarkan tanggal yang dipilih

        // 1. Cek apakah tanggal yang diklik di kalender itu hari libur
        $libur = HariLibur::where('tanggal', $tglTarget)->first();
        if ($libur) {
            return response()->json([
                'status' => 'holiday', 
                'message' => $libur->keterangan,
                'target_week_type' => $mingguKe
            ]);
        }

        // 2. Jika bukan libur, ambil jadwalnya
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = Jadwal::with(['mapel', 'guru', 'kelas'])->where('hari', $namaHari)->where('minggu', $mingguKe);
        
        if ($user->role == 'siswa') {
            $kelasId = DB::table('anggota_kelas')->where('siswa_id', $user->siswa->id)->value('kelas_id');
            $query->where('kelas_id', $kelasId);
        } else if ($user->role == 'guru') {
            $query->where('guru_id', $user->guru->id);
        }

        return response()->json([
            'status' => 'success',
            'target_week_type' => $mingguKe,
            'data' => $query->orderBy('jam_mulai', 'asc')->get()
        ]);
    }

    /**
     * Ambil Penanda Kalender.
     * Fungsinya untuk memberikan dot/titik/warna pada kalender (Mana hari sekolah, mana hari libur).
     */
    public function getCalendarMarkers()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // 1. Ambil Hari KBM: Mencari hari apa saja siswa ini memiliki jadwal (misal: Senin s/d Jumat)
        $kelasId = DB::table('anggota_kelas')->where('siswa_id', $user->siswa->id)->value('kelas_id');
        $activeDays = \App\Models\Jadwal::where('kelas_id', $kelasId)->pluck('hari')->unique()->toArray();

        // 2. Ambil daftar tanggal libur dari inputan manual sekolah
        $holidaysSekolah = \App\Models\HariLibur::pluck('tanggal')->toArray();

        // 3. Ambil daftar tanggal libur nasional dari API Luar (Internet)
        $holidaysNasional = [];
        try {
            $response = Http::get('https://libur.deno.dev/api');
            if ($response->successful()) {
                $holidaysNasional = collect($response->json())->pluck('date')->toArray();
                Log::info("API Libur Nasional Berhasil. Total: " . count($holidaysNasional));
            } else {
                Log::error("API Libur Nasional Gagal. Status: " . $response->status());
            }
        } catch (\Exception $e) {
            // Jika internet mati atau API error, log akan mencatat tapi aplikasi tidak akan crash
            Log::error("API Libur Nasional Error: " . $e->getMessage());
        }

        // Gabungkan semua tanggal libur (sekolah + nasional) dan hapus jika ada tanggal yang ganda
        $allHolidays = array_unique(array_merge($holidaysSekolah, $holidaysNasional));

        return response()->json([
            'active_days' => array_values($activeDays), // Hari apa saja ada pelajaran
            'holidays' => array_values($allHolidays),   // Tanggal berapa saja libur
            'debug_info' => [
                'total_nasional' => count($holidaysNasional),
                'total_sekolah' => count($holidaysSekolah)
            ]
        ]);
    }
}