<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    /**
     * Mendapatkan daftar siswa yang harus dinilai oleh guru (Penilai)
     * Difilter berdasarkan Tahun Ajaran aktif
     */
    public function getStudentsToAssess(Request $request) {
        $guruId = Auth::id();
        $tahunAjaranId = $request->tahun_ajaran_id; // Diambil dari state aplikasi di Flutter

        // Mengambil siswa dengan info penilaian terakhir mereka di tahun ajaran ini
        $students = User::where('role', 'siswa')
            ->with(['assessmentsReceived' => function($q) use ($tahunAjaranId) {
                $q->where('tahun_ajaran_id', $tahunAjaranId)
                  ->latest();
            }])->get();

        return response()->json($students);
    }

    /**
     * Menyimpan Penilaian (Transaction untuk Header & Detail)
     */
    public function store(Request $request) {
        // Validasi data masuk
        $request->validate([
            'evaluatee_id' => 'required|exists:users,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'assessment_date' => 'required|date',
            'scores' => 'required|array', // Format: [{"category_id": 1, "score": 5}, ...]
            'scores.*.category_id' => 'required|exists:assessment_categories,id',
            'scores.*.score' => 'required|numeric|min:1|max:5',
        ]);

        return DB::transaction(function () use ($request) {
            $assessment = Assessment::create([
                'evaluator_id' => Auth::id(),
                'evaluatee_id' => $request->evaluatee_id,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'general_notes' => $request->general_notes,
                'assessment_date' => now(), // TAMBAHKAN INI agar tidak error SQL
            ]);

            // 2. Simpan Detail Penilaian (Looping per Kategori)
            foreach ($request->scores as $item) {
                AssessmentDetail::create([
                    'assessment_id' => $assessment->id,
                    'question_id'   => $item['question_id'], // Gunakan question_id sesuai migrasi
                    'score'         => $item['score']
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Penilaian sikap berhasil disimpan',
                'data' => $assessment->load('details')
            ], 201);
        });
    }

    /**
     * Mendapatkan detail penilaian spesifik
     */
    public function show($id)
    {
        $assessment = Assessment::with(['details.category', 'evaluatee', 'tahunAjaran'])
            ->findOrFail($id);
            
        return response()->json($assessment);
    }
}