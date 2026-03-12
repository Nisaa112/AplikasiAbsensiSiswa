<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HariLiburController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil semua data hari libur khusus sekolah dari database lokal
        $liburSekolah = \App\Models\HariLibur::all()->map(function($item) {
            return [
                'id' => $item->id,
                'tanggal' => $item->tanggal,
                'keterangan' => $item->keterangan,
                'is_nasional' => false
            ];
        });

        try {
            // Mengambil data hari libur nasional secara real-time dari API eksternal
            $response = Http::get('https://libur.deno.dev/api');
            $liburNasional = $response->successful() ? collect($response->json()) : collect();
            
            // Mengubah format data dari API agar sesuai dengan struktur data lokal
            $liburNasionalMapped = $liburNasional->map(function($item) {
                return [
                    'id' => null,
                    'tanggal' => $item['date'],
                    'keterangan' => $item['name'],
                    'is_nasional' => true
                ];
            });
        } catch (\Exception $e) {
            $liburNasionalMapped = collect();
        }

        // Menggabungkan data libur sekolah dan nasional
        $data = $liburSekolah->concat($liburNasionalMapped)->sortBy('tanggal')->values();

        // Jika request meminta JSON (API/AJAX)
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        }

        // Jika request dari browser biasa
        return view('academic-calendar.index', ['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date|unique:hari_libur,tanggal',
            'keterangan' => 'required|string|max:255',
        ]);

        $data = HariLibur::create($validated);
        
        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'data' => $data], 201);
        }

        // Perbaikan: Gunakan route name
        return redirect()->route('academic-calendar.index')->with('success', 'Hari libur sekolah berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $libur = HariLibur::findOrFail($id);
        $validated = $request->validate([
            'tanggal' => 'required|date|unique:hari_libur,tanggal,'.$id,
            'keterangan' => 'required|string|max:255',
        ]);

        $libur->update($validated);
        
        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Diperbarui']);
        }

        return redirect()->route('academic-calendar.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy(Request $request, $id)
    {
        $libur = HariLibur::findOrFail($id);
        $libur->delete();
        
        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Dihapus']);
        }

        return redirect()->route('academic-calendar.index')->with('success', 'Hari libur berhasil dihapus');
    }
}