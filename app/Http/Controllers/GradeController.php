<?php

namespace App\Http\Controllers;

use App\Http\Requests\Grades\GradeRequest;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Services\Grades\GradeConflictException;
use App\Services\Grades\GradeNotAllowedException;
use App\Services\Grades\GradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Planilla de notas del docente para una oferta propia.
 */
class GradeController extends Controller
{
    public function edit(CourseOffering $offering): View
    {
        Gate::authorize('recordGrades', $offering);

        $offering->load(['academicPeriod', 'subject', 'teacher']);

        return view('grades.edit', [
            'offering' => $offering,
            'enrollments' => $offering->enrollmentsWithGrades(),
            'canEdit' => $offering->academicPeriod->status->allowsTeacherGrading(),
        ]);
    }

    /** {enrollment} está acotada a {offering} (scopeBindings): una matrícula de otra oferta da 404. */
    public function update(GradeRequest $request, CourseOffering $offering, Enrollment $enrollment, GradeService $grades): RedirectResponse
    {
        Gate::authorize('recordGrade', $enrollment);

        $bag = 'grade_'.$enrollment->id;
        $back = redirect()->to(route('offerings.grades.edit', $offering).'#row-'.$enrollment->id);

        try {
            $grades->register($request->user(), $enrollment, $request->partials(), $request->expectedVersion());
        } catch (GradeConflictException|GradeNotAllowedException $e) {
            return $back->withErrors(['general' => $e->getMessage()], $bag);
        }

        return $back->with('status', 'Notas guardadas.');
    }
}
