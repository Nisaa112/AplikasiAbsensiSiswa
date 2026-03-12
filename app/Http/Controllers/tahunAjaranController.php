<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    public function index(Request $request)
    {
        $data = TahunAjaran::orderBy('tahun', 'desc')->get();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        }

        return view('academic-year.index', ['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|string',
            'semester' => 'required|in:Ganjil,Genap',
            'tgl_mulai' => 'required|date',
            'status' => 'required|boolean'
        ]);

        // Jika status yang dikirim adalah TRUE, maka matikan semua status tahun ajaran lainnya
        if ($validated['status']) {
            TahunAjaran::where('status', true)->update(['status' => false]);
        }

        $data = TahunAjaran::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Tahun ajaran berhasil ditambahkan',
                'data' => $data
            ], 201);
        }

        return redirect()->route('academic-year.index')->with('success', 'Data ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $data = TahunAjaran::findOrFail($id);

        $validated = $request->validate([
            'tahun' => 'required|string',
            'semester' => 'required|in:Ganjil,Genap',
            'tgl_mulai' => 'required|date',
            'status' => 'required|boolean'
        ]);

        if ($validated['status']) {
            TahunAjaran::where('id', '!=', $id)->update(['status' => false]);
        }

        $data->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Tahun ajaran berhasil diperbarui'
            ]);
        }

        return redirect()->route('academic-year.index')->with('success', 'Data diperbarui');
    }

    public function destroy(Request $request, $id)
    {
        $data = TahunAjaran::findOrFail($id);
        $data->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Tahun ajaran berhasil dihapus'
            ]);
        }

        return redirect('academic-year.index')->with('success', 'Data tahun ajaran berhasil dihapus');
    }
}