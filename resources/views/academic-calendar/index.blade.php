@extends('layouts.app')

@section('title', 'Kalender Libur')
@section('page-title', 'Manajemen Hari Libur')

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
                     style="width: 55px; height: 55px; background-color: #ffeef0;">
                    <i class="bi bi-calendar-x fs-4" style="color: #dc3545;"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Kalender Libur</h5>
                    <p class="text-secondary small mb-0">Daftar hari libur nasional & agenda libur sekolah</p>
                </div>
            </div>
            <button class="btn btn-teal-dark px-4 py-2 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-2"></i> Tambah Libur
            </button>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="card border-0 shadow-sm rounded-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" id="liburTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">No</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-start">Keterangan Hari Libur</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Tanggal</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Sumber</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $avatarStyles = [
                            ['bg' => 'rgba(19, 75, 70, 0.1)',  'border' => '#134B46'], // Teal
                            ['bg' => 'rgba(0, 123, 255, 0.1)', 'border' => '#007bff'], // Biru
                            ['bg' => 'rgba(240, 140, 0, 0.1)', 'border' => '#f08c00'], // Kuning
                        ];
                    @endphp
                    @foreach($data as $index => $item)
                    <tr class="data-row">
                        <td class="fw-bold text-secondary">{{ $index + 1 }}</td>
                        <td class="text-start">
                            <div class="d-flex align-items-center">
                                @php $style = $avatarStyles[$index % 3]; @endphp
                                <div class="avatar-box me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                                    style="background-color: {{ $style['bg'] }}; border: 2px solid {{ $style['border'] }}; color: {{ $style['border'] }}; width: 42px; height: 42px; border-radius: 14px;">
                                    <i class="bi bi-balloon-heart"></i>
                                </div>
                                <div class="fw-bold text-dark search-desc">{{ $item['keterangan'] }}</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-3 search-date">
                                {{ \Carbon\Carbon::parse($item['tanggal'])->format('d M Y') }}
                            </span>
                        </td>
                        <td>
                            @if($item['is_nasional'])
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-3">NASIONAL</span>
                            @else
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3">SEKOLAH</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                @if(!$item['is_nasional'])
                                <button class="btn btn-light btn-sm rounded-3 border px-2 text-primary edit-btn" 
                                        data-id="{{ $item['id'] }}" 
                                        data-tgl="{{ $item['tanggal'] }}" 
                                        data-desc="{{ $item['keterangan'] }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('academic-calendar.destroy', $item['id']) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm rounded-3 border px-2 text-danger" onclick="return confirm('Hapus agenda libur sekolah ini?')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                                @else
                                <span class="text-muted small italic">System Locked</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
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
                <h5 class="fw-bold text-teal-dark">Tambah Agenda Libur</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('academic-calendar.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">TANGGAL LIBUR</label>
                        <input type="date" name="tanggal" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">KETERANGAN</label>
                        <input type="text" name="keterangan" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" placeholder="Contoh: Libur Akhir Semester" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4 fw-bold">Simpan</button>
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
                <h5 class="fw-bold text-teal-dark">Update Agenda Libur</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">TANGGAL</label>
                        <input type="date" name="tanggal" id="edit_tgl" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">KETERANGAN</label>
                        <input type="text" name="keterangan" id="edit_desc" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4 fw-bold">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .rounded-card { border-radius: 28px !important; }
    .btn-teal-dark { background-color: #134B46; color: white; border: none; transition: 0.3s; }
    .btn-teal-dark:hover { background-color: #0d312d; color: white; transform: translateY(-2px); }
    .table tbody td { border-top: 1px solid #f8fafb; padding: 1.1rem 0.5rem; }
    .bg-danger-subtle { background-color: #ffeef0 !important; color: #dc3545 !important; }
    .bg-primary-subtle { background-color: #e7f3ff !important; color: #007bff !important; }
</style>
@endsection

@section('extra-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- SEARCH REAL-TIME ---
        const searchInput = document.getElementById('mainSearch');
        const rows = document.querySelectorAll('.data-row');
        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                const query = searchInput.value.toLowerCase();
                rows.forEach(row => {
                    const desc = row.querySelector('.search-desc').textContent.toLowerCase();
                    const date = row.querySelector('.search-date').textContent.toLowerCase();
                    row.style.display = (desc.includes(query) || date.includes(query)) ? '' : 'none';
                });
            });
        }

        // --- EDIT MODAL ---
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModal = new bootstrap.Modal(document.getElementById('modalEdit'));
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('formEdit').action = `/academic-calendar/${id}`;
                document.getElementById('edit_tgl').value = this.getAttribute('data-tgl');
                document.getElementById('edit_desc').value = this.getAttribute('data-desc');
                editModal.show();
            });
        });
    });
</script>
@endsection