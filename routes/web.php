<?php

use App\Http\Controllers\Admin\AcademicPeriodController;
use App\Http\Controllers\Admin\AcademicPeriodStatusController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CourseOfferingController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\GradeCorrectionController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Reportes: el docente solo ve sus ofertas (scope visibleTo + Policy view).
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/offerings/{offering}', [ReportController::class, 'show'])->name('reports.show');

    // Planilla del docente. Autorización por Policy (docente actual de la oferta).
    // scopeBindings: {enrollment} debe pertenecer a {offering}; si no, 404.
    Route::scopeBindings()->group(function () {
        Route::get('/offerings/{offering}/grades', [GradeController::class, 'edit'])->name('offerings.grades.edit');
        Route::put('/offerings/{offering}/enrollments/{enrollment}/grade', [GradeController::class, 'update'])
            ->name('offerings.grades.update');
    });

    // Administración: solo rol admin (verificado en el servidor por middleware).
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('subjects', SubjectController::class)->except('show');
        Route::resource('academic-periods', AcademicPeriodController::class)->except('show');
        Route::patch('academic-periods/{academic_period}/status', AcademicPeriodStatusController::class)
            ->name('academic-periods.status');
        Route::resource('students', StudentController::class);
        Route::resource('users', UserController::class)->except('show');
        Route::resource('course-offerings', CourseOfferingController::class);
        Route::scopeBindings()->group(function () {
            Route::post('course-offerings/{course_offering}/enrollments', [EnrollmentController::class, 'store'])
                ->name('course-offerings.enrollments.store');
            Route::delete('course-offerings/{course_offering}/enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])
                ->name('course-offerings.enrollments.destroy');
        });
        Route::get('enrollments/{enrollment}/correction', [GradeCorrectionController::class, 'edit'])
            ->name('enrollments.correction.edit');
        Route::put('enrollments/{enrollment}/correction', [GradeCorrectionController::class, 'update'])
            ->name('enrollments.correction.update');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
