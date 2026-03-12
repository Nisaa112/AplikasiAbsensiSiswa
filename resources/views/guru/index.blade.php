@extends('layouts.app')

@section('title', 'Manajemen Guru')
@section('page-title', 'Data Tenaga Pendidik')

@section('content')
<div class="container-fluid px-0">
    <!-- ALERT NOTIFIKASI -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Tampilkan Error Validasi jika ada -->
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- CARD RINGKASAN STATISTIK -->
    <div class="card border-0 shadow-sm rounded-card mb-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center">
                <div class="icon-box rounded-4 me-3 d-flex align-items-center justify-content-center" 
                     style="width: 55px; height: 55px; background-color: #fff4e6;">
                    <i class="bi bi-person-badge-fill fs-4" style="color: #F57C00;"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Total {{ count($data) }} Guru</h5>
                    <p class="text-secondary small mb-0">Kelola informasi data profesional pendidik Attendia</p>
                </div>
            </div>
            <button class="btn btn-teal-dark px-4 py-2 rounded-pill fw-bold shadow-sm w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-2"></i> Tambah Guru
            </button>
        </div>
    </div>

    <!-- TABEL DATA GURU -->
    <div class="card border-0 shadow-sm rounded-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" id="teacherTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">No</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-start">Nama Lengkap</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">NIP</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Status</th>
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
                    
                    @forelse($data as $index => $guru)
                    <tr class="teacher-row">
                        <td class="fw-bold text-secondary">{{ $index + 1 }}</td>
                        <td class="text-start">
                            <div class="d-flex align-items-center">
                                @php $style = $avatarStyles[$guru->id % count($avatarStyles)]; @endphp
                                <div class="avatar-box me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                                    style="background-color: {{ $style['bg'] }}; border: 2px solid {{ $style['border'] }}; color: {{ $style['border'] }}; width: 42px; height: 42px; border-radius: 14px; font-size: 16px;">
                                    {{ strtoupper(substr($guru->nama_guru, 0, 1)) }}
                                </div>
                                <div class="fw-bold text-dark search-name" style="font-size: 15px;">{{ $guru->nama_guru }}</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-3 search-nip" style="font-size: 12px; font-weight: 600;">
                                {{ $guru->nip }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $guru->senioritas == 'Senior' ? 'bg-danger-subtle text-danger' : 'bg-teal-subtle text-teal' }} rounded-pill px-3">
                                {{ strtoupper($guru->senioritas) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-light btn-sm rounded-3 border px-2 text-primary hover-shadow edit-btn" 
                                        data-id="{{ $guru->id }}" 
                                        data-nama="{{ $guru->nama_guru }}" 
                                        data-nip="{{ $guru->nip }}" 
                                        data-user="{{ $guru->user_id }}"
                                        data-senioritas="{{ $guru->senioritas }}"
                                        data-gender="{{ $guru->gender }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('teacher.destroy', $guru->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm rounded-3 border px-2 text-danger hover-shadow" onclick="return confirm('Hapus data guru?')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-secondary">Data guru tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH (AUTOFILL) -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold text-teal-dark">Tambah Guru Baru</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('teacher.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">PILIH AKUN LOGIN TERSEDIA</label>
                        <select name="user_id" id="user_id_select" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                            <option value="" selected disabled>-- Pilih Akun --</option>
                            @foreach($usersAvailable as $user)
                                <option value="{{ $user->id }}" data-name="{{ $user->name }}" data-serial="{{ $user->serial_number }}">
                                    {{ $user->serial_number }} - {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NAMA (OTOMATIS)</label>
                        <input type="text" name="nama_guru" id="nama_auto" class="form-control rounded-3 border-0 bg-secondary bg-opacity-10 p-3 shadow-none" readonly required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NIP (OTOMATIS)</label>
                        <input type="text" name="nip" id="nip_auto" class="form-control rounded-3 border-0 bg-secondary bg-opacity-10 p-3 shadow-none" readonly required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">SENIORITAS</label>
                            <select name="senioritas" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                                <option value="Junior">Junior</option>
                                <option value="Senior">Senior</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">GENDER</label>
                            <select name="gender" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4 fw-bold">Simpan Guru</button>
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
                <h5 class="fw-bold text-teal-dark">Update Data Guru</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NAMA LENGKAP</label>
                        <input type="text" name="nama_guru" id="edit_nama" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">NIP</label>
                            <input type="text" name="nip" id="edit_nip" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">ID USER (LINK)</label>
                            <input type="text" id="edit_user_id_display" class="form-control rounded-3 border-0 bg-secondary bg-opacity-10 p-3 shadow-none" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">SENIORITAS</label>
                            <select name="senioritas" id="edit_senioritas" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                                <option value="Junior">Junior</option>
                                <option value="Senior">Senior</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">GENDER</label>
                            <select name="gender" id="edit_gender" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
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
    .bg-danger-subtle { background-color: #ffeef0 !important; color: #dc3545 !important; }
    .bg-teal-subtle { background-color: #e6fcf5 !important; color: #0ca678 !important; }
</style>
@endsection

@section('extra-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. AUTOFILL GURU ---
        const userSelect = document.getElementById('user_id_select');
        const inputNama = document.getElementById('nama_auto');
        const inputNip = document.getElementById('nip_auto');

        if(userSelect) {
            userSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                inputNama.value = selectedOption.getAttribute('data-name') || "";
                inputNip.value = selectedOption.getAttribute('data-serial') || "";
            });
        }

        // --- 2. SEARCH ---
        const searchInput = document.getElementById('mainSearch');
        const rows = document.querySelectorAll('.teacher-row');
        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                const query = searchInput.value.toLowerCase();
                rows.forEach(row => {
                    const name = row.querySelector('.search-name').textContent.toLowerCase();
                    const nip = row.querySelector('.search-nip').textContent.toLowerCase();
                    row.style.display = (name.includes(query) || nip.includes(query)) ? '' : 'none';
                });
            });
        }

        // --- 3. EDIT MODAL ---
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModal = new bootstrap.Modal(document.getElementById('modalEdit'));
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('formEdit').action = `/guru/${id}`;
                document.getElementById('edit_nama').value = this.getAttribute('data-nama');
                document.getElementById('edit_nip').value = this.getAttribute('data-nip');
                document.getElementById('edit_user_id_display').value = "ID: " + this.getAttribute('data-user');
                document.getElementById('edit_senioritas').value = this.getAttribute('data-senioritas');
                document.getElementById('edit_gender').value = this.getAttribute('data-gender');
                editModal.show();
            });
        });
    });
</script>
@endsection