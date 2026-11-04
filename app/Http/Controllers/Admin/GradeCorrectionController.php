<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grades\GradeCorrectionRequest;
use App\Models\Enrollment;
use App\Services\Grades\GradeConflictException;
use App\Services\Grades\GradeNotAllowedException;
use App\Services\Grades\GradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Corrección de notas por el administrador, con motivo obligatorio y auditada.
 */
class GradeCorrectionController extends Controller
{
    public function edit(Enrollment $enrollment): View
    {
        Gate::authorize('correctGrade', $enrollment);

        $enrollment->load([
            'student', 'grade',
            'courseOffering.academicPeriod', 'courseOffering.subject', 'courseOffering.teacher',
            'auditLogs' => fn ($q) => $q->latest('id'),
            'auditLogs.user',
        ]);

        return view('admin.grades.correct', compact('enrollment'));
    }

    public function update(GradeCorrectionRequest $request, Enrollment $enrollment, GradeService $grades): RedirectResponse
    {
        Gate::authorize('correctGrade', $enrollment);

        try {
            $grades->correct($request->user(), $enrollment, $request->partials(), $request->expectedVersion(), $request->reason());
        } catch (GradeConflictException|GradeNotAllowedException $e) {
            return back()->withErrors(['general' => $e->getMessage()]);
        }

        return redirect()->route('admin.course-offerings.show', $enrollment->course_offering_id)
            ->with('status', 'Corrección registrada y auditada.');
    }
}
