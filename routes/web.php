<?php

use App\Http\Controllers\AnggotaKelasController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\HariLiburController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssessmentCategoryController; // Import baru
use App\Http\Controllers\AssessmentQuestionController;
use App\Http\Controllers\AssessmentReportController;   // Import baru
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\WebAuthController;
use Illuminate\Support\Facades\Auth;

// Halaman Login (Pintu Masuk)
Route::get('/', function () {
    if (Auth::guard('web')->check()) {
        return redirect()->route('dashboard');
    }
    return view('login');
})->name('login');

// Proses Auth
Route::post('/login-proses', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// GUNAKAN auth:web
Route::middleware(['auth:web'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard'); 
    })->name('dashboard');

    Route::get('/siswa', [SiswaController::class, 'index'])->name('murid.index');
    Route::post('/siswa', [SiswaController::class, 'store'])->name('murid.store');
    Route::put('/siswa/{id}', [SiswaController::class, 'update'])->name('murid.update');
    Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->name('murid.destroy');

    Route::get('/guru', [GuruController::class, 'index'])->name('teacher.index');
    Route::post('/guru', [GuruController::class, 'store'])->name('teacher.store');
    Route::put('/guru/{id}', [GuruController::class, 'update'])->name('teacher.update'); 
    Route::delete('/guru/{id}', [GuruController::class, 'destroy'])->name('teacher.destroy'); 

    Route::get('/user', [UserController::class, 'index'])->name('pengguna.index');
    Route::post('/user', [UserController::class, 'store'])->name('pengguna.store');
    Route::put('/user/{id}', [UserController::class, 'update'])->name('pengguna.update'); 
    Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('pengguna.destroy'); 

    Route::get('/academic-year', [TahunAjaranController::class, 'index'])->name('academic-year.index');
    Route::post('/academic-year', [TahunAjaranController::class, 'store'])->name('academic-year.store');
    Route::put('/academic-year/{id}', [TahunAjaranController::class, 'update'])->name('academic-year.update');
    Route::delete('/academic-year/{id}', [TahunAjaranController::class, 'destroy'])->name('academic-year.destroy');

    Route::get('/academic-calendar', [HariLiburController::class, 'index'])->name('academic-calendar.index');
    Route::post('/academic-calendar', [HariLiburController::class, 'store'])->name('academic-calendar.store');
    Route::put('/academic-calendar/{id}', [HariLiburController::class, 'update'])->name('academic-calendar.update');
    Route::delete('/academic-calendar/{id}', [HariLiburController::class, 'destroy'])->name('academic-calendar.destroy');

    Route::get('/subjects', [MapelController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [MapelController::class, 'store'])->name('subjects.store');
    Route::put('/subjects/{id}', [MapelController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{id}', [MapelController::class, 'destroy'])->name('subjects.destroy');

    Route::get('/classes', [KelasController::class, 'index'])->name('classes.index');
    Route::post('/classes', [KelasController::class, 'store'])->name('classes.store');
    Route::put('/classes/{id}', [KelasController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{id}', [KelasController::class, 'destroy'])->name('classes.destroy');

    Route::get('/class-members', [AnggotaKelasController::class, 'index'])->name('class-members.index');
    Route::post('/class-members', [AnggotaKelasController::class, 'store'])->name('class-members.store');
    Route::put('/class-members/{id}', [AnggotaKelasController::class, 'update'])->name('class-members.update');
    Route::delete('/class-members/{id}', [AnggotaKelasController::class, 'destroy'])->name('class-members.destroy');

    Route::get('/schedules', [JadwalController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [JadwalController::class, 'store'])->name('schedules.store');
    Route::put('/schedules/{id}', [JadwalController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{id}', [JadwalController::class, 'destroy'])->name('schedules.destroy');

    Route::get('/locations', [LokasiController::class, 'index'])->name('locations.index');
    Route::post('/locations', [LokasiController::class, 'store'])->name('locations.store');
    Route::put('/locations/{id}', [LokasiController::class, 'update'])->name('locations.update');
    Route::delete('/locations/{id}', [LokasiController::class, 'destroy'])->name('locations.destroy');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');

    // =========================================================
    // FITUR BARU: EVALUASI SIKAP (UKOM FR-IA.02)
    // =========================================================

    // 1. Manajemen Kategori Penilaian (Admin Only)
    Route::get('/kategori-penilaian', [AssessmentCategoryController::class, 'index'])->name('web.kategori-penilaian.index');
    Route::post('/kategori-penilaian', [AssessmentCategoryController::class, 'store'])->name('web.kategori-penilaian.store');
    Route::put('/kategori-penilaian/{id}', [AssessmentCategoryController::class, 'update'])->name('web.kategori-penilaian.update');
    Route::delete('/kategori-penilaian/{id}', [AssessmentCategoryController::class, 'destroy'])->name('web.kategori-penilaian.destroy');

    // Laporan
    Route::get('/assessment-reports', [AssessmentReportController::class, 'index'])->name('web.assessment-reports.index');
    Route::get('/assessment-reports/{id}', [AssessmentReportController::class, 'show'])->name('web.assessment-reports.show');

    // Route untuk Manajemen Pertanyaan
    Route::get('/pertanyaan', [AssessmentQuestionController::class, 'index'])->name('web.pertanyaan.index');
    Route::post('/pertanyaan', [AssessmentQuestionController::class, 'store'])->name('web.pertanyaan.store');
    Route::put('/pertanyaan/{id}', [AssessmentQuestionController::class, 'update'])->name('web.pertanyaan.update');
    Route::delete('/pertanyaan/{id}', [AssessmentQuestionController::class, 'destroy'])->name('web.pertanyaan.destroy');
});