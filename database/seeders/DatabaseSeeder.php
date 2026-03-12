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
        // PENTING: Gunakan Transaction agar proses insert ribuan data jadi sangat cepat
        DB::beginTransaction();

        try {
            // Optimasi: Hash password di luar loop agar tidak membebani CPU
            $password = Hash::make('password123');

            // 1. Tabel: TAHUN AJARAN
            $taId = DB::table('tahun_ajaran')->insertGetId([
                'tahun' => '2025/2026',
                'semester' => 'Genap',
                'tgl_mulai' => '2026-01-05',
                'status' => true,
            ]);

            // 2. Tabel: LOKASI
            $lokasiId = DB::table('lokasi')->insertGetId([
                'nama_lokasi' => 'Gedung Utama SMK (Testing 24 Jam)',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius' => 500,
                'created_at' => now(),
            ]);

            // 3. Tabel: MATA PELAJARAN
            $mapels = ['Matematika', 'B. Indo', 'RPL Pagi', 'RPL Siang', 'RPL Malam', 'RPL Subuh'];
            $mapelIds = [];
            foreach ($mapels as $m) {
                $mapelIds[$m] = DB::table('mapel')->insertGetId(['nama_mapel' => $m, 'created_at' => now()]);
            }

            // 4. Tabel: USERS (Admin, Kepsek, & Guru)
            $adminUserId = DB::table('users')->insertGetId([
                'name' => 'Admin Sistem (Hwang Hyunjin)',
                'serial_number' => 'ADM001',
                'role' => 'admin',
                'password' => $password,
                'created_at' => now(),
            ]);

            $kepsekUserId = DB::table('users')->insertGetId([
                'name' => 'Kepala Sekolah (Bang Chan)',
                'serial_number' => 'KPS001',
                'role' => 'kepsek',
                'password' => $password,
                'created_at' => now(),
            ]);

            // Guru 1
            $userGuru1Id = DB::table('users')->insertGetId([
                'name' => 'Kim Seungmin',
                'serial_number' => '0081239239',
                'role' => 'guru',
                'password' => $password,
                'created_at' => now(),
            ]);

            $guru1Id = DB::table('guru')->insertGetId([
                'user_id' => $userGuru1Id,
                'nip' => '0081239239',
                'nama_guru' => 'Kim Seungmin',
                'senioritas' => 'Senior',
                'gender' => 'L',
                'created_at' => now(),
            ]);

            // Guru 2
            $userGuru2Id = DB::table('users')->insertGetId([
                'name' => 'Lee Know',
                'serial_number' => '0081239240',
                'role' => 'guru',
                'password' => $password,
                'created_at' => now(),
            ]);

            $guru2Id = DB::table('guru')->insertGetId([
                'user_id' => $userGuru2Id,
                'nip' => '0081239240',
                'nama_guru' => 'Lee Know',
                'senioritas' => 'Junior',
                'gender' => 'L',
                'created_at' => now(),
            ]);

            // Guru 3
            $userGuru3Id = DB::table('users')->insertGetId([
                'name' => 'Han Jisung',
                'serial_number' => '0081239241',
                'role' => 'guru',
                'password' => $password,
                'created_at' => now(),
            ]);

            $guru3Id = DB::table('guru')->insertGetId([
                'user_id' => $userGuru3Id,
                'nip' => '0081239241',
                'nama_guru' => 'Han Jisung',
                'senioritas' => 'Senior',
                'gender' => 'L',
                'created_at' => now(),
            ]);
            // Guru 4
            $userGuru4Id = DB::table('users')->insertGetId([
                'name' => 'Seo Changbin',
                'serial_number' => '0081239242',
                'role' => 'guru',
                'password' => $password,
                'created_at' => now(),
            ]);

            $guru4Id = DB::table('guru')->insertGetId([
                'user_id' => $userGuru4Id,
                'nip' => '0081239242',
                'nama_guru' => 'Seo Changbin',
                'senioritas' => 'Senior',
                'gender' => 'L',
                'created_at' => now(),
            ]);

            // 5. Tabel: KELAS
            $kelas12Id = DB::table('kelas')->insertGetId([
                'tingkat' => 12,
                'jurusan' => 'RPL',
                'nomor_kelas' => '2',
                'tahun_ajaran_id' => $taId,
                'wali_kelas_id' => $guru1Id,
                'created_at' => now(),
            ]);

            $kelas11Id = DB::table('kelas')->insertGetId([
                'tingkat' => 11,
                'jurusan' => 'RPL',
                'nomor_kelas' => '1',
                'tahun_ajaran_id' => $taId,
                'wali_kelas_id' => $guru2Id,
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
                    'password' => $password,
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

            // 7. Tabel: JADWAL
            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
            $shiftWaktu = [
                ['Subuh', '00:00:00', '08:59:00', 'RPL Subuh'],
                ['Pagi', '09:00:00', '12:00:00', 'RPL Pagi'],
                ['Siang', '13:00:00', '18:00:00', 'RPL Siang'],
                ['Malam', '19:00:00', '23:59:00', 'RPL Malam'],
            ];

            $masterJadwal = [];
            foreach ($hariList as $hari) {
                foreach ($shiftWaktu as $shift) {
                    // MINGGU 1
                    $masterJadwal[] = [
                        'id' => DB::table('jadwal')->insertGetId([
                            'kelas_id' => $kelas12Id,
                            'mapel_id' => $mapelIds[$shift[3]],
                            'guru_id' => $guru1Id,
                            'lokasi_id' => $lokasiId,
                            'hari' => $hari,
                            'minggu' => 1,
                            'jam_mulai' => $shift[1],
                            'jam_selesai' => $shift[2],
                            'created_at' => now(),
                        ]),
                        'hari' => $hari,
                        'minggu' => 1,
                    ];

                    // MINGGU 2
                    $masterJadwal[] = [
                        'id' => DB::table('jadwal')->insertGetId([
                            'kelas_id' => $kelas12Id,
                            'mapel_id' => $mapelIds['Matematika'],
                            'guru_id' => $guru1Id,
                            'lokasi_id' => $lokasiId,
                            'hari' => $hari,
                            'minggu' => 2,
                            'jam_mulai' => $shift[1],
                            'jam_selesai' => $shift[2],
                            'created_at' => now(),
                        ]),
                        'hari' => $hari,
                        'minggu' => 2,
                    ];
                }
            }

            // 8. LOOPING DATA HARIAN (PERBAIKAN INFINITE LOOP)
            $start = Carbon::create(2026, 2, 8);
            $end = Carbon::create(2026, 2, 16);
            $tglMulaiSekolah = Carbon::parse('2026-01-05');
            $hariIndo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];

            while ($start <= $end) {
                $namaHari = $hariIndo[$start->format('l')];
                $tgl = $start->toDateString();

                // PERBAIKAN: Gunakan copy() agar $start tidak berubah saat panggil startOfWeek()
                $startDateRef = $tglMulaiSekolah->copy()->startOfWeek();
                $currentDateRef = $start->copy()->startOfWeek();
                
                $diffInWeeks = $currentDateRef->diffInWeeks($startDateRef);
                $mingguKe = ($diffInWeeks % 2 == 0) ? 1 : 2;

                foreach ($masterJadwal as $mj) {
                    if ($mj['hari'] == $namaHari && $mj['minggu'] == $mingguKe) {
                        $sesiId = DB::table('sesi_presensi')->insertGetId([
                            'jadwal_id' => $mj['id'],
                            'tanggal' => $tgl,
                            'token_qr' => 'TOKEN-' . bin2hex(random_bytes(4)),
                            'created_at' => $start->copy()->addHours(8),
                        ]);

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
                $start->addDay(); // Sekarang tanggal akan maju dengan benar
            }

            // 9. Tabel: PERIZINAN SISWA
            DB::table('perizinan')->insert([
                'siswa_id' => $siswaIds['2122003'],
                'tgl_izin' => '2026-02-08',
                'jenis_izin' => 'izin',
                'alasan' => 'Urusan keluarga',
                'bukti_gambar' => 'izin_siswa.jpg',
                'status_izin' => 'pending',
                'validator_id' => $guru1Id,
                'created_at' => now(),
            ]);

            // 10. Tabel: PERIZINAN GURU
            DB::table('perizinan_guru')->insert([
                [
                    'guru_id' => $guru1Id,
                    'tgl_izin' => '2026-02-17',
                    'jenis_izin' => 'sakit',
                    'alasan' => 'Demam tinggi',
                    'bukti_gambar' => 'surat_dokter.jpg',
                    'status_izin' => 'disetujui_kepsek',
                    'admin_id' => $adminUserId,
                    'kepsek_id' => $kepsekUserId,
                    'created_at' => now(),
                ],
                [
                    'guru_id' => $guru2Id,
                    'tgl_izin' => '2026-02-18',
                    'jenis_izin' => 'izin',
                    'alasan' => 'Pelatihan',
                    'bukti_gambar' => 'tugas.jpg',
                    'status_izin' => 'disetujui_admin',
                    'admin_id' => $adminUserId,
                    'kepsek_id' => null,
                    'created_at' => now(),
                ]
            ]);

            // 11. Tabel: HARI LIBUR
            DB::table('hari_libur')->insert([
                ['tanggal' => '2026-03-01', 'keterangan' => 'Libur Hari Raya', 'created_at' => now()],
                ['tanggal' => '2026-05-01', 'keterangan' => 'Hari Buruh', 'created_at' => now()],
            ]);

            // // 12. Tabel: JADWAL PIKET
            // DB::table('jadwal_piket')->insert([
            //     ['guru_id' => $guru2Id, 'tanggal' => Carbon::now()->toDateString(), 'created_at' => now()],
            //     ['guru_id' => $guru3Id, 'tanggal' => Carbon::now()->toDateString(), 'created_at' => now()],
            // ]);

            DB::commit();
            $this->command->info("Seeder Selesai!");
            $this->command->warn("Info: Guru 1 Mengajar 24 Jam. Guru 2 & 3 Bertugas Piket.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Terjadi Error: " . $e->getMessage());
        }
    }
}