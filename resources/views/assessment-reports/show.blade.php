@extends('layouts.app')

@section('title', 'Detail Rapor Sikap')
@section('page-title', 'Analisis Karakter: ' . $siswa->name)

@section('content')
<div class="container-fluid px-0">
    <div class="row g-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold mb-0">Grafik Radar Karakter</h5>
                            <small class="text-secondary">Visualisasi kekuatan karakter siswa</small>
                        </div>
                    </div>
                    
                    <div style="position: relative; height: 380px;" class="mt-2">
                        @if($scores->count() > 0)
                            <canvas id="radarChart"></canvas>
                        @else
                            <div class="h-100 d-flex flex-column align-items-center justify-content-center text-secondary opacity-50">
                                <i class="bi bi-graph-up fs-1 mb-2"></i>
                                <p>Data penilaian belum tersedia untuk periode ini.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="avatar-box me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm bg-teal-dark text-white" 
                             style="width: 48px; height: 48px; border-radius: 16px;">
                            {{ strtoupper(substr($siswa->name, 0, 1)) }}
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">Rincian Skor</h5>
                            <p class="small text-secondary mb-0">Rata-rata per kategori</p>
                        </div>
                    </div>

                    <div class="list-group list-group-flush mb-4">
                        @forelse($scores as $score)
                        <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 py-3 border-bottom-dashed">
                            <div class="d-flex align-items-center">
                                <div class="p-2 bg-light rounded-3 me-3 text-teal-dark">
                                    <i class="bi bi-bookmark-star-fill"></i>
                                </div>
                                <div class="fw-bold text-dark">{{ $score->name }}</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-teal-dark rounded-pill px-3">{{ number_format($score->average_score, 1) }}</span>
                                <div class="small text-secondary mt-1" style="font-size: 10px;">Skala 5.0</div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" style="width: 80px;" class="opacity-25 mb-3">
                            <p class="text-secondary italic">Bel  um ada rincian nilai.</p>
                        </div>
                        @endforelse
                    </div>

                    <div class="mt-auto">
                        <a href="{{ route('web.assessment-reports.index') }}" class="btn btn-light w-100 rounded-pill border py-2 fw-bold text-secondary hover-shadow">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .rounded-card { border-radius: 24px !important; }
    .bg-teal-dark { background-color: #134B46 !important; }
    .text-teal-dark { color: #134B46 !important; }
    .bg-teal-subtle { background-color: #e6fcf5 !important; color: #134B46 !important; }
    .border-bottom-dashed { border-bottom: 1px dashed #dee2e6 !important; }
    .hover-shadow:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important; transition: 0.3s; }
</style>
@endsection

@section('extra-js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const labelsData = {!! $scores->pluck('name')->toJson() !!};
        const scoresData = {!! $scores->pluck('average_score')->map(fn($v) => (float)$v)->toJson() !!};

        const ctx = document.getElementById('radarChart');
        if (ctx && labelsData.length > 0) {
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: labelsData,
                    datasets: [{
                        label: 'Skor Karakter',
                        data: scoresData,
                        fill: true,
                        backgroundColor: 'rgba(19, 75, 70, 0.2)',
                        borderColor: '#134B46',
                        pointBackgroundColor: '#134B46',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#134B46',
                        borderWidth: 3,
                        pointRadius: 4,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            angleLines: { 
                                display: true,
                                color: 'rgba(0,0,0,0.05)'
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            suggestedMin: 0,
                            suggestedMax: 5,
                            ticks: { 
                                stepSize: 1, 
                                display: true,
                                font: { size: 10 },
                                backdropColor: 'transparent'
                            },
                            pointLabels: {
                                font: {
                                    size: 11,
                                    weight: '600'
                                },
                                color: '#6c757d'
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#134B46',
                            titleFont: { size: 13 },
                            bodyFont: { size: 13 },
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return ' Skor: ' + context.raw.toFixed(1) + ' / 5.0';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection