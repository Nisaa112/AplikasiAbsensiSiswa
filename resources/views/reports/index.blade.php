@extends('layouts.app')

@section('title', 'Laporan & Statistik')
@section('page-title', 'Laporan Kehadiran')

@section('extra-css')
<style>
    .rounded-card { border-radius: 28px !important; }
    .btn-teal { background-color: #134B46; color: white; border: none; transition: 0.3s; }
    .btn-teal:hover { background-color: #0d312d; color: white; transform: translateY(-2px); }
    .btn-orange { background-color: #F57C00; color: white; border: none; transition: 0.3s; }
    .btn-orange:hover { background-color: #e67600; color: white; transform: translateY(-2px); }
    .form-label { font-weight: 700; color: #666; font-size: 12px; letter-spacing: 0.5px; }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">
    
    <!-- SECTION 1: GRAFIK & FILTER VISUAL -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-card p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow me-2 text-teal"></i>Analistik Kehadiran</h5>
                    
                    <!-- Quick Filters for Chart -->
                    <div class="d-flex flex-wrap gap-2">
                        <select id="filterStatus" class="form-select border-0 bg-light rounded-pill px-3 py-2 small fw-bold shadow-none" style="width: auto;">
                            <option value="hadir">Status: Hadir</option>
                            <option value="sakit">Status: Sakit</option>
                            <option value="izin">Status: Izin</option>
                            <option value="alpa">Status: Alpa</option>
                        </select>
                    </div>
                </div>
                
                <div style="height: 350px;">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: EXPORT WIZARD (Form Ekspor) -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm rounded-card">
                <div class="card-header bg-white border-0 p-4 pb-0 text-center">
                    <div class="icon-box mx-auto mb-3 d-flex align-items-center justify-content-center" 
                         style="width: 65px; height: 65px; background-color: #eef4f3; border-radius: 20px;">
                        <i class="bi bi-file-earmark-arrow-down fs-2 text-teal"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Unduh Laporan Resmi</h4>
                    <p class="text-secondary small">Pilih kriteria data yang ingin Anda ekspor ke dalam dokumen</p>
                </div>

                <div class="card-body p-4 lg:p-5">
                    <form action="#" id="exportForm" method="GET">
                        <div class="row">
                            <!-- Tahun Ajaran -->
                            <div class="col-md-6 mb-4">
                                <label class="form-label text-uppercase">Tahun Akademik</label>
                                <select name="tahun_ajaran_id" class="form-select border-0 bg-light p-3 rounded-4 shadow-none" required>
                                    @foreach($tahunAjaran as $t)
                                        <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Semester -->
                            <div class="col-md-6 mb-4">
                                <label class="form-label text-uppercase">Semester</label>
                                <select name="semester" class="form-select border-0 bg-light p-3 rounded-4 shadow-none" required>
                                    <option value="Ganjil">Ganjil</option>
                                    <option value="Genap">Genap</option>
                                </select>
                            </div>
                            <!-- Pilih Kelas -->
                            <div class="col-12 mb-4">
                                <label class="form-label text-uppercase">Pilih Kelas</label>
                                <select name="kelas_id" class="form-select border-0 bg-light p-3 rounded-4 shadow-none" required>
                                    <option value="" disabled selected>-- Pilih Kelas --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <button type="button" onclick="submitExport('excel')" class="btn btn-teal w-100 py-3 rounded-4 fw-bold shadow-sm">
                                    <i class="bi bi-file-earmark-excel me-2"></i> Ekspor ke Excel
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" onclick="submitExport('pdf')" class="btn btn-orange w-100 py-3 rounded-4 fw-bold shadow-sm">
                                    <i class="bi bi-file-earmark-pdf me-2"></i> Ekspor ke PDF
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. FUNGSI EKSPOR
    function submitExport(type) {
        const form = document.getElementById('exportForm');
        const baseUrl = type === 'excel' ? "{{ route('reports.excel') }}" : "{{ route('reports.pdf') }}";
        
        // Ambil data form
        const params = new URLSearchParams(new FormData(form)).toString();
        
        if(!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Buka link download di tab baru
        window.location.href = `${baseUrl}?${params}`;
    }

    // 2. FUNGSI GRAFIK (AJAX)
    let myChart;
    function loadChartData(status = 'hadir') {
        fetch(`/api/report/chart?status=${status}`) // Sesuaikan dengan URL API Anda
            .then(res => res.json())
            .then(json => {
                const ctx = document.getElementById('attendanceChart').getContext('2d');
                const labels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agt", "Sep", "Okt", "Nov", "Des"];
                
                // Logika pemrosesan data untuk Chart.js bisa ditambahkan di sini
                // Dummy Data jika API belum siap:
                const dummyData = [10, 45, 30, 70, 80, 50, 90, 60, 100, 85, 95, 110];

                if (myChart) myChart.destroy();
                
                myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Absensi',
                            data: dummyData,
                            backgroundColor: '#134B46',
                            borderRadius: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
                    }
                });
            });
    }

    document.getElementById('filterStatus').addEventListener('change', (e) => loadChartData(e.target.value));
    loadChartData(); // Inisialisasi awal
</script>
@endsection