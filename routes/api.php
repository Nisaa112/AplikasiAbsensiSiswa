<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AcademicController;
use App\Http\Controllers\AnggotaKelasController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AssessmentCategoryController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssessmentQuestionController;
use App\Http\Controllers\AssessmentReportController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\HariLiburController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\PiketController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\TeacherPermissionController;
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
    Route::resource('absensi', AbsensiController::class)->only(['index', 'store']);
    Route::apiResource('anggota-kelas', AnggotaKelasController::class)->only(['index', 'store','update', 'destroy']);
    Route::apiResource('tahun-ajaran', TahunAjaranController::class);
    Route::apiResource('hari-libur', HariLiburController::class);
    Route::apiResource('lokasi', LokasiController::class);

    Route::get('/academic/master', [AcademicController::class, 'getMasterData']);

    Route::get('/schedule/mine', [ScheduleController::class, 'mySchedule']);
    Route::get('/schedule/at-date', [ScheduleController::class, 'getScheduleByDate']);
    Route::get('/schedule/markers', [ScheduleController::class, 'getCalendarMarkers']);

    Route::post('/attendance/session', [AttendanceController::class, 'createSesi']); 
    Route::post('/attendance/scan', [AttendanceController::class, 'scanQR']);
    Route::get('/teacher/my-classes', [AttendanceController::class, 'getTeacherClasses']);
    Route::get('/absensi/riwayat', [AttendanceController::class, 'historySiswa']);
    Route::get('/teacher/attendance-report', [AttendanceController::class, 'getAttendanceReport']);

    Route::get('/report/chart', [ReportController::class, 'chartData']);
    Route::get('/report-guru/export', [ReportController::class, 'exportExcel']);
    Route::get('/report-guru/pdf', [ReportController::class, 'exportPdf']);
    Route::get('/report/principal-summary', [ReportController::class, 'getPrincipalSummary']);

    Route::post('/permission/apply', [PermissionController::class, 'store']);
    Route::put('/permission/validate/{id}', [PermissionController::class, 'validateIzin']); 
    Route::get('/permission', [PermissionController::class, 'index']); 

    Route::post('/permission/teacher/apply', [TeacherPermissionController::class, 'store']); 
    Route::get('/permission/teacher/mine', [TeacherPermissionController::class, 'myHistory']); 
    Route::get('/permission/teacher/all', [TeacherPermissionController::class, 'index']); 
    Route::put('/permission/teacher/verify/{id}', [TeacherPermissionController::class, 'verifyByAdmin']); 
    Route::put('/permission/teacher/approve/{id}', [TeacherPermissionController::class, 'approveByKepsek']);

    Route::get('/piket/today', [PiketController::class, 'todayPiket']);
    Route::get('/piket', [PiketController::class, 'index']);
    Route::post('/piket', [PiketController::class, 'store']);
    Route::post('/piket/generate', [PiketController::class, 'generate']);
    Route::delete('/piket/{id}', [PiketController::class, 'destroy']);

    Route::apiResource('assessment-categories', AssessmentCategoryController::class);

    Route::apiResource('assessment-questions', AssessmentQuestionController::class);
    
    Route::get('/assessment/questions/{categoryId}', [AssessmentQuestionController::class, 'getByCategory']);
    
    Route::get('/assessment/students', [AssessmentController::class, 'getStudentsToAssess']);
    Route::post('/assessment/store', [AssessmentController::class, 'store']);
    
    Route::get('/assessment/report/student', [AssessmentReportController::class, 'studentPerformance']);
    Route::get('/assessment/report/teacher-progress', [AssessmentReportController::class, 'teacherProgress']);
    
});
