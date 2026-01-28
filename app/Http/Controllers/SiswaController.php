<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    /**
     * Ambil data siswa berdasarkan ID & user login
     */
    private function findSiswaByIdAndUser($id)
    {
        return Siswa::where('user_id', Auth::id())->findOrFail($id);
    }

    /**
     * List data siswa
     */
    public function index(Request $request)
    {
        $data = Siswa::where('user_id', Auth::id())
            ->with('user')
            ->get();

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        return view('siswa/index', ['data' => $data]);
    }

    /**
     * Form tambah siswa
     */
    public function create()
    {
        return view('siswa/form');
    }

    /**
     * Simpan data siswa
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nisn' => [
                'required',
                'max:30',
                'unique:siswa,nisn',
            ],
            'nama_siswa' => [
                'required',
                'max:100',
                'regex:/^[a-zA-Z\s\-]+$/'
            ],
        ]);

        $validated['user_id'] = Auth::id();

        $status = Siswa::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => (bool) $status,
                'message' => $status ? 'Siswa berhasil ditambahkan' : 'Siswa gagal ditambahkan',
            ], $status ? 200 : 500);
        }

        return $status
            ? redirect('/siswa')->with('success', 'Data siswa berhasil ditambahkan')
            : redirect('/siswa')->with('error', 'Data siswa gagal ditambahkan');
    }

    /**
     * Form edit siswa
     */
    public function edit($id)
    {
        $data = $this->findSiswaByIdAndUser($id);
        return view('siswa/form', ['data' => $data]);
    }

    /**
     * Update data siswa
     */
    public function update(Request $request, $id)
    {
        $siswa = $this->findSiswaByIdAndUser($id);

        $validated = $request->validate([
            'nisn' => [
                'required',
                'max:30',
                Rule::unique('siswa')->ignore($siswa->id),
            ],
            'nama_siswa' => [
                'required',
                'max:100',
                'regex:/^[a-zA-Z\s\-]+$/'
            ],
        ]);

        $status = $siswa->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => (bool) $status,
                'message' => $status ? 'Siswa berhasil diupdate' : 'Siswa gagal diupdate',
            ], $status ? 200 : 500);
        }

        return $status
            ? redirect('/siswa')->with('success', 'Data siswa berhasil diupdate')
            : redirect('/siswa')->with('error', 'Data siswa gagal diupdate');
    }

    /**
     * Hapus data siswa
     */
    public function destroy(Request $request, $id)
    {
        $siswa = $this->findSiswaByIdAndUser($id);

        $status = $siswa->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => (bool) $status,
                'message' => $status ? 'Siswa berhasil dihapus' : 'Siswa gagal dihapus',
            ], $status ? 200 : 500);
        }

        return $status
            ? redirect('/siswa')->with('success', 'Data siswa berhasil dihapus')
            : redirect('/siswa')->with('error', 'Data siswa gagal dihapus');
    }
}
