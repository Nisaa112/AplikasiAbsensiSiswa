<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $data = Guru::with('user')->get();

        // Ambil user role guru yang BELUM terdaftar di tabel guru
        $usersAvailable = User::where('role', 'guru')
            ->whereDoesntHave('guru')
            ->get();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $data]);
        }

        return view('guru.index', compact('data', 'usersAvailable'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id|unique:guru,user_id',
            'nip'        => 'required|max:30|unique:guru,nip',
            'nama_guru'  => 'required|max:100',
            'senioritas' => 'required|in:Senior,Junior',
            'gender'     => 'required|in:L,P',
        ]);

        $status = Guru::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Guru berhasil dibuat',
                'data' => $status,
            ], 201);
        }

        // REDIRECT MENGGUNAKAN NAMA ROUTE
        return redirect()->route('teacher.index')->with('success', 'Data guru berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        $validated = $request->validate([
            'nip'        => ['required', 'max:30', Rule::unique('guru')->ignore($guru->id)],
            'nama_guru'  => 'required|max:100',
            'senioritas' => 'required|in:Senior,Junior',
            'gender'     => 'required|in:L,P',
        ]);

        $guru->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Guru diupdate']);
        }

        return redirect()->route('teacher.index')->with('success', 'Data guru berhasil diupdate');
    }

    public function destroy(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);
        $guru->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Guru dihapus']);
        }

        return redirect()->route('teacher.index')->with('success', 'Data guru berhasil dihapus');
    }
}