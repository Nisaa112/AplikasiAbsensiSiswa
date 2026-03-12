@extends('layouts.app')

@section('title', 'Titik Lokasi')
@section('page-title', 'Konfigurasi Geo-Fence')

@section('extra-css')
<!-- Leaflet Map Package CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .rounded-card { border-radius: 24px !important; }
    .btn-teal-dark { background-color: #134B46; color: white; border: none; transition: 0.3s; }
    .btn-teal-dark:hover { background-color: #0d312d; color: white; transform: translateY(-2px); }
    
    /* Ukuran Peta di dalam Modal */
    #mapAdd, #mapEdit { 
        height: 350px; 
        width: 100%; 
        border-radius: 20px; 
        border: 2px solid #f1f1f1;
        margin-bottom: 20px;
        z-index: 1;
    }
    
    .text-teal { color: #134B46 !important; }
</style>
@endsection

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
                    <i class="bi bi-geo-alt fs-4" style="color: #134B46;"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Lokasi Presensi</h5>
                    <p class="text-secondary small mb-0">Tentukan titik koordinat kantor/sekolah</p>
                </div>
            </div>
            <button class="btn btn-teal-dark px-4 py-2 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-2"></i> Tambah Lokasi
            </button>
        </div>
    </div>

    <!-- TABEL DATA LOKASI -->
    <div class="card border-0 shadow-sm rounded-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">No</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase text-start">Nama Titik Lokasi</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Koordinat</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Radius</th>
                        <th class="py-3 text-secondary small fw-bold text-uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $index => $lokasi)
                    <tr>
                        <td class="fw-bold text-secondary">{{ $index + 1 }}</td>
                        <td class="text-start">
                            <div class="d-flex align-items-center">
                                <div class="avatar-box me-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" 
                                    style="background-color: rgba(19, 75, 70, 0.1); border: 2px solid #134B46; color: #134B46; width: 42px; height: 42px; border-radius: 12px;">
                                    <i class="bi bi-pin-map"></i>
                                </div>
                                <div class="fw-bold text-dark">{{ $lokasi->nama_lokasi }}</div>
                            </div>
                        </td>
                        <td><small class="text-secondary">{{ $lokasi->latitude }}, {{ $lokasi->longitude }}</small></td>
                        <td><span class="badge bg-light text-dark border rounded-pill px-3">{{ $lokasi->radius }} m</span></td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-light btn-sm rounded-3 border px-2 text-primary edit-btn" 
                                        data-id="{{ $lokasi->id }}" data-nama="{{ $lokasi->nama_lokasi }}" 
                                        data-lat="{{ $lokasi->latitude }}" data-long="{{ $lokasi->longitude }}" 
                                        data-radius="{{ $lokasi->radius }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form action="{{ route('locations.destroy', $lokasi->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm rounded-3 border px-2 text-danger" onclick="return confirm('Hapus lokasi?')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-secondary italic">Data belum tersedia.</td></tr>
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
                <h5 class="fw-bold text-teal-dark">Pilih Lokasi di Peta</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('locations.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <!-- PETA UNTUK INPUT -->
                    <div id="mapAdd"></div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NAMA LOKASI</label>
                        <input type="text" name="nama_lokasi" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" placeholder="Misal: Gedung A" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-secondary">LATITUDE</label>
                            <input type="text" name="latitude" id="latAdd" class="form-control rounded-3 border-0 bg-secondary bg-opacity-10 p-3 shadow-none" readonly required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-secondary">LONGITUDE</label>
                            <input type="text" name="longitude" id="lngAdd" class="form-control rounded-3 border-0 bg-secondary bg-opacity-10 p-3 shadow-none" readonly required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-secondary">RADIUS (M)</label>
                            <input type="number" name="radius" id="radiusAdd" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" value="50" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4">Simpan Lokasi</button>
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
                <h5 class="fw-bold text-teal-dark">Edit Titik Koordinat</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div id="mapEdit"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">NAMA LOKASI</label>
                        <input type="text" name="nama_lokasi" id="edit_nama" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-secondary">LATITUDE</label>
                            <input type="text" name="latitude" id="edit_lat" class="form-control rounded-3 border-0 bg-secondary bg-opacity-10 p-3 shadow-none" readonly required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-secondary">LONGITUDE</label>
                            <input type="text" name="longitude" id="edit_long" class="form-control rounded-3 border-0 bg-secondary bg-opacity-10 p-3 shadow-none" readonly required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-secondary">RADIUS (M)</label>
                            <input type="number" name="radius" id="edit_radius" class="form-control rounded-3 border-0 bg-light p-3 shadow-none" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-teal-dark rounded-pill px-4">Update Lokasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<!-- Leaflet Map Package JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const defaultCenter = [-6.200000, 106.816666]; // Default Jakarta
        const primaryTeal = '#134B46';

        // --- MAP TAMBAH ---
        let mapAdd, markerAdd, circleAdd;
        $('#modalTambah').on('shown.bs.modal', function () {
            if (!mapAdd) {
                mapAdd = L.map('mapAdd').setView(defaultCenter, 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapAdd);
                
                markerAdd = L.marker(defaultCenter, {draggable: true}).addTo(mapAdd);
                circleAdd = L.circle(defaultCenter, {radius: 50, color: primaryTeal, fillColor: primaryTeal}).addTo(mapAdd);

                const updateFields = (lat, lng) => {
                    document.getElementById('latAdd').value = lat.toFixed(6);
                    document.getElementById('lngAdd').value = lng.toFixed(6);
                };

                mapAdd.on('click', (e) => {
                    markerAdd.setLatLng(e.latlng);
                    circleAdd.setLatLng(e.latlng);
                    updateFields(e.latlng.lat, e.latlng.lng);
                });

                markerAdd.on('dragend', () => {
                    let pos = markerAdd.getLatLng();
                    circleAdd.setLatLng(pos);
                    updateFields(pos.lat, pos.lng);
                });

                document.getElementById('radiusAdd').addEventListener('input', function() {
                    circleAdd.setRadius(this.value || 0);
                });
            }
            mapAdd.invalidateSize();
        });

        // --- MAP EDIT ---
        let mapEdit, markerEdit, circleEdit;
        const editButtons = document.querySelectorAll('.edit-btn');
        const modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));

        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const d = this.dataset;
                const pos = [parseFloat(d.lat), parseFloat(d.long)];

                document.getElementById('formEdit').action = `/locations/${d.id}`;
                document.getElementById('edit_nama').value = d.nama;
                document.getElementById('edit_lat').value = d.lat;
                document.getElementById('edit_long').value = d.long;
                document.getElementById('edit_radius').value = d.radius;

                modalEdit.show();

                setTimeout(() => {
                    if (!mapEdit) {
                        mapEdit = L.map('mapEdit').setView(pos, 16);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mapEdit);
                        markerEdit = L.marker(pos, {draggable: true}).addTo(mapEdit);
                        circleEdit = L.circle(pos, {radius: d.radius, color: primaryTeal, fillColor: primaryTeal}).addTo(mapEdit);

                        markerEdit.on('dragend', () => {
                            let p = markerEdit.getLatLng();
                            circleEdit.setLatLng(p);
                            document.getElementById('edit_lat').value = p.lat.toFixed(6);
                            document.getElementById('edit_long').value = p.lng.toFixed(6);
                        });
                    } else {
                        mapEdit.setView(pos, 16);
                        markerEdit.setLatLng(pos);
                        circleEdit.setLatLng(pos).setRadius(d.radius);
                    }
                    mapEdit.invalidateSize();
                }, 400);
            });
        });
    });
</script>
@endsection