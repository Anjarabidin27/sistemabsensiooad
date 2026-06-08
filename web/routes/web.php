<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\FaceManagementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// ── Root Redirect ────────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth('student')->check()) {
        return redirect()->route('student.home');
    }
    if (auth('web')->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('student.login');
});

// ── Student Guest Routes ──────────────────────────────────────────────────
Route::middleware('guest:student')->group(function () {
    Route::get('/login', [AuthController::class, 'showStudentLogin'])->name('student.login');
    Route::post('/login', [AuthController::class, 'studentLogin']);
});

// ── Student Authenticated Routes ──────────────────────────────────────────
Route::middleware('auth:student')->group(function () {
    Route::post('/logout', [AuthController::class, 'studentLogout'])->name('student.logout');
    
    Route::get('/home', [AttendanceController::class, 'studentHome'])->name('student.home');
    Route::get('/scan', [AttendanceController::class, 'showScanner'])->name('student.scanner');
    Route::post('/scan', [AttendanceController::class, 'processScan'])->name('student.scanner.process');
    Route::get('/history', [AttendanceController::class, 'studentHistory'])->name('student.history');
    Route::get('/schedule', [AttendanceController::class, 'studentSchedule'])->name('student.schedule');
    Route::get('/profile', [AttendanceController::class, 'studentProfile'])->name('student.profile');
    Route::post('/profile/photo', [AttendanceController::class, 'uploadProfilePhoto'])->name('student.profile.upload');
});

// ── Admin Guest Routes ────────────────────────────────────────────────────
Route::prefix('admin')->middleware('guest:web')->group(function () {
    Route::get('/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'adminLogin']);
});

// ── Admin Authenticated Routes ────────────────────────────────────────────
Route::prefix('admin')->middleware('auth:web')->group(function () {
    Route::post('/logout', [AuthController::class, 'adminLogout'])->name('admin.logout');
    
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Students CRUD
    Route::resource('students', StudentController::class)->names('admin.students');
    
    // Face Management
    Route::get('students/{student}/face', [FaceManagementController::class, 'show'])->name('admin.students.face');
    Route::post('students/{student}/face', [FaceManagementController::class, 'register'])->name('admin.students.face.register');
    Route::delete('students/{student}/face', [FaceManagementController::class, 'destroy'])->name('admin.students.face.destroy');
    
    // Courses CRUD
    Route::resource('courses', CourseController::class)->names('admin.courses');
    Route::post('courses/{course}/toggle-location', [CourseController::class, 'toggleLocation'])->name('admin.courses.toggle-location');
    
    // Attendances
    Route::get('attendances', [AttendanceController::class, 'index'])->name('admin.attendances.index');
    Route::delete('attendances/{attendance}', [AttendanceController::class, 'destroy'])->name('admin.attendances.destroy');
    
    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::get('reports/export', [ReportController::class, 'export'])->name('admin.reports.export');
    
    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('admin.settings');
    Route::post('settings', [SettingsController::class, 'update'])->name('admin.settings.update');
});
