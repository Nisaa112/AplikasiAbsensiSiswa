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
    public function chartData(Request $request)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required',
            'semester'        => 'required|in:Ganjil,Genap', 
            'status'          => 'required|in:hadir,sakit,izin,alpa',
            'tingkat'         => 'required|in:10,11,12',
            'jurusan'         => 'required|string',
        ]);

        $data = Absensi::where('absensi.status', $request->status)
            ->join('sesi_presensi', 'absensi.sesi_id', '=', 'sesi_presensi.id')
            ->join('jadwal', 'sesi_presensi.jadwal_id', '=', 'jadwal.id')
            ->join('kelas', 'jadwal.kelas_id', '=', 'kelas.id')
            ->join('tahun_ajaran', 'kelas.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->where('kelas.tahun_ajaran_id', $request->tahun_ajaran_id)
            ->where('tahun_ajaran.semester', $request->semester)
            ->where('kelas.tingkat', $request->tingkat)
            ->where('kelas.jurusan', $request->jurusan)
            ->select(
                DB::raw('MONTH(absensi.created_at) as bulan'),
                'kelas.nomor_kelas',
                DB::raw('count(*) as jumlah')
            )
            ->groupBy(DB::raw('MONTH(absensi.created_at)'), 'kelas.nomor_kelas')
            ->orderBy('bulan', 'ASC')
            ->get();

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * Ekspor ke Excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'kelas_id'        => 'required',
            'tahun_ajaran_id' => 'required',
            'semester'        => 'required', // Tambahkan validasi
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);
        
        // Nama file menyertakan semester agar jelas
        $cleanClassName = str_replace(' ', '_', $kelas->nama_kelas);
        $fileName = 'Laporan_Absensi_' . $cleanClassName . '_' . $request->semester . '.xlsx';

        // Kirim 3 parameter ke constructor export
        return Excel::download(new AttendanceExport($request->kelas_id, $request->tahun_ajaran_id, $request->semester), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'kelas_id'        => 'required',
            'tahun_ajaran_id' => 'required',
            'semester'        => 'required', // Tambahkan validasi
        ]);

        $data = Absensi::with(['siswa', 'sesi.jadwal.mapel', 'sesi.jadwal.kelas'])
            ->whereHas('sesi.jadwal', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id)
                ->whereHas('kelas', function($queryKelas) use ($request) {
                    $queryKelas->where('tahun_ajaran_id', $request->tahun_ajaran_id)
                        ->whereHas('tahunAjaran', function($queryTA) use ($request) {
                            // FILTER SEMESTER DI PDF
                            $queryTA->where('semester', $request->semester);
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $kelas = Kelas::with('waliKelas')->findOrFail($request->kelas_id);

        $pdf = Pdf::loadView('reports.attendance_pdf', [
            'absensi' => $data,
            'kelas'   => $kelas,
            'semester' => $request->semester, // Kirim ke blade
            'tanggal' => now()->format('d F Y')
        ]);

        return $pdf->download('Laporan_Absensi_'. $kelas->nama_kelas .'_'. $request->semester .'.pdf');
    }
}