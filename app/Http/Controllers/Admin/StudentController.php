<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DeletesRestricted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentRequest;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    use DeletesRestricted;

    public function index(Request $request): View
    {
        $students = Student::query()
            ->search($request->query('q'), ['institutional_id', 'first_names', 'last_names'])
            ->withCount('enrollments')
            ->orderBy('last_names')->orderBy('first_names')
            ->paginate(15)
            ->withQueryString();

        return view('admin.students.index', compact('students'));
    }

    /** Consulta de calificaciones por estudiante. */
    public function show(Student $student): View
    {
        $student->load([
            'enrollments' => fn ($q) => $q->latest('id'),
            'enrollments.grade',
            'enrollments.courseOffering.academicPeriod',
            'enrollments.courseOffering.subject',
            'enrollments.courseOffering.teacher',
        ]);

        return view('admin.students.show', compact('student'));
    }

    public function create(): View
    {
        return view('admin.students.form', ['student' => new Student(['is_active' => true])]);
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        Student::create($request->validated());

        return redirect()->route('admin.students.index')->with('status', 'Estudiante creado.');
    }

    public function edit(Student $student): View
    {
        return view('admin.students.form', compact('student'));
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->validated());

        return redirect()->route('admin.students.index')->with('status', 'Estudiante actualizado.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        return $this->deleteRestricted($student, 'admin.students.index', 'el estudiante');
    }
}
