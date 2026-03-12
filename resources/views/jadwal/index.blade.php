@extends('layouts.app')

@section('title', 'Jadwal KBM')
@section('page-title', 'Manajemen Jadwal Pelajaran')

@section('content')
<div class="container-fluid px-0">
    <!-- NOTIFIKASI -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- CARD RINGKASAN -->
    <div class="card border-0 shadow-sm rounded-card mb-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center">
                <div class="icon-box rounded-4 me-3 d-flex align-items-center justify-content-center" 
                     style="width: 55px; height: 55px; background-color: #eef4f3;">
                    <i class="bi bi-calendar-event fs-4" style="color: #134B46;"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Penjadwalan KBM</h5>
                    <p class="text-secondary small mb-0">Atur jam belajar mengajar guru dan siswa</p>
                </div>
            </div>
            <button class="btn btn-teal-dark px-4 py-2 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-2"></i> Tambah Jadwal
            </button>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="card border-0 shadow-sm rounded-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" id="scheduleTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">No</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-start">Mata Pelajaran & Kelas</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-start">Guru Pengampu</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Hari & Waktu</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $avatarStyles = [
                            ['bg' => 'rgba(19, 75, 70, 0.1)',  'border' => '#134B46'], 
                            ['bg' => 'rgba(0, 123, 255, 0.1)', 'border' => '#007bff'], 
                            ['bg' => 'rgba(240, 140, 0, 0.1)', 'border' => '#f08c00'], 
                        ];
                    @endphp
                    
                    @forelse($data as $index => $jadwal)
                    <tr class="schedule-row">
                        <td class="fw-bold text-secondary">{{ $index + 1 }}</td>
                        <td class="text-start">
                            <div class="d-flex align-items-center">
                                @php $style = $avatarStyles[$jadwal->id % count($avatarStyles)]; @endphp
                                <div class="avatar-box me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                                    style="background-color: {{ $style['bg'] }}; border: 2px solid {{ $style['border'] }}; color: {{ $style['border'] }}; width: 42px; height: 42px; border-radius: 14px; font-size: 16px;">
                                    {{ strtoupper(substr($jadwal->mapel->nama_mapel, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark search-target">{{ $jadwal->mapel->nama_mapel }}</div>
                                    <div class="text-secondary small">Kelas: {{ $jadwal->kelas->nama_kelas }} (Mgg-{{ $jadwal->minggu }})</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-start">
                            <div class="fw-bold small">{{ $jadwal->guru->nama_guru }}</div>
                            <div class="text-secondary small">Lokasi: {{ $jadwal->lokasi->nama_lokasi }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-3">{{ $jadwal->hari }}</span>
                            <div class="small fw-bold mt-1 text-teal-dark">{{ substr($jadwal->jam_mulai,0,5) }} - {{ substr($jadwal->jam_selesai,0,5) }}</div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-light btn-sm rounded-3 border px-2 text-primary hover-shadow edit-btn" 
                                        data-id="{{ $jadwal->id }}" 
                                        data-kelas="{{ $jadwal->kelas_id }}" 
                                        data-mapel="{{ $jadwal->mapel_id }}" 
                                        data-guru="{{ $jadwal->guru_id }}" 
                                        data-lokasi="{{ $jadwal->lokasi_id }}" 
                                        data-minggu="{{ $jadwal->minggu }}" 
                                        data-hari="{{ $jadwal->hari }}" 
                                        data-mulai="{{ $jadwal->jam_mulai }}" 
                                        data-selesai="{{ $jadwal->jam_selesai }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('schedules.destroy', $jadwal->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm rounded-3 border px-2 text-danger hover-shadow" onclick="return confirm('Hapus jadwal ini?')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-secondary italic">Belum ada jadwal yang diatur.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-teal-dark">Buat Jadwal Baru</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('schedules.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">KELAS</label>
                            <select name="kelas_id" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                                <option value="" disabled selected>-- Pilih Kelas --</option>
                                @foreach($classes as $c) <option value="{{ $c->id }}">{{ $c->nama_kelas }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">MATA PELAJARAN</label>
                            <select name="mapel_id" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                                <option value="" disabled selected>-- Pilih Mapel --</option>
                                @foreach($subjects as $m) <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">GURU PENGAMPU</label>
                            <select name="guru_id" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                                <option value="" disabled selected>-- Pilih Guru --</option>
                                @foreach($teachers as $g) <option value="{{ $g->id }}">{{ $g->nama_guru }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">LOKASI / RUANGAN</label>
                            <select name="lokasi_id" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                                @foreach($locations as $l) <option value="{{ $l->id }}">{{ $l->nama_lokasi }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">MINGGU KE</label>
                            <select name="minggu" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                                <option value="1">Minggu 1 (Ganjil)</option>
                                <option value="2">Minggu 2 (Genap)</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">HARI</label>
                            <select name="hari" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h) <option value="{{ $h }}">{{ $h }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">JAM MULAI</label>
                            <input type="time" name="jam_mulai" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">JAM SELESAI</label>
                            <input type="time" name="jam_selesai" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4 fw-bold">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT (Dynamic) -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-teal-dark">Edit Jadwal</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">KELAS</label>
                            <select name="kelas_id" id="edit_kelas" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                                @foreach($classes as $c) <option value="{{ $c->id }}">{{ $c->nama_kelas }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">MATA PELAJARAN</label>
                            <select name="mapel_id" id="edit_mapel" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                                @foreach($subjects as $m) <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option> @endforeach
                            </select>
                        </div>
                        <!-- Tambahkan field lainnya sesuai store modal -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">GURU</label>
                            <select name="guru_id" id="edit_guru" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                                @foreach($teachers as $g) <option value="{{ $g->id }}">{{ $g->nama_guru }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">LOKASI</label>
                            <select name="lokasi_id" id="edit_lokasi" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                                @foreach($locations as $l) <option value="{{ $l->id }}">{{ $l->nama_lokasi }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">HARI</label>
                            <select name="hari" id="edit_hari" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $h) <option value="{{ $h }}">{{ $h }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">MINGGU KE</label>
                            <select name="minggu" id="edit_minggu" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                                <option value="1">1</option><option value="2">2</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">JAM MULAI</label>
                            <input type="time" name="jam_mulai" id="edit_mulai" class="form-control rounded-3 border-0 bg-light p-3 shadow-none">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-secondary">JAM SELESAI</label>
                            <input type="time" name="jam_selesai" id="edit_selesai" class="form-control rounded-3 border-0 bg-light p-3 shadow-none">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4 fw-bold">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .rounded-card { border-radius: 24px !important; }
    .btn-teal-dark { background-color: #134B46; color: white; border: none; transition: 0.3s; }
    .btn-teal-dark:hover { background-color: #0d312d; color: white; transform: translateY(-2px); }
    .table tbody td { border-top: 1px solid #f8fafb; padding: 1.2rem 0.5rem; }
</style>
@endsection

@section('extra-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- SEARCH REAL-TIME ---
        const searchInput = document.getElementById('mainSearch');
        const rows = document.querySelectorAll('.schedule-row');
        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                const query = searchInput.value.toLowerCase();
                rows.forEach(row => {
                    const target = row.querySelector('.search-target').textContent.toLowerCase();
                    row.style.display = target.includes(query) ? '' : 'none';
                });
            });
        }

        // --- EDIT MODAL ---
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModal = new bootstrap.Modal(document.getElementById('modalEdit'));
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('formEdit').action = `/schedules/${id}`;
                document.getElementById('edit_kelas').value = this.getAttribute('data-kelas');
                document.getElementById('edit_mapel').value = this.getAttribute('data-mapel');
                document.getElementById('edit_guru').value = this.getAttribute('data-guru');
                document.getElementById('edit_lokasi').value = this.getAttribute('data-lokasi');
                document.getElementById('edit_minggu').value = this.getAttribute('data-minggu');
                document.getElementById('edit_hari').value = this.getAttribute('data-hari');
                document.getElementById('edit_mulai').value = this.getAttribute('data-mulai');
                document.getElementById('edit_selesai').value = this.getAttribute('data-selesai');
                editModal.show();
            });
        });
    });
</script>
@endsection