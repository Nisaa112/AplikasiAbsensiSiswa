@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Overview Dashboard')

@section('content')
<div class="container-fluid px-0">
    <!-- Row 1: Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Card Total Siswa -->
        <div class="col-12 col-sm-6 col-xl-6">
            <div class="card border-0 shadow-sm rounded-card p-2 p-md-3 hover-card">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box rounded-4 me-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 60px; height: 60px; background-color: #eef4f3;">
                        <i class="bi bi-people-fill fs-3" style="color: #134B46;"></i>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-secondary small mb-0 fw-bold text-uppercase tracking-wider text-truncate">Total Siswa</p>
                        <h3 class="fw-black mb-0 responsive-h3" style="color: #134B46;">1,240</h3>
                        <small class="text-success fw-bold text-truncate d-block"><i class="bi bi-arrow-up"></i> +24 Aktif</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Total Guru -->
        <div class="col-12 col-sm-6 col-xl-6">
            <div class="card border-0 shadow-sm rounded-card p-2 p-md-3 hover-card">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box rounded-4 me-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 60px; height: 60px; background-color: #fff4e6;">
                        <i class="bi bi-person-badge-fill fs-3" style="color: #F57C00;"></i>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-secondary small mb-0 fw-bold text-uppercase tracking-wider text-truncate">Total Guru</p>
                        <h3 class="fw-black mb-0 responsive-h3" style="color: #134B46;">85</h3>
                        <small class="text-secondary fw-bold text-truncate d-block">Semua Mapel</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Grafik Kehadiran + Filter -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-card p-3 p-md-4">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
                    <h5 class="fw-bold mb-0 text-center text-xl-start">Grafik Kehadiran Siswa</h5>
                    
                    <!-- Filters Grid -->
                    <div class="row g-2 w-100 w-xl-auto">
                        <div class="col-12 col-sm-4">
                            <select class="form-select border shadow-sm rounded-pill px-3 py-2 bg-light small fw-bold w-100">
                                <option selected>Angkatan</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4">
                            <select class="form-select border shadow-sm rounded-pill px-3 py-2 bg-light small fw-bold w-100">
                                <option selected>Jurusan</option>
                                <option>RPL</option>
                                <option>TKJ</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4">
                            <select class="form-select border shadow-sm rounded-pill px-3 py-2 bg-light small fw-bold w-100">
                                <option selected>Kelas</option>
                                <option>X RPL 1</option>
                                <option>XI RPL 2</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Container Chart yang responsif -->
                <div class="chart-container" style="position: relative; height:40vh; min-height: 250px; width: 100%;">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Global Rounded */
    .rounded-card { border-radius: 24px !important; }
    .fw-black { font-weight: 900; }
    
    /* Hover Effect */
    .hover-card { transition: all 0.3s ease; }
    .hover-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important; }

    /* Responsive Typography */
    .responsive-h3 { font-size: calc(1.3rem + 0.6vw); }
    
    @media (max-width: 576px) {
        .card-body { padding: 1rem 0.5rem; }
        .icon-box { width: 50px !important; height: 50px !important; }
        .icon-box i { font-size: 1.5rem !important; }
    }

    /* Customizing Select Focus */
    .form-select:focus {
        border-color: #134B46;
        box-shadow: 0 0 0 0.25rem rgba(19, 75, 70, 0.1);
    }
</style>
@endsection

@section('extra-js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        
        // Gradient creation
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(19, 75, 70, 0.3)');
        gradient.addColorStop(1, 'rgba(19, 75, 70, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                datasets: [{
                    label: 'Kehadiran (%)',
                    data: [95, 88, 92, 98, 85, 90],
                    borderColor: '#134B46',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#134B46',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#134B46',
                        titleFont: { family: 'Plus Jakarta Sans' },
                        bodyFont: { family: 'Plus Jakarta Sans' },
                        padding: 12,
                        cornerRadius: 10
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { borderDash: [5, 5], color: '#f0f0f0' },
                        ticks: { 
                            font: { family: 'Plus Jakarta Sans', size: 10 },
                            stepSize: 20
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 10 } }
                    }
                }
            }
        });
    });
</script>
@endsection