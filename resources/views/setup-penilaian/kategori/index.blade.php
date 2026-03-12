@extends('layouts.app')

@section('title', 'Kategori Penilaian')
@section('page-title', 'Manajemen Indikator Sikap')

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
                     style="width: 55px; height: 55px; background-color: #f0f7ff;">
                    <i class="bi bi-journal-check fs-4" style="color: #007bff;"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Total {{ count($categories) }} Indikator</h5>
                    <p class="text-secondary small mb-0">Kriteria penilaian karakter siswa & guru</p>
                </div>
            </div>
            <button class="btn btn-teal-dark px-4 py-2 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-2"></i> Tambah Indikator
            </button>
        </div>
    </div>

    <!-- TABEL KATEGORI -->
    <div class="card border-0 shadow-sm rounded-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">No</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Indikator Penilaian</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Deskripsi</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center">Tipe</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center">Status</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $colors = [
                            ['bg' => 'rgba(19, 75, 70, 0.1)', 'border' => '#134B46'],
                            ['bg' => 'rgba(255, 94, 94, 0.1)', 'border' => '#FF5E5E'],
                            ['bg' => 'rgba(0, 123, 255, 0.1)', 'border' => '#007bff'],
                        ];
                    @endphp
                    
                    @forelse($categories as $index => $item)
                    <tr class="item-row">
                        <td class="ps-4 fw-bold text-secondary text-center">{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @php $style = $colors[$item->id % count($colors)]; @endphp
                                <div class="avatar-box me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                                    style="background-color: {{ $style['bg'] }}; border: 2px solid {{ $style['border'] }}; color: {{ $style['border'] }}; width: 40px; height: 40px; border-radius: 12px;">
                                    {{ strtoupper(substr($item->name, 0, 1)) }}
                                </div>
                                <div class="fw-bold text-dark search-name">{{ $item->name }}</div>
                            </div>
                        </td>
                        <td class="small text-secondary text-truncate" style="max-width: 200px;">
                            {{ $item->description ?? '-' }}
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill px-3 {{ $item->type == 'student' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning' }}">
                                {{ strtoupper($item->type) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill px-3 {{ $item->is_active ? 'bg-teal-subtle text-teal' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $item->is_active ? 'AKTIF' : 'NON-AKTIF' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('web.pertanyaan.index', ['category_id' => $item->id]) }}" 
                                class="btn btn-light btn-sm rounded-3 border px-2 text-teal-dark" 
                                title="Isi Pertanyaan">
                                    <i class="bi bi-patch-plus-fill"></i>
                                </a>

                                <button class="btn btn-light btn-sm rounded-3 border px-2 text-primary edit-btn" 
                                        data-id="{{ $item->id }}" 
                                        data-name="{{ $item->name }}" 
                                        data-desc="{{ $item->description }}" 
                                        data-type="{{ $item->type }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                
                                <form action="{{ route('web.kategori-penilaian.destroy', $item->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm rounded-3 border px-2 text-danger" onclick="return confirm('Hapus indikator ini?')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary italic">Belum ada indikator penilaian.</td>
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
                <h5 class="fw-bold text-teal-dark">Tambah Indikator Sikap</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('web.kategori-penilaian.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NAMA INDIKATOR</label>
                        <input type="text" name="name" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" placeholder="Cth: Kesopanan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">TIPE PENILAIAN</label>
                        <select name="type" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                            <option value="student">SISWA</option>
                            <option value="employee">GURU / STAF</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">DESKRIPSI (OPSIONAL)</label>
                        <textarea name="description" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" rows="3" placeholder="Penjelasan indikator..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4">Simpan Indikator</button>
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
                <h5 class="fw-bold text-teal-dark">Update Indikator</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NAMA INDIKATOR</label>
                        <input type="text" name="name" id="edit_name" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">TIPE PENILAIAN</label>
                        <select name="type" id="edit_type" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                            <option value="student">SISWA</option>
                            <option value="employee">GURU / STAF</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">DESKRIPSI</label>
                        <textarea name="description" id="edit_desc" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4">Update Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .rounded-card { border-radius: 24px !important; }
    .btn-teal-dark { background-color: #134B46; color: white; border: none; transition: 0.3s; }
    .btn-teal-dark:hover { background-color: #0f3632; color: white; transform: translateY(-2px); }
    .bg-teal-subtle { background-color: #e6fcf5 !important; color: #0ca678 !important; }
    .bg-primary-subtle { background-color: #e7f3ff !important; color: #007bff !important; }
    .bg-warning-subtle { background-color: #fff4e6 !important; color: #fd7e14 !important; }
</style>
@endsection

@section('extra-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModal = new bootstrap.Modal(document.getElementById('modalEdit'));
        
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('formEdit').action = `/kategori-penilaian/${id}`;
                document.getElementById('edit_name').value = this.getAttribute('data-name');
                document.getElementById('edit_type').value = this.getAttribute('data-type');
                document.getElementById('edit_desc').value = this.getAttribute('data-desc');
                editModal.show();
            });
        });
    });
</script>
@endsection