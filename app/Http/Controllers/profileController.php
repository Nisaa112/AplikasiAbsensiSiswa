<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller {
    public function show() {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data = $user->role === 'guru' ? $user->load('guru') : $user->load('siswa');
        
        return response()->json(['status' => 'success', 'data' => $data]);
    }
}