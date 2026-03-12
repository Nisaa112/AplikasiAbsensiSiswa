<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AbsensiController extends Controller
{
    /**
     * Mencari data absensi tunggal.
     * Logika: Menggunakan 'findOrFail' agar jika ID tidak ada, Laravel langsung 
     * memberikan respons error 404 (Not Found) secara otomatis.
     */
    private function findAbsensiById($id)
    {
        return Absensi::findOrFail($id);
    }

    /**
     * Menampilkan daftar absensi.
     * Logika: Menggunakan 'with' (Eager Loading) untuk memuat data relasi 'sesi' dan 'siswa' 
     * sekaligus guna menghindari masalah N+1 query yang bisa memperlambat aplikasi.
     */
    public function index(Request $request)
    {
        $data = Absensi::with(['sesi', 'siswa'])->get();

        // Mengecek apakah request meminta format JSON (biasanya dari AJAX atau API)
        if ($request->expectsJson()) {
            return response()->json($data);
        }

        // Jika request browser biasa, kirimkan data ke folder resources/views/absensi/index.blade.php
        return view('absensi/index', [
            'data' => $data
        ]);
    }

    /**
     * Memanggil halaman formulir input.
     */
    public function create()
    {
        return view('absensi/form');
    }

    /**
     * Proses penyimpanan data baru.
     */
    public function store(Request $request)
    {
        // Validasi input: Memastikan data yang masuk sesuai aturan (misal: ID harus ada di tabel lain)
        $validated = $request->validate([
            'sesi_id'   => 'required|exists:sesi_presensi,id',
            'siswa_id'  => 'required|exists:siswa,id',
            'waktu_scan'=> 'required|date',
            'status'    => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alfa'])],
            'lat_siswa' => 'nullable|numeric',
            'long_siswa'=> 'nullable|numeric',
            'is_valid'  => 'required|boolean',
        ]);

        // Eksekusi insert data ke database menggunakan Mass Assignment
        $status = Absensi::create($validated);

        // Jika request via API, berikan feedback status 200 (berhasil) atau 500 (gagal server)
        if ($request->expectsJson()) {
            return response()->json([
                'status'  => (bool) $status,
                'message' => $status ? 'Absensi berhasil ditambahkan' : 'Absensi gagal ditambahkan',
            ], $status ? 200 : 500);
        }

        // Redirect kembali ke halaman index dengan pesan sukses di session flash
        if ($status) {
            return redirect('/absensi')->with('success', 'Absensi berhasil ditambahkan');
        }

        // Redirect kembali dengan pesan error jika proses insert gagal
        return redirect('/absensi')->with('error', 'Absensi gagal ditambahkan');
    }

    /**
     * Mengambil data spesifik untuk ditampilkan di form edit.
     */
    public function edit($id)
    {
        $data = $this->findAbsensiById($id);

        return view('absensi/form', [
            'data' => $data
        ]);
    }

    /**
     * Proses pembaruan data yang sudah ada.
     */
    public function update(Request $request, $id)
    {
        // Mencari objek model berdasarkan ID terlebih dahulu sebelum diupdate
        $absensi = $this->findAbsensiById($id);

        // Validasi ulang data yang dikirimkan melalui form edit
        $validated = $request->validate([
            'sesi_id'   => 'required|exists:sesi_presensi,id',
            'siswa_id'  => 'required|exists:siswa,id',
            'waktu_scan'=> 'required|date',
            'status'    => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alfa'])],
            'lat_siswa' => 'nullable|numeric',
            'long_siswa'=> 'nullable|numeric',
            'is_valid'  => 'required|boolean',
        ]);

        // Mengupdate record di database berdasarkan ID yang ditemukan tadi
        $status = $absensi->update($validated);

        // Percabangan response JSON untuk kebutuhan integrasi frontend modern/mobile
        if ($request->expectsJson()) {
            return response()->json([
                'status'  => (bool) $status,
                'message' => $status ? 'Absensi berhasil diupdate' : 'Absensi gagal diupdate',
            ], $status ? 200 : 500);
        }

        if ($status) {
            return redirect('/absensi')->with('success', 'Absensi berhasil diupdate');
        }

        return redirect('/absensi')->with('error', 'Absensi gagal diupdate');
    }

    /**
     * Menghapus data permanen.
     */
    public function destroy(Request $request, $id)
    {
        // Cari datanya, jika ketemu langsung eksekusi perintah delete
        $absensi = $this->findAbsensiById($id);
        $status  = $absensi->delete();

        // Mengirimkan response balik apakah penghapusan berhasil atau tidak
        if ($request->expectsJson()) {
            return response()->json([
                'status'  => (bool) $status,
                'message' => $status ? 'Absensi berhasil dihapus' : 'Absensi gagal dihapus',
            ], $status ? 200 : 500);
        }

        if ($status) {
            return redirect('/absensi')->with('success', 'Absensi berhasil dihapus');
        }

        return redirect('/absensi')->with('error', 'Absensi gagal dihapus');
    }
}