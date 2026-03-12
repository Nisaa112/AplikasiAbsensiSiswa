@extends('layouts.app')

@section('title', 'Tahun Ajaran')
@section('page-title', 'Manajemen Tahun Akademik')

@section('content')
<div class="container-fluid px-0">
    <!-- NOTIFIKASI -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    <!-- CARD STATISTIK -->
    <div class="card border-0 shadow-sm rounded-card mb-4">
        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center">
                <div class="icon-box rounded-4 me-3 d-flex align-items-center justify-content-center" 
                     style="width: 55px; height: 55px; background-color: #eef4f3;">
                    <i class="bi bi-calendar3 fs-4" style="color: #134B46;"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Tahun Ajaran Aktif</h5>
                    <p class="text-secondary small mb-0">
                        @php $active = $data->where('status', 1)->first(); @endphp
                        {{ $active ? $active->tahun . ' - ' . $active->semester : 'Belum ada yang aktif' }}
                    </p>
                </div>
            </div>
            <button class="btn btn-teal-dark px-4 py-2 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-2"></i> Tambah Tahun
            </button>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="card border-0 shadow-sm rounded-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">No</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-start">Tahun / Periode</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Semester</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Mulai Tanggal</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Status</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $index => $item)
                    <tr class="data-row">
                        <td class="fw-bold text-secondary">{{ $index + 1 }}</td>
                        <td class="text-start">
                            <div class="d-flex align-items-center">
                                <div class="avatar-box me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                                    style="background-color: rgba(19, 75, 70, 0.1); border: 2px solid #134B46; color: #134B46; width: 42px; height: 42px; border-radius: 14px;">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <div class="fw-bold text-dark">{{ $item->tahun }}</div>
                            </div>
                        </td>
                        <td>{{ $item->semester }}</td>
                        <td><span class="text-secondary small">{{ \Carbon\Carbon::parse($item->tgl_mulai)->format('d M Y') }}</span></td>
                        <td>
                            @if($item->status)
                                <span class="badge bg-teal-subtle text-teal rounded-pill px-3">AKTIF</span>
                            @else
                                <span class="badge bg-light text-muted border rounded-pill px-3">NON-AKTIF</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-light btn-sm rounded-3 border px-2 text-primary hover-shadow edit-btn" 
                                        data-id="{{ $item->id }}" data-tahun="{{ $item->tahun }}" 
                                        data-semester="{{ $item->semester }}" data-tgl="{{ $item->tgl_mulai }}" 
                                        data-status="{{ $item->status }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('academic-year.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm rounded-3 border px-2 text-danger hover-shadow" onclick="return confirm('Hapus data ini?')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-secondary italic">Tidak ada data.</td></tr>
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
                <h5 class="fw-bold text-teal-dark">Tambah Tahun Ajaran</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('academic-year.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">TAHUN (Contoh: 2023/2024)</label>
                        <input type="text" name="tahun" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">SEMESTER</label>
                            <select name="semester" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">TGL MULAI</label>
                            <input type="date" name="tgl_mulai" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">STATUS AKTIF</label>
                        <select name="status" class="form-select rounded-3 border-0 bg-light p-3 shadow-none" required>
                            <option value="1">Aktif (Gunakan Sekarang)</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4">Simpan</button>
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
                <h5 class="fw-bold text-teal-dark">Update Tahun Ajaran</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">TAHUN</label>
                        <input type="text" name="tahun" id="edit_tahun" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">SEMESTER</label>
                            <select name="semester" id="edit_semester" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">TGL MULAI</label>
                            <input type="date" name="tgl_mulai" id="edit_tgl" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">STATUS AKTIF</label>
                        <select name="status" id="edit_status" class="form-select rounded-3 border-0 bg-light p-3 shadow-none">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .rounded-card { border-radius: 24px !important; }
    .btn-teal-dark { background-color: #134B46; color: white; border: none; transition: 0.3s; }
    .btn-teal-dark:hover { background-color: #0d312d; color: white; transform: translateY(-2px); }
    .bg-teal-subtle { background-color: #e6fcf5 !important; color: #0ca678 !important; }
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
                document.getElementById('formEdit').action = `/academic-year/${id}`;
                document.getElementById('edit_tahun').value = this.getAttribute('data-tahun');
                document.getElementById('edit_semester').value = this.getAttribute('data-semester');
                document.getElementById('edit_tgl').value = this.getAttribute('data-tgl');
                document.getElementById('edit_status').value = this.getAttribute('data-status');
                editModal.show();
            });
        });
    });
</script>
@endsection