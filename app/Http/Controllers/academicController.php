<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Guru, Kelas, Mapel, TahunAjaran};

class AcademicController extends Controller {
    
    /**
     * Fungsi getMasterData
     * * Logika: Berfungsi sebagai "Single Point of Truth" untuk mengambil semua data referensi 
     * utama yang dibutuhkan aplikasi (misal untuk mengisi dropdown/pilihan di form) dalam satu kali request.
     */
    public function getMasterData() {
        // Mengembalikan response dalam format JSON agar bisa dikonsumsi oleh Frontend (Vue/React) atau Mobile.
        return response()->json([
            
            // Logika: Mencari record tahun ajaran yang kolom 'status'-nya bernilai true (1).
            // Method 'first()' digunakan karena sistem biasanya hanya memiliki satu tahun ajaran yang aktif saat ini.
            'tahun_aktif' => TahunAjaran::where('status', true)->first(),
            
            // Logika: Mengambil semua data kelas. 
            // Menggunakan 'with' (Eager Loading) untuk ikut memuat data Guru yang bertugas sebagai wali kelas,
            // sehingga aplikasi tidak perlu melakukan query tambahan berulang kali saat menampilkan nama wali kelas.
            'daftar_kelas' => Kelas::with('waliKelas')->get(),
            
            // Logika: Mengambil seluruh daftar Mata Pelajaran yang tersedia di database tanpa filter.
            'daftar_mapel' => Mapel::all(),
            
            // Logika: Mengambil seluruh daftar Guru yang terdaftar di database untuk referensi data akademik.
            'daftar_guru'  => Guru::all()
        ]);
    }
}