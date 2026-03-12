<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapelController extends Controller
{
    private function findMapelById($id)
    {
        return Mapel::findOrFail($id);
    }

    public function index(Request $request)
    {
        $data = Mapel::orderBy('nama_mapel', 'asc')->get();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $data // Dibungkus key data agar sinkron dengan Flutter
            ]);
        }

        return view('mapel/index', ['data' => $data]);
    }

    public function create()
    {
        return view('mapel/form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mapel' => [
                'required',
                'max:100',
                'unique:mapel,nama_mapel'
            ],
        ]);

        $status = Mapel::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => (bool) $status,
                'message' => $status ? 'Berhasil ditambahkan' : 'Gagal ditambahkan',
            ], $status ? 200 : 500);
        }

        if ($status) {
            return redirect('subjects.index')->with('success', 'Mapel berhasil ditambahkan');
        }

        return redirect('subjects.index')->with('error', 'Mapel gagal ditambahkan');
    }

    public function edit($id)
    {
        $data = $this->findMapelById($id);

        return view('mapel/form', [
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $mapel = $this->findMapelById($id);

        $validated = $request->validate([
            'nama_mapel' => [
                'required',
                'max:100',
                Rule::unique('mapel', 'nama_mapel')->ignore($mapel->id),
            ],
        ]);

        $status = $mapel->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => (bool) $status,
                'message' => $status ? 'Berhasil diupdate' : 'Gagal diupdate',
            ], $status ? 200 : 500);
        }

        if ($status) {
            return redirect('subjects.index')->with('success', 'Mapel berhasil diupdate');
        }

        return redirect('subjects.index')->with('error', 'Mapel gagal diupdate');
    }

    public function destroy(Request $request, $id)
    {
        $mapel = $this->findMapelById($id);
        $status = $mapel->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => (bool) $status,
                'message' => $status ? 'Berhasil dihapus' : 'Gagal dihapus',
            ], $status ? 200 : 500);
        }

        if ($status) {
            return redirect('subjects.index')->with('success', 'Mapel berhasil dihapus');
        }

        return redirect('subjects.index')->with('error', 'Mapel gagal dihapus');
    }
}
