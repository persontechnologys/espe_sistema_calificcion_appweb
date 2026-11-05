<?php

namespace App\Http\Controllers;

use App\Enums\PeriodStatus;
use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Panel de inicio. Solo lectura: conteos con consultas agregadas y carga anticipada (sin N+1).
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $offerings = CourseOffering::query()
            ->visibleTo($user)
            ->with(['academicPeriod', 'subject', 'teacher'])
            ->withCount([
                'enrollments',
                // Registro completo: existe la nota y los tres parciales no son NULL.
                'enrollments as complete_count' => fn (Builder $q) => $q->whereHas('grade', fn (Builder $g) => $g
                    ->whereNotNull('partial_1')->whereNotNull('partial_2')->whereNotNull('partial_3')),
            ])
            ->latest('id')
            ->limit($user->isAdmin() ? 5 : 100)
            ->get();

        if (! $user->isAdmin()) {
            return view('dashboard', ['offerings' => $offerings, 'stats' => [], 'openPeriod' => null, 'activity' => collect()]);
        }

        $openPeriods = AcademicPeriod::query()->where('status', PeriodStatus::Open->value);

        $stats = [
            'students' => Student::where('is_active', true)->count(),
            'teachers' => User::teachers()->active()->count(),
            'subjects' => Subject::where('is_active', true)->count(),
            'openOfferings' => CourseOffering::whereIn('academic_period_id', (clone $openPeriods)->select('id'))->count(),
            'enrollments' => Enrollment::count(),
            // Incompleto: matrícula sin registro de notas o con algún parcial NULL (especificación §4.3.2).
            'incomplete' => Enrollment::query()
                ->where(fn (Builder $q) => $q->doesntHave('grade')->orWhereHas('grade', fn (Builder $g) => $g
                    ->whereNull('partial_1')->orWhereNull('partial_2')->orWhereNull('partial_3')))
                ->count(),
        ];

        return view('dashboard', [
            'offerings' => $offerings,
            'stats' => $stats,
            'openPeriod' => (clone $openPeriods)->orderByDesc('start_date')->first(),
            'activity' => AuditLog::with(['user', 'enrollment.student', 'enrollment.courseOffering'])->latest('id')->limit(5)->get(),
        ]);
    }
}
