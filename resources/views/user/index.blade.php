@extends('layouts.app')

@section('title', 'Manajemen Akun')
@section('page-title', 'Data Pengguna Sistem')

@section('content')
<div class="container-fluid px-0">
    <!-- NOTIFIKASI BERHASIL -->
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
                     style="width: 55px; height: 55px; background-color: #e7f3ff;">
                    <i class="bi bi-people-fill fs-4" style="color: #007bff;"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Total {{ count($data) }} Akun</h5>
                    <p class="text-secondary small mb-0">Kelola hak akses admin, guru dan siswa</p>
                </div>
            </div>
            <button class="btn btn-teal-dark px-4 py-2 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-2"></i> Tambah User
            </button>
        </div>
    </div>

    <!-- TABEL USER -->
    <div class="card border-0 shadow-sm rounded-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="userTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">No</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Nama Pengguna</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center">Serial / NIP / NIS</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center">Role</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Definisi pasangan warna: [Background Muda, Outline/Teks Solid]
                        $avatarStyles = [
                            ['bg' => 'rgba(19, 75, 70, 0.1)',  'border' => '#134B46'], // Teal
                            ['bg' => 'rgba(0, 123, 255, 0.1)', 'border' => '#007bff'], // Biru
                            ['bg' => 'rgba(240, 140, 0, 0.1)', 'border' => '#f08c00'], // Kuning
                        ];
                    @endphp
                    
                    @forelse($data as $index => $user)
                    <tr class="user-row">
                        <td class="ps-4 fw-bold text-secondary text-center">{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <!-- LOGIKA AVATAR -->
                                @php
                                    // Pilih style berdasarkan ID agar warna konsisten untuk user yang sama
                                    $style = $avatarStyles[$user->id % count($avatarStyles)];
                                @endphp
                                
                                <div class="avatar-box me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                                    style="background-color: {{ $style['bg'] }}; 
                                        border: 2px solid {{ $style['border'] }}; 
                                        color: {{ $style['border'] }}; 
                                        width: 42px; height: 42px; border-radius: 14px; font-size: 16px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>

                                <div class="fw-bold mb-0 text-dark search-name" style="font-size: 15px;">
                                    {{ $user->name }}
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border rounded-pill px-3 search-serial" style="font-size: 12px; font-weight: 600;">
                                {{ $user->serial_number }}
                            </span>
                        </td>
                        
                        <td class="text-center">
                            @php
                                $roleClass = [
                                    'admin' => 'bg-danger-subtle text-danger',
                                    'guru'  => 'bg-teal-subtle text-teal',
                                    'siswa' => 'bg-primary-subtle text-primary',
                                    'kepsek' => 'bg-warning-subtle text-warning'
                                ][$user->role] ?? 'bg-secondary-subtle text-secondary';
                            @endphp
                            <span class="badge {{ $roleClass }} rounded-pill px-3">
                                {{ strtoupper($user->role) }}
                            </span>
                        </td>

                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-light btn-sm rounded-3 border px-2 text-primary hover-shadow edit-btn" 
                                        data-id="{{ $user->id }}" 
                                        data-nama="{{ $user->name }}" 
                                        data-serial="{{ $user->serial_number }}" 
                                        data-role="{{ $user->role }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <form action="{{ route('pengguna.destroy', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm rounded-3 border px-2 text-danger hover-shadow" onclick="return confirm('Hapus akun ini?')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-secondary italic">Tidak ada data pengguna.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH (ID DISESUAIKAN) -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-teal-dark">Buat Akun Baru</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pengguna.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NAMA LENGKAP</label>
                        <input type="text" name="name" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" placeholder="Masukkan Nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">SERIAL NUMBER / NIP / NIS</label>
                        <input type="text" name="serial_number" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" placeholder="Masukkan ID Unik" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">PASSWORD</label>
                            <input type="password" name="password" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" placeholder="Min 6 Karakter" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">ROLE</label>
                            <select name="role" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                                <option value="siswa">SISWA</option>
                                <option value="guru">GURU</option>
                                <option value="kepsek">KEPSEK</option>
                                <option value="admin">ADMIN</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4">Simpan User</button>
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
                <h5 class="fw-bold text-teal-dark">Update Akun</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NAMA LENGKAP</label>
                        <input type="text" name="name" id="edit_name" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">SERIAL NUMBER</label>
                        <input type="text" name="serial_number" id="edit_serial" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">ROLE</label>
                            <select name="role" id="edit_role" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                                <option value="siswa">SISWA</option>
                                <option value="guru">GURU</option>
                                <option value="kepsek">KEPSEK</option>
                                <option value="admin">ADMIN</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">GANTI PASSWORD</label>
                            <input type="password" name="password" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" placeholder="Kosongkan jika tidak ganti">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4">Update Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .rounded-card { border-radius: 24px !important; }
    .btn-teal-dark { background-color: #134B46; color: white; border: none; transition: 0.3s; }
    .btn-teal-dark:hover { background-color: #0f3632; color: white; transform: translateY(-2px); }
    .bg-danger-subtle { background-color: #ffeef0 !important; color: #dc3545 !important; }
    .bg-teal-subtle { background-color: #e6fcf5 !important; color: #0ca678 !important; }
    .bg-primary-subtle { background-color: #e7f3ff !important; color: #007bff !important; }
    .bg-warning-subtle { background-color: #fff9db !important; color: #f08c00 !important; }
</style>
@endsection

@section('extra-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. FITUR SEARCH
        const searchInput = document.getElementById('mainSearch');
        const rows = document.querySelectorAll('.user-row');

        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                const query = searchInput.value.toLowerCase();
                rows.forEach(row => {
                    const name = row.querySelector('.search-name').textContent.toLowerCase();
                    const serial = row.querySelector('.search-serial').textContent.toLowerCase();
                    row.style.display = (name.includes(query) || serial.includes(query)) ? '' : 'none';
                });
            });
        }

        // 2. FITUR EDIT MODAL
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModal = new bootstrap.Modal(document.getElementById('modalEdit'));
        
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('formEdit').action = `/user/${id}`;
                document.getElementById('edit_name').value = this.getAttribute('data-nama');
                document.getElementById('edit_serial').value = this.getAttribute('data-serial');
                document.getElementById('edit_role').value = this.getAttribute('data-role');
                editModal.show();
            });
        });
    });
</script>
@endsection