<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Kelas;
use Illuminate\Http\Request;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data untuk dropdown filter di web
        $kelas = \App\Models\Kelas::all();
        $tahunAjaran = \App\Models\TahunAjaran::all();

        return view('reports.index', compact('kelas', 'tahunAjaran'));
    }

    /**
     * Mengambil data statistik untuk ditampilkan dalam bentuk Grafik (Chart).
     * Logika: Mengelompokkan jumlah kehadiran berdasarkan bulan dan nomor kelas.
     */
    public function chartData(Request $request)
    {
        // Menangkap filter dari URL (Contoh: ?status=sakit&tingkat=10)
        $status   = $request->query('status', 'hadir');
        $tahunId  = $request->query('tahun_ajaran_id');
        $semester = $request->query('semester');
        $tingkat  = $request->query('tingkat');
        $jurusan  = $request->query('jurusan');

        // Query awal: Hanya mengambil absensi yang statusnya sesuai dan sudah divalidasi
        $query = Absensi::where('absensi.status', $status)
            ->where('absensi.is_valid', true);

        // Filter: Memastikan data sesuai dengan Tahun Ajaran dan Semester yang dipilih
        $query->whereHas('sesi.jadwal.kelas.tahunAjaran', function($q) use ($tahunId, $semester) {
            $q->where('id', $tahunId)->where('semester', $semester);
        });

        // Filter: Memastikan data sesuai dengan Tingkat (10/11/12) dan Jurusan (RPL/TKJ/dll)
        $query->whereHas('sesi.jadwal.kelas', function($q) use ($tingkat, $jurusan) {
            $q->where('tingkat', $tingkat)->where('jurusan', $jurusan);
        });

        // LOGIKA AGREGASI: Menggabungkan beberapa tabel untuk menghitung jumlah per bulan
        $data = $query->join('sesi_presensi', 'absensi.sesi_id', '=', 'sesi_presensi.id')
            ->join('jadwal', 'sesi_presensi.jadwal_id', '=', 'jadwal.id')
            ->join('kelas', 'jadwal.kelas_id', '=', 'kelas.id')
            ->select(
                DB::raw('MONTH(absensi.waktu_scan) as bulan'), // Mengambil angka bulan (1-12)
                'kelas.nomor_kelas', // Identitas kelas agar grafik bisa membedakan 10-1, 10-2, dsb.
                DB::raw('count(*) as jumlah') // Menghitung total baris sebagai jumlah kehadiran
            )
            ->groupBy('bulan', 'kelas.nomor_kelas') // Pengelompokan data
            ->orderBy('bulan', 'ASC')
            ->get();

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * Mengunduh laporan absensi dalam format file Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        // Validasi: Memastikan parameter kelas dan waktu sudah dipilih
        $request->validate([
            'kelas_id' => 'required',
            'tahun_ajaran_id' => 'required',
            'semester' => 'required',
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);
        // Membersihkan nama kelas dari spasi agar nama file tidak error (Contoh: "XI RPL" jadi "XI_RPL")
        $cleanName = str_replace(' ', '_', $kelas->nama_kelas);
        $fileName = "Laporan_Absensi_{$cleanName}_{$request->semester}.xlsx";

        // Memanggil Class Export (Maatwebsite Excel) untuk membuat file
        return Excel::download(new AttendanceExport($request->kelas_id, $request->tahun_ajaran_id, $request->semester), $fileName);
    }

    /**
     * Mengunduh laporan absensi dalam format file PDF.
     */
    public function exportPdf(Request $request)
    {
        // Mengambil data absensi lengkap dengan data siswa, jadwal, dan nama mata pelajaran
        $absensi = Absensi::with(['siswa', 'sesi.jadwal.mapel'])
            ->whereHas('sesi.jadwal.kelas', function($q) use ($request) {
                $q->where('id', $request->kelas_id)
                  ->where('tahun_ajaran_id', $request->tahun_ajaran_id);
            })
            ->whereHas('sesi.jadwal.kelas.tahunAjaran', function($q) use ($request) {
                $q->where('semester', $request->semester);
            })
            ->orderBy('waktu_scan', 'desc')
            ->get();

        // Mengambil info kelas beserta Nama Wali Kelas untuk tanda tangan di bawah PDF
        $kelas = Kelas::with('waliKelas')->findOrFail($request->kelas_id);

        // Memasukkan data ke dalam template view 'reports.attendance_pdf' untuk dirender menjadi PDF
        $pdf = Pdf::loadView('reports.attendance_pdf', [
            'absensi' => $absensi,
            'kelas'   => $kelas,
            'semester' => $request->semester,
            'tanggal' => now()->translatedFormat('d F Y') // Format tanggal hari ini dalam bahasa Indonesia
        ]);

        return $pdf->download("Laporan_Absensi_{$kelas->nama_kelas}.pdf");
    }

    /**
     * Memberikan ringkasan cepat untuk Dashboard Kepala Sekolah.
     * Logika: Menghitung persentase kehadiran siswa dan guru hari ini secara real-time.
     */
    public function getPrincipalSummary()
    {
        $today = now()->toDateString();
        
        // 1. STATISTIK SISWA
        $totalSiswa = \App\Models\Siswa::count();
        $siswaHadir = Absensi::whereDate('waktu_scan', $today)
            ->where('status', 'hadir')
            ->where('is_valid', true)
            ->distinct('siswa_id') // Jika satu siswa absen di 3 mapel, tetap dihitung 1 orang hadir
            ->count();
        
        // Rumus Persentase: (Bagian / Total) * 100
        $studentPercentage = ($totalSiswa > 0) ? round(($siswaHadir / $totalSiswa) * 100) : 0;

        // 2. STATISTIK GURU
        $totalGuru = \App\Models\Guru::count();
        // Menghitung berapa banyak guru yang sudah membuka sesi absen hari ini
        $guruHadir = \App\Models\SesiPresensi::whereDate('tanggal', $today)->distinct('jadwal_id')->count();
        $teacherPercentage = ($totalGuru > 0) ? round(($guruHadir / $totalGuru) * 100) : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'student_percentage' => $studentPercentage,
                'teacher_percentage' => $teacherPercentage,
                'total_siswa' => $totalSiswa,
                'siswa_hadir' => $siswaHadir
            ]
        ]);
    }
}