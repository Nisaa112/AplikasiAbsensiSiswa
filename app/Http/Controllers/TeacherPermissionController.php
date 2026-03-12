<?php

namespace App\Http\Controllers;

use App\Models\PerizinanGuru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TeacherPermissionController extends Controller
{
    // --- GURU: Mengajukan Izin ---
    public function store(Request $request)
    {
        $request->validate([
            'jenis_izin' => 'required|in:sakit,izin,cuti,dinas_luar',
            'alasan' => 'required|string',
            'bukti' => 'required|image|max:2048',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'guru' || !$user->guru) {
            return response()->json(['message' => 'Hanya guru yang dapat mengajukan izin'], 403);
        }

        $path = $request->file('bukti')->store('perizinan_guru', 'public');

        $izin = PerizinanGuru::create([
            'guru_id' => $user->guru->id,
            'tgl_izin' => now(),
            'jenis_izin' => $request->jenis_izin,
            'alasan' => $request->alasan,
            'bukti_gambar' => $path,
            'status_izin' => 'pending',
        ]);

        return response()->json(['status' => 'success', 'data' => $izin]);
    }

    // --- GURU: Melihat Riwayat Pribadi ---
    public function myHistory()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'guru' || !$user->guru) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        // Mengambil riwayat izin berdasarkan guru_id yang login
        $history = PerizinanGuru::with(['admin', 'kepsek'])
            ->where('guru_id', $user->guru->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }

    // --- ADMIN: Verifikasi Berkas ---
    public function verifyByAdmin(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang dapat memverifikasi berkas'], 403);
        }

        $request->validate(['status' => 'required|in:disetujui_admin,ditolak']);

        $izin = PerizinanGuru::findOrFail($id);
        
        $izin->update([
            'status_izin' => $request->status,
            'admin_id' => $user->id
        ]);

        return response()->json(['message' => 'Berkas telah diperiksa admin. Menunggu persetujuan Kepala Sekolah.']);
    }

    // --- KEPSEK: Persetujuan Final ---
    public function approveByKepsek(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'kepsek') {
            return response()->json(['message' => 'Hanya Kepala Sekolah yang dapat memberikan persetujuan final'], 403);
        }

        $request->validate(['status' => 'required|in:disetujui_kepsek,ditolak']);

        $izin = PerizinanGuru::findOrFail($id);

        if ($izin->status_izin !== 'disetujui_admin' && $request->status === 'disetujui_kepsek') {
            return response()->json(['message' => 'Berkas harus diverifikasi admin terlebih dahulu'], 422);
        }

        $izin->update([
            'status_izin' => $request->status,
            'kepsek_id' => $user->id
        ]);

        if ($request->status === 'disetujui_kepsek') {
            // Logika sinkronisasi ke tabel absensi_guru atau trigger guru pengganti bisa ditaruh di sini
        }

        return response()->json(['message' => 'Keputusan final Kepala Sekolah telah disimpan: ' . $request->status]);
    }

    // --- ADMIN & KEPSEK: Melihat Daftar Semua Izin ---
    public function index()
    {
        $user = Auth::user();
        $query = PerizinanGuru::with(['guru', 'admin', 'kepsek']);

        if ($user->role === 'kepsek') {
            // Kepsek hanya melihat yang sudah lolos admin (agar tidak melihat berkas mentah/pending)
            $query->whereIn('status_izin', ['disetujui_admin', 'disetujui_kepsek', 'ditolak']);
        }

        return response()->json(['data' => $query->latest()->get()]);
    }
}