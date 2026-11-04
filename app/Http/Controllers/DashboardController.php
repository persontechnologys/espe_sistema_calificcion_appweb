<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $offerings = CourseOffering::query()
            ->visibleTo($user)
            ->with(['academicPeriod', 'subject', 'teacher'])
            ->withCount('enrollments')
            ->latest('id')
            ->limit($user->isAdmin() ? 10 : 100)
            ->get();

        $stats = $user->isAdmin() ? [
            'Períodos' => AcademicPeriod::count(),
            'Asignaturas' => Subject::count(),
            'Estudiantes' => Student::count(),
            'Docentes' => User::teachers()->count(),
            'Ofertas (NRC)' => CourseOffering::count(),
            'Matrículas' => Enrollment::count(),
        ] : [];

        return view('dashboard', compact('offerings', 'stats'));
    }
}
