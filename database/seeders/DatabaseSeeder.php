<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tabel: TAHUN AJARAN
        $taId = DB::table('tahun_ajaran')->insertGetId([
            'tahun' => '2025/2026',
            'semester' => 'Ganjil',
            'status' => true,
            'created_at' => now(),
        ]);

        // 2. Tabel: LOKASI
        $lokasiId = DB::table('lokasi')->insertGetId([
            'nama_lokasi' => 'Gedung Utama SMK (Testing 24 Jam)',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'radius' => 500,
            'created_at' => now(),
        ]);

        // 3. Tabel: MAPEL
        $mapels = ['Matematika', 'B. Indo', 'RPL Pagi', 'RPL Siang', 'RPL Malam', 'RPL Subuh'];
        $mapelIds = [];
        foreach ($mapels as $m) {
            $mapelIds[$m] = DB::table('mapel')->insertGetId(['nama_mapel' => $m, 'created_at' => now()]);
        }

        // 4. Tabel: USERS (Admin & Guru)
        DB::table('users')->insert([
            'name' => 'Admin Sistem',
            'serial_number' => 'ADM001',
            'role' => 'admin',
            'password' => Hash::make('password123'),
            'created_at' => now(),
        ]);

        $userGuruId = DB::table('users')->insertGetId([
            'name' => 'Kim Seungmin',
            'serial_number' => '0081239239',
            'role' => 'guru',
            'password' => Hash::make('password123'),
            'created_at' => now(),
        ]);

        $guruId = DB::table('guru')->insertGetId([
            'user_id' => $userGuruId,
            'nip' => '0081239239',
            'nama_guru' => 'Kim Seungmin',
            'created_at' => now(),
        ]);

        // 5. Tabel: KELAS (Disesuaikan dengan migrasi terbaru)
        $kelas12Id = DB::table('kelas')->insertGetId([
            'tingkat' => 12,
            'jurusan' => 'RPL',
            'nomor_kelas' => '2',
            'tahun_ajaran_id' => $taId,
            'wali_kelas_id' => $guruId,
            'created_at' => now(),
        ]);

        $kelas11Id = DB::table('kelas')->insertGetId([
            'tingkat' => 11,
            'jurusan' => 'RPL',
            'nomor_kelas' => '1',
            'tahun_ajaran_id' => $taId,
            'wali_kelas_id' => $guruId,
            'created_at' => now(),
        ]);

        // 6. Tabel: SISWA & ANGGOTA KELAS
        $siswaData = [
            ['name' => 'Annisa Aulia F', 'serial' => '2122001', 'kelas' => $kelas12Id],
            ['name' => 'Sania Eka W', 'serial' => '2122002', 'kelas' => $kelas12Id],
            ['name' => 'Shalwa Ainnur H', 'serial' => '2122003', 'kelas' => $kelas12Id],
            ['name' => 'Shaqilla Salsabila', 'serial' => '2122004', 'kelas' => $kelas11Id],
        ];

        $siswaIds = [];
        foreach ($siswaData as $s) {
            $uId = DB::table('users')->insertGetId([
                'name' => $s['name'],
                'serial_number' => $s['serial'],
                'role' => 'siswa',
                'password' => Hash::make('password123'),
                'created_at' => now(),
            ]);

            $sId = DB::table('siswa')->insertGetId([
                'user_id' => $uId,
                'nisn' => $s['serial'],
                'nama_siswa' => $s['name'],
                'created_at' => now(),
            ]);

            DB::table('anggota_kelas')->insert([
                'siswa_id' => $sId,
                'kelas_id' => $s['kelas'],
                'created_at' => now(),
            ]);
            $siswaIds[$s['serial']] = $sId;
        }

        // 7. Tabel: JADWAL (24 Jam untuk Kelas 11 & 12)
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $shiftWaktu = [
            ['Subuh', '00:00:00', '06:00:00', 'RPL Subuh'],
            ['Pagi', '07:00:00', '12:00:00', 'RPL Pagi'],
            ['Siang', '13:00:00', '18:00:00', 'RPL Siang'],
            ['Malam', '19:00:00', '23:59:00', 'RPL Malam'],
        ];

        $masterJadwal = [];
        foreach ($hariList as $hari) {
            foreach ($shiftWaktu as $shift) {
                // Buat jadwal untuk kelas 12
                $masterJadwal[] = [
                    'id' => DB::table('jadwal')->insertGetId([
                        'kelas_id' => $kelas12Id,
                        'mapel_id' => $mapelIds[$shift[3]],
                        'guru_id' => $guruId,
                        'lokasi_id' => $lokasiId,
                        'hari' => $hari,
                        'jam_mulai' => $shift[1],
                        'jam_selesai' => $shift[2],
                        'created_at' => now(),
                    ]),
                    'hari' => $hari,
                    'kelas_id' => $kelas12Id
                ];
            }
        }

        // 8. LOOPING DATA HARIAN (8 FEB - 16 FEB 2026)
        $start = Carbon::create(2026, 2, 8);
        $end = Carbon::create(2026, 2, 16);

        $hariIndo = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];

        while ($start <= $end) {
            $namaHari = $hariIndo[$start->format('l')];
            $tgl = $start->toDateString();

            foreach ($masterJadwal as $mj) {
                if ($mj['hari'] == $namaHari) {
                    
                    // 9. Tabel: SESI PRESENSI
                    $sesiId = DB::table('sesi_presensi')->insertGetId([
                        'jadwal_id' => $mj['id'],
                        'tanggal' => $tgl,
                        'token_qr' => 'TOKEN-' . bin2hex(random_bytes(4)),
                        'created_at' => $start->copy()->addHours(8), // Set created_at agar filter bulan bekerja
                    ]);

                    // 10. Tabel: ABSENSI
                    // Buat Annisa (2122001) dan Budi (2122002) hadir di kelas 12
                    foreach ([$siswaIds['2122001'], $siswaIds['2122002']] as $sid) {
                        DB::table('absensi')->insert([
                            'sesi_id' => $sesiId,
                            'siswa_id' => $sid,
                            'waktu_scan' => $tgl . ' ' . rand(7, 20) . ':'.rand(10,59).':00',
                            'status' => 'hadir',
                            'lat_siswa' => -6.200001,
                            'long_siswa' => 106.816667,
                            'is_valid' => true,
                            'created_at' => $start->copy()->addHours(9),
                        ]);
                    }
                }
            }

            // 11. Tabel: PERIZINAN (Citra Lestari)
            if ($tgl == '2026-02-08') { 
                DB::table('perizinan')->insert([
                    'siswa_id' => $siswaIds['2122003'],
                    'tgl_izin' => $tgl,
                    'jenis_izin' => 'izin',
                    'alasan' => 'Urusan keluarga di hari libur',
                    'bukti_gambar' => 'izin_minggu.jpg',
                    'status_izin' => 'pending',
                    'validator_id' => $guruId,
                    'created_at' => now(),
                ]);
            }

            $start->addDay();
        }

        $this->command->info("Seeder Selesai!");
    }
}