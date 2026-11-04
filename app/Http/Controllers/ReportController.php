<?php

namespace App\Http\Controllers;

use App\Enums\GradeStatus;
use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Reporte de calificaciones por oferta (NRC). Vista HTML imprimible (@media print);
 * el PDF se obtiene con "Imprimir → Guardar como PDF" del navegador.
 */
class ReportController extends Controller
{
    /** Filtro por período, asignatura y NRC. El docente solo ve sus ofertas. */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'academic_period_id' => ['nullable', 'integer'],
            'subject_id' => ['nullable', 'integer'],
            'nrc' => ['nullable', 'string', 'max:20'],
        ]);

        $user = $request->user();
        $visible = CourseOffering::query()->visibleTo($user);

        $offerings = (clone $visible)
            ->with(['academicPeriod', 'subject', 'teacher'])
            ->withCount('enrollments')
            ->when($filters['academic_period_id'] ?? null, fn ($q, $id) => $q->where('academic_period_id', $id))
            ->when($filters['subject_id'] ?? null, fn ($q, $id) => $q->where('subject_id', $id))
            ->search($filters['nrc'] ?? null, ['nrc'])
            ->orderByDesc('academic_period_id')->orderBy('nrc')
            ->paginate(20)
            ->withQueryString();

        // Las listas de filtros solo incluyen períodos/asignaturas de ofertas visibles.
        return view('reports.index', [
            'offerings' => $offerings,
            'periods' => AcademicPeriod::whereIn('id', (clone $visible)->select('academic_period_id'))->orderByDesc('code')->get(),
            'subjects' => Subject::whereIn('id', (clone $visible)->select('subject_id'))->orderBy('code')->get(),
        ]);
    }

    public function show(CourseOffering $offering): View
    {
        Gate::authorize('view', $offering);

        $offering->load(['academicPeriod', 'subject', 'teacher']);
        $enrollments = $offering->enrollmentsWithGrades();

        $rows = $enrollments->map(fn ($e) => ['enrollment' => $e, 'result' => Grade::resultFor($e->grade)]);

        $summary = collect(GradeStatus::cases())
            ->mapWithKeys(fn (GradeStatus $s) => [$s->value => $rows->where('result.status', $s)->count()]);

        return view('reports.show', [
            'offering' => $offering,
            'rows' => $rows,
            'summary' => $summary,
            'generatedAt' => now(),
        ]);
    }
}
