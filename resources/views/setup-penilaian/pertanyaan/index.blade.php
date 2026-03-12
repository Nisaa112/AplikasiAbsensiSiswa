@extends('layouts.app')

@section('title', 'Daftar Pertanyaan')
@section('page-title', 'Kelola Butir Penilaian')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div>
            <a href="{{ route('web.kategori-penilaian.index') }}" class="btn btn-link text-decoration-none text-secondary p-0 mb-2">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kategori
            </a>
            <h4 class="fw-bold mb-0">Indikator: <span class="text-teal-dark">{{ $category->name }}</span></h4>
            <p class="text-secondary small mb-0">Tipe: {{ strtoupper($category->type) }}</p>
        </div>
        <button class="btn btn-teal-dark px-4 py-2 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-lg me-2"></i> Tambah Pertanyaan
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase" style="width: 80px;">No</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Butir Pertanyaan / Indikator</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $index => $q)
                    <tr>
                        <td class="ps-4 fw-bold text-secondary text-center">{{ $index + 1 }}</td>
                        <td class="text-dark py-3">{{ $q->question_text }}</td>
                        <td class="text-center pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-light btn-sm rounded-3 border px-2 text-primary btn-edit" 
                                        data-id="{{ $q->id }}" 
                                        data-text="{{ $q->question_text }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('web.pertanyaan.destroy', $q->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm rounded-3 border px-2 text-danger" onclick="return confirm('Hapus pertanyaan ini?')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 text-secondary">
                            <i class="bi bi-chat-left-dots fs-1 d-block mb-3 opacity-25"></i>
                            Belum ada butir pertanyaan untuk kategori ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-teal-dark">Tambah Pertanyaan</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('web.pertanyaan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="category_id" value="{{ $category->id }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">TEKS PERTANYAAN</label>
                        <textarea name="question_text" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" rows="4" placeholder="Cth: Apakah siswa selalu datang tepat waktu?" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4">Simpan Pertanyaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-teal-dark">Update Pertanyaan</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="category_id" value="{{ $category->id }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">TEKS PERTANYAAN</label>
                        <textarea name="question_text" id="edit_question_text" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" rows="4" required></textarea>
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
</style>
@endsection

@section('extra-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.btn-edit');
        const editModal = new bootstrap.Modal(document.getElementById('modalEdit'));
        
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const text = this.getAttribute('data-text');
                
                // Pastikan action URL mengarah ke route update pertanyaan
                document.getElementById('formEdit').action = `/pertanyaan/${id}`;
                document.getElementById('edit_question_text').value = text;
                
                editModal.show();
            });
        });
    });
</script>
@endsection