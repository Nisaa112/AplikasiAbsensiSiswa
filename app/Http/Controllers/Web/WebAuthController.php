<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'serial_number' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            'serial_number' => $request->serial_number,
            'password' => $request->password,
        ];

        if (Auth::guard('web')->attempt($credentials, $request->remember)) {
            $user = Auth::guard('web')->user();
            $role = strtolower($user->role); 

            if (!in_array($role, ['admin', 'kepsek'])) {
                Auth::guard('web')->logout();
                return back()->withErrors(['serial_number' => 'Role Anda tidak diizinkan.']);
            }

            $request->session()->regenerate();
            
            // Gunakan redirect()->route('dashboard') agar lebih pasti
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['serial_number' => 'Kredensial tidak cocok dengan data kami.']);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('info', 'Anda telah keluar.');
    }
}