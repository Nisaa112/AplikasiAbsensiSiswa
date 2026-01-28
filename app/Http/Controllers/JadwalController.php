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
    private function findJadwalById($id)
    {
        return Jadwal::findOrFail($id);
    }

    public function index(Request $request)
    {
        $data = Jadwal::with(['kelas', 'mapel', 'guru', 'lokasi'])->get();

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        return view('jadwal/index', [
            'data' => $data
        ]);
    }

    public function create()
    {
        return view('jadwal/form', [
            'kelas' => Kelas::all(),
            'mapel' => Mapel::all(),
            'guru' => Guru::all(),
            'lokasi' => Lokasi::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
            'guru_id' => 'required|exists:guru,id',
            'lokasi_id' => 'required|exists:lokasi,id',
            'hari' => 'required|string|max:20',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        $status = Jadwal::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => (bool) $status,
                'message' => $status ? 'Berhasil ditambahkan' : 'Gagal ditambahkan',
            ], $status ? 200 : 500);
        }

        if ($status) {
            return redirect('/jadwal')->with('success', 'Jadwal berhasil ditambahkan');
        }

        return redirect('/jadwal')->with('error', 'Jadwal gagal ditambahkan');
    }

    public function edit($id)
    {
        $data = $this->findJadwalById($id);

        return view('jadwal/form', [
            'data' => $data,
            'kelas' => Kelas::all(),
            'mapel' => Mapel::all(),
            'guru' => Guru::all(),
            'lokasi' => Lokasi::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $jadwal = $this->findJadwalById($id);

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
            'guru_id' => 'required|exists:guru,id',
            'lokasi_id' => 'required|exists:lokasi,id',
            'hari' => 'required|string|max:20',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        $status = $jadwal->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => (bool) $status,
                'message' => $status ? 'Berhasil diupdate' : 'Gagal diupdate',
            ], $status ? 200 : 500);
        }

        if ($status) {
            return redirect('/jadwal')->with('success', 'Jadwal berhasil diupdate');
        }

        return redirect('/jadwal')->with('error', 'Jadwal gagal diupdate');
    }

    public function destroy(Request $request, $id)
    {
        $jadwal = $this->findJadwalById($id);
        $status = $jadwal->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => (bool) $status,
                'message' => $status ? 'Berhasil dihapus' : 'Gagal dihapus',
            ], $status ? 200 : 500);
        }

        if ($status) {
            return redirect('/jadwal')->with('success', 'Jadwal berhasil dihapus');
        }

        return redirect('/jadwal')->with('error', 'Jadwal gagal dihapus');
    }
}
