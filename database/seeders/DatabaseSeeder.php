<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $taId = DB::table('tahun_ajaran')->insertGetId([
            'tahun' => '2025/2026',
            'semester' => 'Ganjil',
            'status' => true,
            'created_at' => now(),
        ]);

        $lokasiId = DB::table('lokasi')->insertGetId([
            'nama_lokasi' => 'Gedung Utama SMK',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius' => 50, // 50 meter
            'created_at' => now(),
        ]);

        $mapels = ['Matematika', 'B. Indo', 'Konsentrasi RPL', 'BK', 'Senam'];
        $mapelIds = [];
        foreach ($mapels as $m) {
            $mapelIds[] = DB::table('mapel')->insertGetId([
                'nama_mapel' => $m,
                'created_at' => now(),
            ]);
        }

        $userGuru = DB::table('users')->insertGetId([
            'name' => 'Kim Seungmin',
            'serial_number' => '0081239239',
            'role' => 'guru',
            'password' => Hash::make('password123'),
            'created_at' => now(),
        ]);

        $guruId = DB::table('guru')->insertGetId([
            'user_id' => $userGuru,
            'nip' => '0081239239',
            'nama_guru' => 'Kim Seungmin',
            'created_at' => now(),
        ]);

        $kelasId = DB::table('kelas')->insertGetId([
            'nama_kelas' => 'XII RPL 2',
            'tahun_ajaran_id' => $taId,
            'wali_kelas_id' => $guruId,
            'created_at' => now(),
        ]);

        $userSiswa = DB::table('users')->insertGetId([
            'name' => 'Annisa Putri',
            'serial_number' => '2122001',
            'role' => 'siswa',
            'password' => Hash::make('password123'),
            'created_at' => now(),
        ]);

        $siswaId = DB::table('siswa')->insertGetId([
            'user_id' => $userSiswa,
            'nisn' => '2122001',
            'nama_siswa' => 'Annisa Putri',
            'created_at' => now(),
        ]);

        DB::table('anggota_kelas')->insert([
            'siswa_id' => $siswaId,
            'kelas_id' => $kelasId,
            'created_at' => now(),
        ]);

        $jadwalId = DB::table('jadwal')->insertGetId([
            'kelas_id' => $kelasId,
            'mapel_id' => $mapelIds[0], // Matematika
            'guru_id' => $guruId,
            'lokasi_id' => $lokasiId,
            'hari' => 'Selasa',
            'jam_mulai' => '07:10:00',
            'jam_selesai' => '09:10:00',
            'created_at' => now(),
        ]);

        $sesiId = DB::table('sesi_presensi')->insertGetId([
            'jadwal_id' => $jadwalId,
            'tanggal' => now()->toDateString(),
            'token_qr' => bin2hex(random_bytes(16)),
            'created_at' => now(),
        ]);

        DB::table('absensi')->insert([
            'sesi_id' => $sesiId,
            'siswa_id' => $siswaId,
            'waktu_scan' => now(),
            'status' => 'hadir',
            'lat_siswa' => -6.200005,
            'long_siswa' => 106.816670,
            'is_valid' => true,
            'created_at' => now(),
        ]);

        DB::table('perizinan')->insert([
            'siswa_id' => $siswaId,
            'tgl_izin' => now()->toDateString(),
            'jenis_izin' => 'sakit',
            'alasan' => 'Demam tinggi',
            'bukti_gambar' => 'bukti_sakit_dummy.jpg',
            'status_izin' => 'pending',
            'validator_id' => $guruId,
            'created_at' => now(),
        ]);

        $jadwalSenin = DB::table('jadwal')->insertGetId([
            'kelas_id' => $kelasId,
            'mapel_id' => $mapelIds[2],
            'guru_id' => $guruId,
            'lokasi_id' => $lokasiId,
            'hari' => 'Senin',
            'jam_mulai' => '07:10:00',
            'jam_selesai' => '12:00:00',
            'created_at' => now(),
        ]);

        DB::table('sesi_presensi')->insert([
            'jadwal_id' => $jadwalSenin,
            'tanggal' => now()->toDateString(),
            'token_qr' => bin2hex(random_bytes(16)),
            'created_at' => now(),
        ]);
        
        $this->command->info('Semua tabel (12 tabel) berhasil di-seed!');
    }
}