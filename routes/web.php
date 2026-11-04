<?php

use App\Http\Controllers\Admin\AcademicPeriodController;
use App\Http\Controllers\Admin\AcademicPeriodStatusController;
use App\Http\Controllers\Admin\CourseOfferingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Administración: solo rol admin (verificado en el servidor por middleware).
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('subjects', SubjectController::class)->except('show');
        Route::resource('academic-periods', AcademicPeriodController::class)->except('show');
        Route::patch('academic-periods/{academic_period}/status', AcademicPeriodStatusController::class)
            ->name('academic-periods.status');
        Route::resource('students', StudentController::class)->except('show');
        Route::resource('users', UserController::class)->except('show');
        Route::resource('course-offerings', CourseOfferingController::class)->except('show');
    });
});
