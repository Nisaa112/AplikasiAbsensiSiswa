<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Guru;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $data = Kelas::with(['tahunAjaran', 'waliKelas'])->get();
        
        // Data pendukung untuk Dropdown di Modal
        $teachers = Guru::orderBy('nama_guru', 'asc')->get();
        $years = TahunAjaran::orderBy('tahun', 'desc')->get();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $data]);
        }

        return view('kelas.index', compact('data', 'teachers', 'years'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tingkat'         => 'required|integer',
            'jurusan'         => 'required|string|max:20',
            'nomor_kelas'     => 'required|string|max:5',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'wali_kelas_id'   => 'required|exists:guru,id',
        ]);

        $status = Kelas::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'data' => $status], 201);
        }

        return redirect()->route('classes.index')->with('success', 'Data kelas berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);
        $validated = $request->validate([
            'tingkat'         => 'required|integer',
            'jurusan'         => 'required|string|max:20',
            'nomor_kelas'     => 'required|string|max:5',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'wali_kelas_id'   => 'required|exists:guru,id',
        ]);

        $kelas->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Berhasil diupdate']);
        }

        return redirect()->route('classes.index')->with('success', 'Data kelas berhasil diperbarui');
    }

    public function destroy(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Berhasil dihapus']);
        }

        return redirect()->route('classes.index')->with('success', 'Data kelas berhasil dihapus');
    }
}