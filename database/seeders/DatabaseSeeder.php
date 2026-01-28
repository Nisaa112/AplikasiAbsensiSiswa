<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Lokasi;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Jadwal;
use App\Models\AnggotaKelas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tahunAjaran = TahunAjaran::create([
            'tahun' => '2025/2026',
            'semester' => 'Ganjil',
            'status' => true
        ]);

        $lokasiLab = Lokasi::create([
            'nama_lokasi' => 'Lab PPLG 1',
            'latitude' => -6.914744, 
            'longitude' => 107.609810,
            'radius' => 20
        ]);

        $lokasiKelas = Lokasi::create([
            'nama_lokasi' => 'Ruang Teori XI-2',
            'latitude' => -6.914800,
            'longitude' => 107.610000,
            'radius' => 15
        ]);

        $lokasiSekolah = Lokasi::create([
            'nama_lokasi' => 'SMKN 1 Cianjur',
            'latitude' => -6.818456,
            'longitude' => 107.144414,
            'radius' => 50 
        ]);

        $userGuru = User::create([
            'serial_number' => '19850101', 
            'password' => Hash::make('password123'),
            'role' => 'guru'
        ]);

        $guru = Guru::create([
            'user_id' => $userGuru->id,
            'nip' => '19850101',
            'nama_guru' => 'Budi Santoso, S.Kom'
        ]);

        $userSiswa = User::create([
            'serial_number' => '222310101',
            'password' => Hash::make('siswa123'),
            'role' => 'siswa',
            'device_id' => null 
        ]);

        $siswa = Siswa::create([
            'user_id' => $userSiswa->id,
            'nisn' => '222310101',
            'nama_siswa' => 'Annisa Putri'
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => '12 PPLG 2',
            'tahun_ajaran_id' => $tahunAjaran->id,
            'wali_kelas_id' => $guru->id
        ]);

        AnggotaKelas::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id
        ]);

        $mapel = Mapel::create([
            'nama_mapel' => 'Pemrograman Mobile'
        ]);

        $hariIni = date('l'); 
        $daftarHari = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];

        Jadwal::create([
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'guru_id' => $guru->id,
            'lokasi_id' => $lokasiLab->id,
            'hari' => $daftarHari[$hariIni] ?? 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '12:00:00'
        ]);
    }
}