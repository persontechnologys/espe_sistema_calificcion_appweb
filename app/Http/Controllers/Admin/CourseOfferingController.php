<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DeletesRestricted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CourseOfferingRequest;
use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseOfferingController extends Controller
{
    use DeletesRestricted;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'academic_period_id' => ['nullable', 'integer'],
            'subject_id' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:50'],
        ]);

        $offerings = CourseOffering::query()
            ->with(['academicPeriod', 'subject', 'teacher'])
            ->withCount('enrollments')
            ->when($filters['academic_period_id'] ?? null, fn ($q, $id) => $q->where('academic_period_id', $id))
            ->when($filters['subject_id'] ?? null, fn ($q, $id) => $q->where('subject_id', $id))
            ->search($filters['q'] ?? null, ['nrc'])
            ->orderByDesc('academic_period_id')->orderBy('nrc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.course-offerings.index', [
            'offerings' => $offerings,
            'periods' => AcademicPeriod::orderByDesc('code')->get(),
            'subjects' => Subject::orderBy('code')->get(),
        ]);
    }

    public function show(CourseOffering $courseOffering): View
    {
        $courseOffering->load(['academicPeriod', 'subject', 'teacher']);

        $enrollments = $courseOffering->enrollmentsWithGrades();

        $availableStudents = Student::query()
            ->where('is_active', true)
            ->whereNotIn('id', $enrollments->pluck('student_id'))
            ->orderBy('last_names')->orderBy('first_names')
            ->get();

        return view('admin.course-offerings.show', [
            'offering' => $courseOffering,
            'enrollments' => $enrollments,
            'availableStudents' => $availableStudents,
        ]);
    }

    public function create(): View
    {
        return view('admin.course-offerings.form', ['offering' => new CourseOffering, ...$this->formOptions(null)]);
    }

    public function store(CourseOfferingRequest $request): RedirectResponse
    {
        $offering = CourseOffering::create($request->validated());

        return redirect()->route('admin.course-offerings.show', $offering)->with('status', 'Oferta creada.');
    }

    public function edit(CourseOffering $courseOffering): View
    {
        return view('admin.course-offerings.form', ['offering' => $courseOffering, ...$this->formOptions($courseOffering)]);
    }

    /** Incluye la reasignación de docente: el anterior pierde el acceso de inmediato. */
    public function update(CourseOfferingRequest $request, CourseOffering $courseOffering): RedirectResponse
    {
        $courseOffering->update($request->validated());

        return redirect()->route('admin.course-offerings.show', $courseOffering)->with('status', 'Oferta actualizada.');
    }

    public function destroy(CourseOffering $courseOffering): RedirectResponse
    {
        return $this->deleteRestricted($courseOffering, 'admin.course-offerings.index', 'la oferta');
    }

    private function formOptions(?CourseOffering $offering): array
    {
        return [
            'periods' => AcademicPeriod::orderByDesc('code')->get(),
            'subjects' => Subject::where('is_active', true)
                ->when($offering, fn ($q) => $q->orWhere('id', $offering->subject_id))
                ->orderBy('code')->get(),
            'teachers' => User::teachers()->active()->orderBy('name')->get(),
        ];
    }
}
