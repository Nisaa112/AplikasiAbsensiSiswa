<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Guru;
use App\Models\Lokasi;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $data = Jadwal::with(['kelas', 'mapel', 'guru', 'lokasi'])->latest()->get();

        // Data pendukung untuk Dropdown di Modal
        $classes = Kelas::all();
        $subjects = Mapel::orderBy('nama_mapel', 'asc')->get();
        $teachers = Guru::orderBy('nama_guru', 'asc')->get();
        $locations = Lokasi::all();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $data]);
        }

        return view('jadwal.index', compact('data', 'classes', 'subjects', 'teachers', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
            'guru_id' => 'required|exists:guru,id',
            'lokasi_id' => 'required|exists:lokasi,id',
            'minggu' => 'required|in:1,2',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        $item = Jadwal::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'data' => $item], 201);
        }

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
            'guru_id' => 'required|exists:guru,id',
            'lokasi_id' => 'required|exists:lokasi,id',
            'minggu' => 'required|in:1,2',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        $jadwal->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Jadwal diperbarui']);
        }

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil diperbarui');
    }

    public function destroy(Request $request, $id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Jadwal dihapus']);
        }

        return redirect()->route('schedules.index')->with('success', 'Jadwal berhasil dihapus');
    }
}