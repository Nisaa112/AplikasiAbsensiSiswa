<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Class AttendanceExport
 * Menggunakan 3 Interface dari Laravel Excel:
 * 1. FromCollection: Mengambil data dari database.
 * 2. WithHeadings: Membuat judul kolom di baris pertama Excel.
 * 3. WithMapping: Mengatur data mana saja yang masuk ke kolom mana.
 */
class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    // Variabel untuk menyimpan kriteria filter yang dikirim dari Controller
    protected $kelasId, $tahunId, $semester;

    // Constructor: Berfungsi untuk menerima kiriman data filter (ID Kelas, Tahun, Semester) saat class ini dipanggil
    public function __construct($kelasId, $tahunId, $semester)
    {
        $this->kelasId = $kelasId;
        $this->tahunId = $tahunId;
        $this->semester = $semester;
    }

    /**
     * Bagian Collection: 
     * Mengambil data mentah dari tabel Absensi berdasarkan filter yang sudah ditentukan.
     */
    public function collection()
    {
        // Menggunakan Eager Loading (with) agar data Siswa dan Mapel ikut terbawa dalam satu query
        return Absensi::with(['siswa', 'sesi.jadwal.mapel'])
            // Filter 1: Pastikan ID Tahun Ajaran dan Semesternya cocok
            ->whereHas('sesi.jadwal.kelas.tahunAjaran', function($q) {
                $q->where('id', $this->tahunId)->where('semester', $this->semester);
            })
            // Filter 2: Pastikan data absensi yang ditarik hanya untuk Kelas yang dipilih
            ->whereHas('sesi.jadwal', function($q) {
                $q->where('kelas_id', $this->kelasId);
            })
            ->get(); // Eksekusi pengambilan data
    }

    /**
     * Bagian Headings:
     * Mengatur baris paling atas (Header) pada file Excel Anda.
     */
    public function headings(): array
    {
        return ["Nama Siswa", "NISN", "Mata Pelajaran", "Tanggal", "Status", "Keterangan"];
    }

    /**
     * Bagian Mapping:
     * Mengatur isi sel pada Excel berdasarkan data yang didapat dari fungsi collection() di atas.
     * $absensi adalah satu baris data dari database.
     */
    public function map($absensi): array
    {
        return [
            // Mengambil nama siswa, jika datanya hilang diganti dengan tanda strip '-' (Null Safety)
            $absensi->siswa->nama_siswa ?? '-',
            $absensi->siswa->nisn ?? '-',
            // Menarik nama mata pelajaran melalui relasi bertingkat (Absensi -> Sesi -> Jadwal -> Mapel)
            $absensi->sesi->jadwal->mapel->nama_mapel ?? '-',
            // Memformat tanggal agar mudah dibaca manusia (Contoh: 11-03-2026 07:30)
            $absensi->created_at ? $absensi->created_at->format('d-m-Y H:i') : '-',
            // Mengubah status (hadir/sakit/izin) menjadi HURUF KAPITAL agar terlihat formal di Excel
            strtoupper($absensi->status),
            // Mengubah nilai boolean is_valid menjadi teks yang mudah dimengerti
            $absensi->is_valid ? 'Valid' : 'Tidak Valid',
        ];
    }
}