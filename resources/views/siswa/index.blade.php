@extends('layouts.app')

@section('title', 'Manajemen Siswa')
@section('page-title', 'Data Murid Attendia')

@section('content')
<div class="container-fluid px-0">
    <!-- ALERT NOTIFIKASI -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- CARD RINGKASAN & TOMBOL TAMBAH -->
    <div class="card border-0 shadow-sm rounded-card mb-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center text-center text-md-start">
                <div class="icon-box rounded-4 me-md-3 mb-3 mb-md-0 d-flex align-items-center justify-content-center" 
                     style="width: 55px; height: 55px; background-color: #eef4f3;">
                    <i class="bi bi-people-fill fs-4" style="color: #134B46;"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Total {{ count($data) }} Siswa</h5>
                    <p class="text-secondary small mb-0">Manajemen data diri dan NISN murid</p>
                </div>
            </div>
            <button class="btn btn-teal-dark px-4 py-2 rounded-pill fw-bold shadow-sm w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-2"></i> Tambah Siswa
            </button>
        </div>
    </div>

    <!-- TABEL DATA SISWA -->
    <div class="card border-0 shadow-sm rounded-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="studentTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase text-center">No</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Informasi Siswa</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center">NISN</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Gaya Avatar: [Background Muda, Outline/Teks Solid]
                        $avatarStyles = [
                            ['bg' => 'rgba(19, 75, 70, 0.1)',  'border' => '#134B46'], // Teal
                            ['bg' => 'rgba(0, 123, 255, 0.1)', 'border' => '#007bff'], // Biru
                            ['bg' => 'rgba(240, 140, 0, 0.1)', 'border' => '#f08c00'], // Kuning
                        ];
                    @endphp
                    
                    @forelse($data as $index => $siswa)
                    <tr class="student-row">
                        <td class="text-center fw-bold text-secondary">{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <!-- AVATAR BOX -->
                                @php $style = $avatarStyles[$siswa->id % count($avatarStyles)]; @endphp
                                <div class="avatar-box me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                                    style="background-color: {{ $style['bg'] }}; border: 2px solid {{ $style['border'] }}; color: {{ $style['border'] }}; width: 42px; height: 42px; border-radius: 14px; font-size: 16px;">
                                    {{ strtoupper(substr($siswa->nama_siswa, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark search-name" style="font-size: 15px;">{{ $siswa->nama_siswa }}</div>
                                    <div class="text-secondary small" style="font-size: 11px;">
                                        UID: {{ $siswa->user->serial_number ?? 'Akun Error' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border rounded-pill px-3 search-nisn" style="font-size: 12px; font-weight: 600;">
                                {{ $siswa->nisn }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-light btn-sm rounded-3 border px-2 text-primary hover-shadow edit-btn" 
                                        data-id="{{ $siswa->id }}" 
                                        data-nama="{{ $siswa->nama_siswa }}" 
                                        data-nisn="{{ $siswa->nisn }}" 
                                        data-user="{{ $siswa->user_id }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <form action="{{ route('murid.destroy', $siswa->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm rounded-3 border px-2 text-danger hover-shadow" onclick="return confirm('Hapus data murid ini?')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-secondary italic">Data murid tidak ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH (DENGAN AUTOFILL) -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-teal-dark">Tambah Murid Baru</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('murid.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">1. PILIH AKUN SISWA</label>
                        <select name="user_id" id="user_id_select" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                            <option value="" selected disabled>-- Pilih Akun Terdaftar --</option>
                            @foreach($usersAvailable as $user)
                                <option value="{{ $user->id }}" data-name="{{ $user->name }}" data-serial="{{ $user->serial_number }}">
                                    {{ $user->serial_number }} - {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">2. NAMA LENGKAP (OTOMATIS)</label>
                        <input type="text" name="nama_siswa" id="nama_siswa_auto" class="form-control rounded-3 border-0 bg-secondary bg-opacity-10 p-3 shadow-none" placeholder="Akan terisi otomatis" readonly required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">3. NISN (OTOMATIS)</label>
                        <input type="text" name="nisn" id="nisn_auto" class="form-control rounded-3 border-0 bg-secondary bg-opacity-10 p-3 shadow-none" placeholder="Akan terisi otomatis" readonly required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4 fw-bold">Simpan Data</button>
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
                <h5 class="fw-bold text-teal-dark">Update Informasi Murid</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NAMA LENGKAP</label>
                        <input type="text" name="nama_siswa" id="edit_nama" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">NISN</label>
                            <input type="text" name="nisn" id="edit_nisn" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">USER ID (LINK)</label>
                            <input type="text" id="edit_user_id_display" class="form-control rounded-3 border-0 bg-secondary bg-opacity-10 p-3 shadow-none" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4 fw-bold">Update Murid</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .rounded-card { border-radius: 24px !important; }
    .btn-teal-dark { background-color: #134B46; color: white; border: none; transition: 0.3s; }
    .btn-teal-dark:hover { background-color: #0f3632; color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(19, 75, 70, 0.2); }
    .table thead th { border-bottom: none; }
    .table tbody td { border-top: 1px solid #f8fafb; padding: 1.1rem 0.5rem; }
    .hover-shadow:hover { box-shadow: 0 4px 8px rgba(0,0,0,0.1); background-color: #fff !important; border-color: #ddd !important; }
</style>
@endsection

@section('extra-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. LOGIKA AUTOFILL TAMBAH SISWA ---
        const userSelect = document.getElementById('user_id_select');
        const inputNama = document.getElementById('nama_siswa_auto');
        const inputNisn = document.getElementById('nisn_auto');

        if(userSelect) {
            userSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const name = selectedOption.getAttribute('data-name');
                const serial = selectedOption.getAttribute('data-serial');

                inputNama.value = name || "";
                inputNisn.value = serial || "";
            });
        }

        // --- 2. FITUR SEARCH REAL-TIME ---
        const searchInput = document.getElementById('mainSearch');
        const rows = document.querySelectorAll('.student-row');

        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                const query = searchInput.value.toLowerCase();
                rows.forEach(row => {
                    const name = row.querySelector('.search-name').textContent.toLowerCase();
                    const nisn = row.querySelector('.search-nisn').textContent.toLowerCase();
                    row.style.display = (name.includes(query) || nisn.includes(query)) ? '' : 'none';
                });
            });
        }

        // --- 3. FITUR MODAL EDIT ---
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModal = new bootstrap.Modal(document.getElementById('modalEdit'));
        
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const user = this.getAttribute('data-user');
                document.getElementById('formEdit').action = `/siswa/${id}`;
                document.getElementById('edit_nama').value = this.getAttribute('data-nama');
                document.getElementById('edit_nisn').value = this.getAttribute('data-nisn');
                document.getElementById('edit_user_id_display').value = "Akun ID: " + user;
                editModal.show();
            });
        });
    });
</script>
@endsection