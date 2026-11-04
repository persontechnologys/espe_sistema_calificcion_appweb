<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Grade;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    public function store(Request $request, CourseOffering $courseOffering): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where('is_active', true)],
        ], ['student_id.exists' => 'El estudiante no existe o está inactivo.']);

        try {
            DB::transaction(function () use ($courseOffering, $validated) {
                // Bloqueo compartido: el período no puede cerrarse mientras se matricula.
                $period = AcademicPeriod::whereKey($courseOffering->academic_period_id)->sharedLock()->firstOrFail();

                if (! $period->status->allowsEnrollment()) {
                    throw ValidationException::withMessages(['student_id' => "El período {$period->code} está cerrado: no admite matrículas."]);
                }

                Enrollment::create(['student_id' => $validated['student_id'], 'course_offering_id' => $courseOffering->id]);
            });
        } catch (UniqueConstraintViolationException) {
            // UNIQUE(student_id, course_offering_id): protege también ante dos solicitudes simultáneas.
            return back()->withErrors(['student_id' => 'El estudiante ya está matriculado en esta oferta.']);
        }

        return back()->with('status', 'Estudiante matriculado.');
    }

    /** Solo para corregir una matrícula errónea sin notas. La anulación de matrículas está fuera del alcance. */
    public function destroy(CourseOffering $courseOffering, Enrollment $enrollment): RedirectResponse
    {
        try {
            DB::transaction(function () use ($courseOffering, $enrollment) {
                $period = AcademicPeriod::whereKey($courseOffering->academic_period_id)->sharedLock()->firstOrFail();

                if (! $period->status->allowsEnrollment()) {
                    throw ValidationException::withMessages(['general' => 'El período está cerrado: no se modifican matrículas.']);
                }
                if (Grade::where('enrollment_id', $enrollment->id)->lockForUpdate()->exists()) {
                    throw ValidationException::withMessages(['general' => 'La matrícula tiene calificaciones registradas: no se puede eliminar.']);
                }

                $enrollment->delete();
            });
        } catch (QueryException) {
            return back()->withErrors(['general' => 'La matrícula tiene registros dependientes: no se puede eliminar.']);
        }

        return back()->with('status', 'Matrícula eliminada.');
    }
}
