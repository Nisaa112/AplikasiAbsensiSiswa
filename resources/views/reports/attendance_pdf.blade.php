<!DOCTYPE html>
<html>
<head>
    <title>Laporan Absensi Attendia</title>
    <style>
        /* Mengatur margin halaman PDF */
        @page { margin: 0; }
        
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11px; 
            color: #2D3436; 
            margin: 0; 
            padding: 0;
            background-color: #ffffff;
        }

        /* Header Section - Mengikuti style Teal Header di App */
        .header-container {
            background-color: #134B46; /* Primary Teal dari App */
            color: white;
            padding: 40px 50px;
            border-bottom-right-radius: 40px;
            border-bottom-left-radius: 40px;
            text-align: left;
        }

        .header-container h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .header-container p {
            margin: 5px 0 0 0;
            font-size: 12px;
            opacity: 0.8;
        }

        /* Content Wrapper */
        .content {
            padding: 30px 45px;
        }

        /* Info Card Style - Seperti kartu di Monitoring Page */
        .info-card {
            background-color: #ffffff;
            border: 1px solid #f0f0f0;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }

        .info-table {
            width: 100%;
            border: none;
        }

        .info-table td {
            border: none;
            padding: 4px 0;
            vertical-align: top;
        }

        .label {
            color: #95a5a6;
            font-size: 10px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }

        .value {
            color: #134B46;
            font-weight: bold;
            font-size: 13px;
        }

        /* Table Style - Clean & Modern */
        .main-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
        }

        .main-table th {
            background-color: #f8fbfb;
            color: #134B46;
            padding: 15px 12px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #134B46;
            letter-spacing: 0.5px;
        }

        .main-table td {
            padding: 12px;
            border-bottom: 1px solid #f1f1f1;
            vertical-align: middle;
        }

        /* Status Pills - Konsisten dengan warna Status di App */
        .status-badge {
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            min-width: 60px;
        }

        .bg-hadir { background-color: #e8f5e9; color: #2e7d32; } /* Hijau App */
        .bg-izin { background-color: #fff3e0; color: #ef6c00; }  /* Orange App */
        .bg-alpa { background-color: #ffebee; color: #c62828; }  /* Merah App */

        .student-name {
            font-weight: bold;
            color: #2d3436;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .student-nisn {
            color: #b2bec3;
            font-size: 9px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 30px;
            width: 100%;
            text-align: center;
            color: #b2bec3;
            font-size: 9px;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <h1>Laporan Kehadiran</h1>
        <p>Attendia Digital Attendance System • Report ID: #{{ rand(1000, 9999) }}</p>
    </div>

    <div class="content">
        <!-- Info Card -->
        <div class="info-card">
            <table class="info-table">
                <tr>
                    <td width="35%">
                        <span class="label">Kelas</span>
                        <span class="value">{{ $kelas->nama_kelas }}</span>
                    </td>
                    <td width="35%">
                        <span class="label">Semester</span>
                        <span class="value">{{ strtoupper($semester) }}</span>
                    </td>
                    <td width="30%" style="text-align: right;">
                        <span class="label">Tanggal Cetak</span>
                        <span class="value">{{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top: 15px;">
                        <span class="label">Wali Kelas</span>
                        <span class="value">{{ $kelas->waliKelas->nama_guru ?? '-' }}</span>
                    </td>
                    <td style="padding-top: 15px; text-align: right;">
                        <span class="label">Status Laporan</span>
                        <span class="value" style="color: #2e7d32;">Verified by System</span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Data Table -->
        <table class="main-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="35%">Informasi Siswa</th>
                    <th width="25%">Mata Pelajaran</th>
                    <th width="20%">Waktu Absen</th>
                    <th width="15%" style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($absensi as $key => $row)
                <tr>
                    <td style="color: #b2bec3;">{{ $key + 1 }}</td>
                    <td>
                        <div class="student-name">{{ strtoupper($row->siswa->nama_siswa) }}</div>
                        <div class="student-nisn">NISN: {{ $row->siswa->nisn }}</div>
                    </td>
                    <td>{{ $row->sesi->jadwal->mapel->nama_mapel }}</td>
                    <td style="color: #636e72;">
                        {{ \Carbon\Carbon::parse($row->waktu_scan)->format('d/m/y') }}<br>
                        <small>{{ \Carbon\Carbon::parse($row->waktu_scan)->format('H:i') }} WIB</small>
                    </td>
                    <td style="text-align: center;">
                        @php 
                            $st = strtolower($row->status);
                            $class = in_array($st, ['sakit', 'izin']) ? 'izin' : $st;
                        @endphp
                        <span class="status-badge bg-{{ $class }}">
                            {{ strtoupper($row->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        Laporan ini digenerate secara otomatis oleh <strong>Attendia Dashboard</strong> pada {{ date('d/m/Y H:i') }}
    </div>
</body>
</html>