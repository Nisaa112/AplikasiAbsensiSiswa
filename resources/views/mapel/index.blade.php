@extends('layouts.app')

@section('title', 'Mata Pelajaran')
@section('page-title', 'Manajemen Kurikulum')

@section('content')
<div class="container-fluid px-0">
    <!-- ALERT NOTIFIKASI -->
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
                     style="width: 55px; height: 55px; background-color: #fff4e6;">
                    <i class="bi bi-journal-text fs-4" style="color: #F57C00;"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Total {{ count($data) }} Mata Pelajaran</h5>
                    <p class="text-secondary small mb-0">Daftar kurikulum aktif di Attendia System</p>
                </div>
            </div>
            <button class="btn btn-teal-dark px-4 py-2 rounded-pill fw-bold shadow-sm w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-2"></i> Tambah Mapel
            </button>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="card border-0 shadow-sm rounded-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" id="subjectTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">No</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-start">Nama Mata Pelajaran</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Dibuat Pada</th>
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
                    
                    @forelse($data as $index => $mapel)
                    <tr class="subject-row">
                        <td class="fw-bold text-secondary">{{ $index + 1 }}</td>
                        <td class="text-start">
                            <div class="d-flex align-items-center">
                                @php $style = $avatarStyles[$mapel->id % count($avatarStyles)]; @endphp
                                <div class="avatar-box me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                                    style="background-color: {{ $style['bg'] }}; border: 2px solid {{ $style['border'] }}; color: {{ $style['border'] }}; width: 42px; height: 42px; border-radius: 14px; font-size: 16px;">
                                    {{ strtoupper(substr($mapel->nama_mapel, 0, 1)) }}
                                </div>
                                <div class="fw-bold text-dark search-name" style="font-size: 15px;">{{ $mapel->nama_mapel }}</div>
                            </div>
                        </td>
                        <td>
                            <span class="text-secondary small">{{ $mapel->created_at->format('d M Y') }}</span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-light btn-sm rounded-3 border px-2 text-primary hover-shadow edit-btn" 
                                        data-id="{{ $mapel->id }}" 
                                        data-nama="{{ $mapel->nama_mapel }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <form action="{{ route('subjects.destroy', $mapel->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm rounded-3 border px-2 text-danger hover-shadow" onclick="return confirm('Hapus mata pelajaran ini?')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-secondary italic">Belum ada data mata pelajaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-teal-dark">Tambah Mapel Baru</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('subjects.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NAMA MATA PELAJARAN</label>
                        <input type="text" name="nama_mapel" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" placeholder="Contoh: Matematika" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4 fw-bold">Simpan Mapel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-teal-dark">Update Nama Mapel</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NAMA MATA PELAJARAN</label>
                        <input type="text" name="nama_mapel" id="edit_nama" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4 fw-bold">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .rounded-card { border-radius: 24px !important; }
    .btn-teal-dark { background-color: #134B46; color: white; border: none; transition: 0.3s; }
    .btn-teal-dark:hover { background-color: #0d312d; color: white; transform: translateY(-2px); }
    .table thead th { border-bottom: none; }
    .table tbody td { border-top: 1px solid #f8fafb; padding: 1.1rem 0.5rem; }
    .hover-shadow:hover { box-shadow: 0 4px 8px rgba(0,0,0,0.1); background-color: #fff !important; border-color: #ddd !important; }
</style>
@endsection

@section('extra-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- SEARCH REAL-TIME ---
        const searchInput = document.getElementById('mainSearch');
        const rows = document.querySelectorAll('.subject-row');

        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                const query = searchInput.value.toLowerCase();
                rows.forEach(row => {
                    const name = row.querySelector('.search-name').textContent.toLowerCase();
                    row.style.display = (name.includes(query)) ? '' : 'none';
                });
            });
        }

        // --- EDIT MODAL ---
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModal = new bootstrap.Modal(document.getElementById('modalEdit'));
        
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nama = this.getAttribute('data-nama');

                document.getElementById('formEdit').action = `/subjects/${id}`;
                document.getElementById('edit_nama').value = nama;
                editModal.show();
            });
        });
    });
</script>
@endsection