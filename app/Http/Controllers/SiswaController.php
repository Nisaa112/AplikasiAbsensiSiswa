<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    private function findSiswaById($id)
    {
        return Siswa::findOrFail($id);
    }

    public function index(Request $request)
    {
        $data = Siswa::with('user')->get();

        // Ambil user role siswa yang belum punya profil siswa
        $usersAvailable = User::where('role', 'siswa')
            ->whereDoesntHave('siswa')
            ->select('id', 'name', 'serial_number')
            ->get();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $data]);
        }

        return view('siswa.index', compact('data', 'usersAvailable'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id|unique:siswa,user_id',
            'nisn'       => 'required|max:30|unique:siswa,nisn',
            'nama_siswa' => 'required|max:100',
        ]);

        $status = Siswa::create($validated);

        // CEK APAKAH REQUEST DARI APLIKASI (API)
        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Siswa berhasil ditambahkan',
                'data' => $status
            ], 201);
        }

        // JIKA DARI WEB (BROWSER), REDIRECT KE ROUTE NAME 'murid.index'
        return redirect()->route('murid.index')->with('success', 'Data siswa berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $siswa = $this->findSiswaById($id);

        $validated = $request->validate([
            'nisn'       => ['required', 'max:30', Rule::unique('siswa')->ignore($siswa->id)],
            'nama_siswa' => 'required|max:100',
            // User ID dikunci agar tidak berubah saat update untuk keamanan data
        ]);

        $siswa->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Siswa berhasil diupdate',
            ]);
        }

        return redirect()->route('murid.index')->with('success', 'Data siswa berhasil diupdate');
    }

    public function destroy(Request $request, $id)
    {
        $siswa = $this->findSiswaById($id);
        $siswa->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Siswa berhasil dihapus',
            ]);
        }

        return redirect()->route('murid.index')->with('success', 'Data siswa berhasil dihapus');
    }
}