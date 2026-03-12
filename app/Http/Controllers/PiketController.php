<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\PerizinanGuru;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PiketController extends Controller
{
    public function todayPiket(Request $request)
    {
        $tanggal = $request->query('date', now()->toDateString());
        $data = JadwalPiket::with('guru')->where('tanggal', $tanggal)->get();

        if ($data->isEmpty() && $tanggal == now()->toDateString()) {
            $this->generateInternal($tanggal, 3);
            $data = JadwalPiket::with('guru')->where('tanggal', $tanggal)->get();
        }

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    public function index(Request $request)
    {
        $tanggal = $request->query('date', now()->toDateString());
        $data = JadwalPiket::with('guru')->where('tanggal', $tanggal)->get();
        return response()->json(['status' => 'success', 'data' => $data]);

        return view('piket/index', ['data' => $data]);
    }

    public function generate(Request $request)
    {
        $request->validate(['tanggal' => 'required|date', 'kuota' => 'required|integer']);
        return $this->generateInternal($request->tanggal, $request->kuota);
    }

    private function generateInternal($tanggal, $kuota)
    {
        $hariIndo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        $targetDate = Carbon::parse($tanggal);
        $namaHariTarget = $hariIndo[$targetDate->format('l')];

        $startOfWeek = $targetDate->copy()->startOfWeek();
        $endOfWeek = $targetDate->copy()->endOfWeek();

        $guruIzinIds = PerizinanGuru::where('tgl_izin', $tanggal)
            ->where('status_izin', 'disetujui_kepsek')
            ->pluck('guru_id');

        $kandidat = Guru::whereNotIn('id', $guruIzinIds)->get();

        $scoredGurus = $kandidat->map(function ($guru) use ($namaHariTarget, $startOfWeek, $endOfWeek) {
            $jamHariIni = Jadwal::where('guru_id', $guru->id)->where('hari', $namaHariTarget)->count();
            $totalJamSeminggu = Jadwal::where('guru_id', $guru->id)->count();
            $sudahPiketMingguIni = JadwalPiket::where('guru_id', $guru->id)
                ->whereBetween('tanggal', [$startOfWeek, $endOfWeek])->exists();

            // SKORING
            $skor = ($totalJamSeminggu * 5); // Beban kerja mingguan
            if ($jamHariIni > 0) $skor += 500; // Penalti jika ada jadwal hari ini
            if ($jamHariIni > 4) $skor += 5000; // PENALTI SANGAT BERAT (Kim Seungmin)
            if ($sudahPiketMingguIni) $skor += 2000; // Adil (rotasi)

            return [
                'id' => $guru->id,
                'skor' => $skor,
                'nama' => $guru->nama_guru,
            ];
        });

        $selected = $scoredGurus->sortBy('skor')->take($kuota);

        JadwalPiket::where('tanggal', $tanggal)->delete();

        foreach ($selected as $item) {
            JadwalPiket::create(['guru_id' => $item['id'], 'tanggal' => $tanggal]);
        }

        return response()->json([
            'status' => 'success',
            'data' => JadwalPiket::with('guru')->where('tanggal', $tanggal)->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'tanggal' => 'required|date',
        ]);
        $data = JadwalPiket::updateOrCreate(['guru_id' => $request->guru_id, 'tanggal' => $request->tanggal]);
        return response()->json(['status' => true, 'data' => $data->load('guru')]);

        return redirect('/piket')->with('success', 'Data piket berhasil ditambahkan');
    }

    public function destroy($id)
    {
        JadwalPiket::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Dihapus']);

        return redirect('/piket')->with('success', 'Data piket berhasil dihapus');
    }
}