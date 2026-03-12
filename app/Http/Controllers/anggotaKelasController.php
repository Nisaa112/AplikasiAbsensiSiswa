<?php

namespace App\Http\Controllers;

use App\Models\AnggotaKelas;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;

class AnggotaKelasController extends Controller
{
    public function index(Request $request)
    {
        $query = AnggotaKelas::with(['siswa', 'kelas']);

        if ($request->has('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $data = $query->latest()->get();

        // Data pendukung untuk Modal
        $classes = Kelas::all();
        // Hanya ambil siswa yang BELUM terdaftar di anggota_kelas manapun
        $studentsAvailable = Siswa::whereDoesntHave('anggotaKelas')->get();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $data]);
        }

        return view('anggota.index', compact('data', 'classes', 'studentsAvailable'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id|unique:anggota_kelas,siswa_id',
            'kelas_id' => 'required|exists:kelas,id',
        ], [
            'siswa_id.unique' => 'Siswa sudah terdaftar di kelas lain.'
        ]);

        $item = AnggotaKelas::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'data' => $item->load(['siswa', 'kelas'])], 201);
        }

        return redirect()->route('class-members.index')->with('success', 'Siswa berhasil dimasukkan ke kelas');
    }

    public function update(Request $request, $id)
    {
        $anggota = AnggotaKelas::findOrFail($id);
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $anggota->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Berhasil diperbarui']);
        }

        return redirect()->route('class-members.index')->with('success', 'Data anggota berhasil diperbarui');
    }

    public function destroy(Request $request, $id)
    {
        $data = AnggotaKelas::findOrFail($id);
        $data->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Berhasil dihapus']);
        }

        return redirect()->route('class-members.index')->with('success', 'Siswa telah dikeluarkan dari kelas');
    }
}