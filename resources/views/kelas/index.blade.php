@extends('layouts.app')

@section('title', 'Manajemen Kelas')
@section('page-title', 'Data Kelas Akademik')

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
                     style="width: 55px; height: 55px; background-color: #eef4f3;">
                    <i class="bi bi-building fs-4" style="color: #134B46;"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Total {{ count($data) }} Ruang Kelas</h5>
                    <p class="text-secondary small mb-0">Manajemen pembagian kelas dan wali murid</p>
                </div>
            </div>
            <button class="btn btn-teal-dark px-4 py-2 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-2"></i> Tambah Kelas
            </button>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="card border-0 shadow-sm rounded-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" id="classTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">No</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-start">Nama Kelas</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Wali Kelas</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Tahun Ajaran</th>
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
                    
                    @forelse($data as $index => $kelas)
                    <tr class="class-row">
                        <td class="fw-bold text-secondary">{{ $index + 1 }}</td>
                        <td class="text-start">
                            <div class="d-flex align-items-center">
                                @php $style = $avatarStyles[$kelas->id % count($avatarStyles)]; @endphp
                                <div class="avatar-box me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                                    style="background-color: {{ $style['bg'] }}; border: 2px solid {{ $style['border'] }}; color: {{ $style['border'] }}; width: 42px; height: 42px; border-radius: 14px; font-size: 14px;">
                                    {{ $kelas->nama_kelas[0] }}{{ $kelas->nama_kelas[1] }}
                                </div>
                                <div class="fw-bold text-dark search-name">{{ $kelas->nama_kelas }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="small fw-bold">{{ $kelas->waliKelas->nama_guru ?? 'N/A' }}</div>
                            <div class="text-secondary" style="font-size: 10px;">NIP: {{ $kelas->waliKelas->nip ?? '-' }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border rounded-pill px-3">{{ $kelas->tahunAjaran->tahun }} ({{ $kelas->tahunAjaran->semester }})</span></td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-light btn-sm rounded-3 border px-2 text-primary hover-shadow edit-btn" 
                                        data-id="{{ $kelas->id }}" 
                                        data-tingkat="{{ $kelas->tingkat }}"
                                        data-jurusan="{{ $kelas->jurusan }}"
                                        data-nomor="{{ $kelas->nomor_kelas }}"
                                        data-tahun="{{ $kelas->tahun_ajaran_id }}"
                                        data-wali="{{ $kelas->wali_kelas_id }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('classes.destroy', $kelas->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm rounded-3 border px-2 text-danger hover-shadow" onclick="return confirm('Hapus data kelas ini?')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-secondary italic">Belum ada data kelas.</td></tr>
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
                <h5 class="fw-bold text-teal-dark">Konfigurasi Kelas Baru</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('classes.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label class="form-label small fw-bold text-secondary">TINGKAT</label>
                            <select name="tingkat" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                                <option value="10">X</option>
                                <option value="11">XI</option>
                                <option value="12">XII</option>
                            </select>
                        </div>
                        <div class="col-8 mb-3">
                            <label class="form-label small fw-bold text-secondary">JURUSAN (Singkatan)</label>
                            <input type="text" name="jurusan" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" placeholder="Contoh: RPL" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NOMOR KELAS</label>
                        <input type="text" name="nomor_kelas" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" placeholder="Contoh: 1 atau A" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">WALI KELAS</label>
                        <select name="wali_kelas_id" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                            <option value="" disabled selected>-- Pilih Guru --</option>
                            @foreach($teachers as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama_guru }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">TAHUN AJARAN</label>
                        <select name="tahun_ajaran_id" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                            <option value="" disabled selected>-- Pilih Periode --</option>
                            @foreach($years as $year)
                                <option value="{{ $year->id }}">{{ $year->tahun }} ({{ $year->semester }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4 fw-bold">Simpan Kelas</button>
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
                <h5 class="fw-bold text-teal-dark">Edit Data Kelas</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label class="form-label small fw-bold text-secondary">TINGKAT</label>
                            <select name="tingkat" id="edit_tingkat" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                                <option value="10">X</option>
                                <option value="11">XI</option>
                                <option value="12">XII</option>
                            </select>
                        </div>
                        <div class="col-8 mb-3">
                            <label class="form-label small fw-bold text-secondary">JURUSAN</label>
                            <input type="text" name="jurusan" id="edit_jurusan" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NOMOR KELAS</label>
                        <input type="text" name="nomor_kelas" id="edit_nomor" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">WALI KELAS</label>
                        <select name="wali_kelas_id" id="edit_wali" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                            @foreach($teachers as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama_guru }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">TAHUN AJARAN</label>
                        <select name="tahun_ajaran_id" id="edit_tahun" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                            @foreach($years as $year)
                                <option value="{{ $year->id }}">{{ $year->tahun }} ({{ $year->semester }})</option>
                            @endforeach
                        </select>
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
        const rows = document.querySelectorAll('.class-row');
        if(searchInput) {
            searchInput.addEventListener('keyup', function() {
                const query = searchInput.value.toLowerCase();
                rows.forEach(row => {
                    const name = row.querySelector('.search-name').textContent.toLowerCase();
                    row.style.display = (name.includes(query)) ? '' : 'none';
                });
            });
        }

        // --- EDIT MODAL HANDLER ---
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModal = new bootstrap.Modal(document.getElementById('modalEdit'));
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                document.getElementById('formEdit').action = `/classes/${id}`;
                document.getElementById('edit_tingkat').value = this.getAttribute('data-tingkat');
                document.getElementById('edit_jurusan').value = this.getAttribute('data-jurusan');
                document.getElementById('edit_nomor').value = this.getAttribute('data-nomor');
                document.getElementById('edit_wali').value = this.getAttribute('data-wali');
                document.getElementById('edit_tahun').value = this.getAttribute('data-tahun');
                editModal.show();
            });
        });
    });
</script>
@endsection