@extends('layouts.app')

@section('title', 'Laporan Penilaian Sikap')
@section('page-title', 'Rekapitulasi Karakter Siswa')

@section('content')
<div class="container-fluid px-0">
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-card bg-teal-dark text-white h-100">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="icon-box rounded-4 me-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 60px; height: 60px; background-color: rgba(255,255,255,0.1);">
                        <i class="bi bi-people fs-2"></i>
                    </div>
                    <div>
                        <p class="small fw-bold text-uppercase mb-0 opacity-75">Siswa Terfilter</p>
                        <h3 class="fw-black mb-0">{{ $data->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm rounded-card p-3 p-md-4 h-100">
                <form action="{{ route('web.assessment-reports.index') }}" method="GET" id="filterForm">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="hide-on-mobile">
                            <h6 class="fw-bold mb-0">Filter Laporan</h6>
                            <small class="text-secondary">Penyaringan data otomatis</small>
                        </div>
                        
                        <div class="row g-2 w-100 w-md-auto">
                            <div class="col-12 col-sm-4">
                                <select name="tahun_ajaran_id" onchange="this.form.submit()" class="form-select border shadow-sm rounded-pill px-3 py-2 bg-light small fw-bold w-100">
                                    <option value="">Tahun Aktif</option>
                                    @foreach($years as $y)
                                        <option value="{{ $y->id }}" {{ request('tahun_ajaran_id') == $y->id ? 'selected' : '' }}>
                                            {{ $y->tahun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-sm-4">
                                <select name="tingkat" onchange="this.form.submit()" class="form-select border shadow-sm rounded-pill px-3 py-2 bg-light small fw-bold w-100">
                                    <option value="">Semua Tingkat</option>
                                    @foreach([10, 11, 12] as $t)
                                        <option value="{{ $t }}" {{ request('tingkat') == $t ? 'selected' : '' }}>Kelas {{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-sm-4">
                                <select name="jurusan" onchange="this.form.submit()" class="form-select border shadow-sm rounded-pill px-3 py-2 bg-light small fw-bold w-100">
                                    <option value="">Semua Jurusan</option>
                                    @foreach($majors as $m)
                                        <option value="{{ $m }}" {{ request('jurusan') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-card p-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="p-2 bg-teal-subtle rounded-3 me-3">
                        <i class="bi bi-bar-chart-fill text-teal"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-teal">Visualisasi Rata-rata Karakter Per Kelas</h6>
                        <small class="text-secondary">Data perbandingan skor antar indikator penilaian</small>
                    </div>
                </div>
                
                @if($classAverages->count() > 0)
                    <div style="position: relative; height: 350px; width: 100%;">
                        <canvas id="characterChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-5 border rounded-card border-dashed">
                        <i class="bi bi-graph-down fs-2 d-block mb-2 opacity-25"></i>
                        <small class="text-secondary italic">Tidak ada data penilaian untuk ditampilkan dalam grafik.</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">Siswa</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center">NIS</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center">Status</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $siswa)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-box me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm bg-primary-subtle text-primary" 
                                     style="width: 42px; height: 42px; border-radius: 14px; flex-shrink: 0;">
                                    {{ strtoupper(substr($siswa->name, 0, 1)) }}
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 15px; max-width: 180px;">
                                        {{ $siswa->name }}
                                    </div>
                                    <div class="text-secondary small text-truncate" style="font-size: 11px;">
                                        @php $infoKelas = $siswa->anggotaKelas->first(); @endphp
                                        @if($infoKelas && $infoKelas->kelas)
                                            {{ $infoKelas->kelas->tingkat }} {{ $infoKelas->kelas->jurusan }} {{ $infoKelas->kelas->nomor_kelas }}
                                        @else
                                            <span class="text-danger fw-bold">BELUM MASUK KELAS</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border rounded-pill px-3">{{ $siswa->serial_number }}</span>
                        </td>
                        <td class="text-center">
                            @php 
                                $selectedYear = request('tahun_ajaran_id') ?: ($years->where('status', 1)->first()->id ?? null);
                                $hasAssessment = $siswa->assessmentsReceived->where('tahun_ajaran_id', $selectedYear)->count() > 0; 
                            @endphp
                            <span class="badge rounded-pill px-3 {{ $hasAssessment ? 'bg-teal-subtle text-teal' : 'bg-danger-subtle text-danger' }}">
                                <i class="bi {{ $hasAssessment ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                {{ $hasAssessment ? 'DINILAI' : 'BELUM' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('web.assessment-reports.show', ['id' => $siswa->id, 'tahun_ajaran_id' => request('tahun_ajaran_id')]) }}" 
                               class="btn btn-light btn-sm rounded-pill border px-3 text-teal fw-bold hover-shadow">
                                <i class="bi bi-bar-chart-line-fill me-1"></i> Analisis
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-secondary">
                            <i class="bi bi-search fs-2 d-block mb-3 opacity-25"></i>
                            Data tidak ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('characterChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const classData = @json($classAverages);
        const labels = Object.keys(classData);
        
        // Ambil kategori unik
        const categories = [];
        labels.forEach(label => {
            classData[label].forEach(item => {
                if (!categories.includes(item.nama_kategori)) categories.push(item.nama_kategori);
            });
        });

        // Daftar warna bertema Teal/Profesional
        const colorPalette = ['#134B46', '#2D7D75', '#45B2A8', '#73D2C9', '#A8E7E1'];

        const datasets = categories.map((cat, index) => {
            return {
                label: cat,
                data: labels.map(label => {
                    const found = classData[label].find(i => i.nama_kategori === cat);
                    return found ? parseFloat(found.avg_skor).toFixed(2) : 0;
                }),
                backgroundColor: colorPalette[index % colorPalette.length],
                borderRadius: 6,
                barPercentage: 0.8,
                categoryPercentage: 0.6
            };
        });

        new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        max: 5,
                        ticks: { stepSize: 1, font: { weight: 'bold' } },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: { grid: { display: false }, ticks: { font: { weight: 'bold' } } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                    tooltip: {
                        backgroundColor: '#134B46',
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: {
                            label: (context) => ` ${context.dataset.label}: ${context.raw}`
                        }
                    }
                }
            }
        });
    });
</script>

<style>
    .rounded-card { border-radius: 24px !important; }
    .bg-teal-dark { background-color: #134B46 !important; }
    .fw-black { font-weight: 900; }
    .text-teal { color: #134B46 !important; }
    .bg-teal-subtle { background-color: #e6fcf5 !important; color: #134B46 !important; }
    .bg-danger-subtle { background-color: #fff5f5 !important; color: #ff6b6b !important; }
    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
    .hover-shadow:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important; transition: 0.3s; }
</style>
@endsection