<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Admin harus bisa mencari user mana saja berdasarkan ID
    private function findUserById($id)
    {
        return User::findOrFail($id);
    }

    /**
     * List Semua User (Untuk Admin)
     */
    public function index(Request $request)
    {
        $data = User::orderBy('id', 'desc')->get();

        // pengecekan ini agar bisa melayani API dan Web sekaligus
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ]);
        }

        return view('user/index', ['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'serial_number' => 'required|string|unique:users,serial_number',
            'password'      => 'required|min:6',
            'role'          => ['required', Rule::in(['admin', 'guru', 'siswa', 'kepsek'])],
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'User berhasil dibuat',
                'data' => $user,
            ], 201);
        }

        return redirect()->route('pengguna.index')->with('success', 'User berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $user = $this->findUserById($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'serial_number' => ['required', Rule::unique('users')->ignore($user->id)],
            'password'      => 'nullable|min:6',
            'role'          => ['required', Rule::in(['admin', 'guru', 'siswa', 'kepsek'])],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        
        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'User berhasil diupdate',
                'data' => $user
            ]);
        }

        return redirect('/user')->with('success', 'Data user berhasil diupdate');
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->findUserById($id);
        $user->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'User berhasil dihapus',
            ]);
        }

        return redirect('/user')->with('success', 'Data user berhasil dihapus');
    }
}