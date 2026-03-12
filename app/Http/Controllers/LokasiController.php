<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function index(Request $request)
    {
        $data = Lokasi::orderBy('nama_lokasi', 'asc')->get();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $data]);
        }

        return view('lokasi.index', compact('data'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'radius'      => 'required|integer',
        ]);

        $item = Lokasi::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'data' => $item], 201);
        }

        return redirect()->route('locations.index')->with('success', 'Titik lokasi berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $lokasi = Lokasi::findOrFail($id);
        
        $validated = $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'radius'      => 'required|integer',
        ]);

        $lokasi->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Lokasi diperbarui']);
        }

        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil diperbarui');
    }

    public function destroy(Request $request, $id)
    {
        $lokasi = Lokasi::findOrFail($id);
        $lokasi->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Lokasi dihapus']);
        }

        return redirect()->route('locations.index')->with('success', 'Data lokasi berhasil dihapus');
    }
}