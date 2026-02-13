<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $kelasId, $tahunId, $semester;

    // Tambahkan parameter semester di constructor
    public function __construct($kelasId, $tahunId, $semester)
    {
        $this->kelasId = $kelasId;
        $this->tahunId = $tahunId;
        $this->semester = $semester;
    }

    public function collection()
    {
        return Absensi::with(['siswa', 'sesi.jadwal.mapel'])
            ->whereHas('sesi.jadwal', function($q) {
                $q->where('kelas_id', $this->kelasId)
                  ->whereHas('kelas', function($queryKelas) {
                      $queryKelas->where('tahun_ajaran_id', $this->tahunId)
                        ->whereHas('tahunAjaran', function($queryTA) {
                            // FILTER SEMESTER DI SINI
                            $queryTA->where('semester', $this->semester);
                        });
                  });
            })->get();
    }

    public function headings(): array
    {
        return ["Nama Siswa", "NISN", "Mata Pelajaran", "Tanggal", "Status", "Keterangan"];
    }

    public function map($absensi): array
    {
        return [
            $absensi->siswa->nama_siswa ?? '-',
            $absensi->siswa->nisn ?? '-',
            $absensi->sesi->jadwal->mapel->nama_mapel ?? '-',
            $absensi->created_at ? $absensi->created_at->format('d-m-Y H:i') : '-',
            strtoupper($absensi->status),
            $absensi->is_valid ? 'Valid' : 'Tidak Valid',
        ];
    }
}