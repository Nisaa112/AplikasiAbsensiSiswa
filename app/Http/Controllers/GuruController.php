<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    /**
     * Ambil data guru berdasarkan ID & user login
     */
    private function findGuruByIdAndUser($id)
    {
        return Guru::where('user_id', Auth::id())->findOrFail($id);
    }

    /**
     * List data guru
     */
    public function index(Request $request)
    {
        $data = Guru::all();

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        return view('guru/index', ['data' => $data]);
    }

    /**
     * Form tambah guru
     */
    public function create()
    {
        return view('guru/form');
    }

    /**
     * Simpan data guru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => [
                'required',
                'max:30',
                'unique:guru,nip',
            ],
            'nama_guru' => [
                'required',
                'max:100',
                'regex:/^[a-zA-Z\s\-]+$/'
            ],
        ]);

        $validated['user_id'] = Auth::id();

        $status = Guru::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => (bool) $status,
                'message' => $status ? 'Guru berhasil ditambahkan' : 'Guru gagal ditambahkan',
            ], $status ? 200 : 500);
        }

        return $status
            ? redirect('/guru')->with('success', 'Data guru berhasil ditambahkan')
            : redirect('/guru')->with('error', 'Data guru gagal ditambahkan');
    }

    /**
     * Form edit guru
     */
    public function edit($id)
    {
        $data = $this->findGuruByIdAndUser($id);
        return view('guru/form', ['data' => $data]);
    }

    /**
     * Update data guru
     */
    public function update(Request $request, $id)
    {
        $guru = $this->findGuruByIdAndUser($id);

        $validated = $request->validate([
            'nip' => [
                'required',
                'max:30',
                Rule::unique('guru')->ignore($guru->id),
            ],
            'nama_guru' => [
                'required',
                'max:100',
                'regex:/^[a-zA-Z\s\-]+$/'
            ],
        ]);

        $status = $guru->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => (bool) $status,
                'message' => $status ? 'Guru berhasil diupdate' : 'Guru gagal diupdate',
            ], $status ? 200 : 500);
        }

        return $status
            ? redirect('/guru')->with('success', 'Data guru berhasil diupdate')
            : redirect('/guru')->with('error', 'Data guru gagal diupdate');
    }

    /**
     * Hapus data guru
     */
    public function destroy(Request $request, $id)
    {
        $guru = $this->findGuruByIdAndUser($id);

        $status = $guru->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => (bool) $status,
                'message' => $status ? 'Guru berhasil dihapus' : 'Guru gagal dihapus',
            ], $status ? 200 : 500);
        }

        return $status
            ? redirect('/guru')->with('success', 'Data guru berhasil dihapus')
            : redirect('/guru')->with('error', 'Data guru gagal dihapus');
    }
}
