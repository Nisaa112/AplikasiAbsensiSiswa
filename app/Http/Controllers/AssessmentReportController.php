<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentDetail;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentReportController extends Controller
{
    /**
     * Helper: Mendapatkan ID Tahun Ajaran Aktif
     */
    private function getActiveYearId($request) {
        if ($request->has('tahun_ajaran_id') && (int)$request->tahun_ajaran_id > 0) {
            return $request->tahun_ajaran_id;
        }
        $activeYear = TahunAjaran::where('status', 1)->first();
        return $activeYear ? $activeYear->id : null;
    }

    /**
     * API UTAMA UNTUK FLUTTER: studentPerformance
     * PERBAIKAN: Join lewat assessment_questions untuk mendapatkan kategori
     */
    public function studentPerformance(Request $request) {
        $userId = $request->query('student_id') ?: Auth::id(); 
        $tahunAjaranId = $this->getActiveYearId($request); 

        // 1. Query Radar Chart - Perbaikan Join
        $scores = AssessmentDetail::join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
            // Join ke questions dulu karena category_id ada di sana
            ->join('assessment_questions', 'assessment_questions.id', '=', 'assessment_details.question_id')
            ->join('assessment_categories', 'assessment_categories.id', '=', 'assessment_questions.category_id')
            ->where('assessments.evaluatee_id', $userId)
            ->when($tahunAjaranId, function($q) use ($tahunAjaranId) {
                return $q->where('assessments.tahun_ajaran_id', $tahunAjaranId);
            })
            ->select(
                'assessment_categories.name', 
                DB::raw('CAST(AVG(assessment_details.score) AS FLOAT) as average_score')
            )
            ->groupBy('assessment_categories.name')
            ->get();

        // 2. Query Riwayat/Timeline
        $history = Assessment::where('evaluatee_id', $userId)
            ->when($tahunAjaranId, function($q) use ($tahunAjaranId) {
                return $q->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->latest()
            ->get();

        $totalAvg = $scores->avg('average_score') ?? 0;

        return response()->json([
            'total_score' => round($totalAvg, 1),
            'scores' => $scores,
            'history' => $history
        ]);
    }

    /**
     * API: Rekapitulasi Sederhana (Untuk Dashboard Siswa)
     * PERBAIKAN: Join lewat assessment_questions
     */
    public function student(Request $request)
    {
        $userId = Auth::id();
        $tahunAjaranId = $this->getActiveYearId($request);

        $data = AssessmentDetail::join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
            ->join('assessment_questions', 'assessment_questions.id', '=', 'assessment_details.question_id')
            ->join('assessment_categories', 'assessment_categories.id', '=', 'assessment_questions.category_id')
            ->where('assessments.evaluatee_id', $userId)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('assessments.tahun_ajaran_id', $tahunAjaranId);
            })
            ->select('assessment_categories.name', DB::raw('CAST(AVG(assessment_details.score) AS FLOAT) as average_score'))
            ->groupBy('assessment_categories.name')
            ->get();

        return response()->json($data);
    }

    public function index(Request $request)
    {
        $tahunId = $this->getActiveYearId($request);
        $tingkat = $request->get('tingkat');
        $jurusan = $request->get('jurusan');

        // 1. Data untuk Tabel Siswa
        $query = User::where('role', 'siswa');
        if ($tingkat || $jurusan) {
            $query->whereHas('anggotaKelas.kelas', function($q) use ($tingkat, $jurusan) {
                if ($tingkat) $q->where('tingkat', $tingkat);
                if ($jurusan) $q->where('jurusan', $jurusan);
            });
        }
        $data = $query->with(['assessmentsReceived' => function($q) use ($tahunId) {
            if ($tahunId) $q->where('tahun_ajaran_id', $tahunId);
        }, 'anggotaKelas.kelas'])->get();

        // 2. Data untuk Chart (Rata-rata per Kategori per Kelas)
        $rawAverages = AssessmentDetail::join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
            ->join('assessment_questions', 'assessment_questions.id', '=', 'assessment_details.question_id')
            ->join('assessment_categories', 'assessment_categories.id', '=', 'assessment_questions.category_id')
            ->join('siswa', 'siswa.user_id', '=', 'assessments.evaluatee_id') 
            ->join('anggota_kelas', 'anggota_kelas.siswa_id', '=', 'siswa.id')
            ->join('kelas', 'kelas.id', '=', 'anggota_kelas.kelas_id')
            ->when($tahunId, function($q) use ($tahunId) {
                return $q->where('assessments.tahun_ajaran_id', $tahunId);
            })
            ->when($tingkat, function($q) use ($tingkat) {
                return $q->where('kelas.tingkat', $tingkat);
            })
            ->when($jurusan, function($q) use ($jurusan) {
                return $q->where('kelas.jurusan', $jurusan);
            })
            ->select(
                'kelas.tingkat', 'kelas.jurusan', 'kelas.nomor_kelas',
                'assessment_categories.name as nama_kategori',
                DB::raw('CAST(AVG(assessment_details.score) AS FLOAT) as avg_skor')
            )
            ->groupBy('kelas.tingkat', 'kelas.jurusan', 'kelas.nomor_kelas', 'assessment_categories.name')
            ->get();

        $classAverages = $rawAverages->groupBy(function($item) {
            $romawi = [10 => 'X', 11 => 'XI', 12 => 'XII'];
            $t = $romawi[$item->tingkat] ?? $item->tingkat;
            return "{$t} {$item->jurusan} {$item->nomor_kelas}";
        });

        $years = TahunAjaran::orderBy('tahun', 'desc')->get();
        $majors = Kelas::distinct()->pluck('jurusan');

        return view('assessment-reports.index', compact('data', 'years', 'majors', 'classAverages'));
    }

    /**
     * Detail Web: Perbaikan Join
     */
    public function show(Request $request, $id)
    {
        $siswa = User::findOrFail($id);
        $tahunAjaranId = $request->tahun_ajaran_id;

        $scores = AssessmentDetail::join('assessments', 'assessments.id', '=', 'assessment_details.assessment_id')
            ->join('assessment_questions', 'assessment_questions.id', '=', 'assessment_details.question_id')
            ->join('assessment_categories', 'assessment_categories.id', '=', 'assessment_questions.category_id')
            ->where('assessments.evaluatee_id', $id)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('assessments.tahun_ajaran_id', $tahunAjaranId);
            })
            ->select('assessment_categories.name', DB::raw('CAST(AVG(assessment_details.score) AS FLOAT) as average_score'))
            ->groupBy('assessment_categories.name')
            ->get();

        return view('assessment-reports.show', compact('siswa', 'scores'));
    }

    public function teacherProgress(Request $request) {
        $guruId = Auth::id();
        $tahunAjaranId = $this->getActiveYearId($request);

        $totalStudents = User::where('role', 'siswa')->count();
        
        $assessedCount = Assessment::where('evaluator_id', $guruId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->distinct('evaluatee_id')
            ->count();

        return response()->json([
            'total' => $totalStudents,
            'assessed' => $assessedCount,
            'percentage' => $totalStudents > 0 ? ($assessedCount / $totalStudents) * 100 : 0
        ]);
    }
}