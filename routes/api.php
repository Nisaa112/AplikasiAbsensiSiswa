<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AcademicController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'show']);

    Route::apiResource('user', UserController::class);
    Route::apiResource('guru', GuruController::class);
    Route::apiResource('siswa', SiswaController::class);
    Route::apiResource('jadwal', JadwalController::class);
    Route::apiResource('kelas', KelasController::class);
    Route::apiResource('mapel', MapelController::class);
    Route::apiResource('absensi', AbsensiController::class);

    Route::get('/academic/master', [AcademicController::class, 'getMasterData']);

    Route::get('/schedule/mine', [ScheduleController::class, 'mySchedule']);

    Route::post('/attendance/session', [AttendanceController::class, 'createSesi']); 
    Route::post('/attendance/scan', [AttendanceController::class, 'scanQR']);

    Route::get('/absensi/riwayat', [AttendanceController::class, 'historySiswa']);

    Route::get('/report/chart', [ReportController::class, 'chartData']);
    Route::get('/report-guru/export', [ReportController::class, 'exportExcel']);
    Route::get('/report-guru/pdf', [ReportController::class, 'exportPdf']);

    Route::post('/permission/apply', [PermissionController::class, 'store']);
    Route::put('/permission/validate/{id}', [PermissionController::class, 'validateIzin']); 
    Route::get('/permission', [PermissionController::class, 'index']); 
});
