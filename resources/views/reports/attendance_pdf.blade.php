<!DOCTYPE html>
<html>
<head>
    <title>Laporan Absensi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; text-transform: uppercase; font-size: 11px;}
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 5px; letter-spacing: 1px; }
        .subtitle { font-size: 13px; margin-bottom: 5px; color: #555; }
        .date { font-size: 11px; color: #888; margin-top: 5px; }
        
        /* Warna Status */
        .status-hadir { color: green; font-weight: bold; }
        .status-sakit, .status-izin { color: orange; font-weight: bold; }
        .status-alpa { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN KEHADIRAN SISWA</div>
        <!-- Menggunakan accessor nama_kelas dan relasi waliKelas -->
        <div class="subtitle">
            Kelas: <b>{{ $kelas->nama_kelas }}</b> &nbsp;|&nbsp; 
            Semester: <b>{{ strtoupper($semester) }}</b> &nbsp;|&nbsp; 
            Wali Kelas: <b>{{ $kelas->waliKelas->nama_guru ?? '-' }}</b>
        </div>
        <div class="date">Dicetak pada: {{ $tanggal }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Nama Siswa</th>
                <th width="30%">Mata Pelajaran</th>
                <th width="20%">Waktu Scan</th>
                <th width="20%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($absensi as $key => $row)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $row->siswa->nama_siswa ?? '-' }}</td>
                <td>{{ $row->sesi->jadwal->mapel->nama_mapel ?? '-' }}</td>
                <td>{{ $row->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    <!-- Pewarnaan teks berdasarkan status -->
                    @if(strtolower($row->status) == 'hadir')
                        <span class="status-hadir">HADIR</span>
                    @elseif(in_array(strtolower($row->status), ['sakit', 'izin']))
                        <span class="status-sakit">{{ strtoupper($row->status) }}</span>
                    @else
                        <span class="status-alpa">{{ strtoupper($row->status) }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">Belum ada data kehadiran untuk kelas ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>