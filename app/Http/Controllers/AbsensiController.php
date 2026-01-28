<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AbsensiController extends Controller
{
    private function findAbsensiById($id)
    {
        return Absensi::findOrFail($id);
    }

    public function index(Request $request)
    {
        $data = Absensi::with(['sesi', 'siswa'])->get();

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        return view('absensi/index', [
            'data' => $data
        ]);
    }

    public function create()
    {
        return view('absensi/form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sesi_id'   => 'required|exists:sesi_presensi,id',
            'siswa_id'  => 'required|exists:siswa,id',
            'waktu_scan'=> 'required|date',
            'status'    => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alfa'])],
            'lat_siswa' => 'nullable|numeric',
            'long_siswa'=> 'nullable|numeric',
            'is_valid'  => 'required|boolean',
        ]);

        $status = Absensi::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => (bool) $status,
                'message' => $status ? 'Absensi berhasil ditambahkan' : 'Absensi gagal ditambahkan',
            ], $status ? 200 : 500);
        }

        if ($status) {
            return redirect('/absensi')->with('success', 'Absensi berhasil ditambahkan');
        }

        return redirect('/absensi')->with('error', 'Absensi gagal ditambahkan');
    }

    public function edit($id)
    {
        $data = $this->findAbsensiById($id);

        return view('absensi/form', [
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $absensi = $this->findAbsensiById($id);

        $validated = $request->validate([
            'sesi_id'   => 'required|exists:sesi_presensi,id',
            'siswa_id'  => 'required|exists:siswa,id',
            'waktu_scan'=> 'required|date',
            'status'    => ['required', Rule::in(['hadir', 'izin', 'sakit', 'alfa'])],
            'lat_siswa' => 'nullable|numeric',
            'long_siswa'=> 'nullable|numeric',
            'is_valid'  => 'required|boolean',
        ]);

        $status = $absensi->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => (bool) $status,
                'message' => $status ? 'Absensi berhasil diupdate' : 'Absensi gagal diupdate',
            ], $status ? 200 : 500);
        }

        if ($status) {
            return redirect('/absensi')->with('success', 'Absensi berhasil diupdate');
        }

        return redirect('/absensi')->with('error', 'Absensi gagal diupdate');
    }

    public function destroy(Request $request, $id)
    {
        $absensi = $this->findAbsensiById($id);
        $status  = $absensi->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => (bool) $status,
                'message' => $status ? 'Absensi berhasil dihapus' : 'Absensi gagal dihapus',
            ], $status ? 200 : 500);
        }

        if ($status) {
            return redirect('/absensi')->with('success', 'Absensi berhasil dihapus');
        }

        return redirect('/absensi')->with('error', 'Absensi gagal dihapus');
    }
}
