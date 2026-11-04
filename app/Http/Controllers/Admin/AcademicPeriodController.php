<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DeletesRestricted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AcademicPeriodRequest;
use App\Models\AcademicPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicPeriodController extends Controller
{
    use DeletesRestricted;

    public function index(Request $request): View
    {
        $periods = AcademicPeriod::query()
            ->search($request->query('q'), ['code', 'description'])
            ->withCount('courseOfferings')
            ->orderByDesc('code')
            ->paginate(15)
            ->withQueryString();

        return view('admin.academic-periods.index', compact('periods'));
    }

    public function create(): View
    {
        return view('admin.academic-periods.form', ['period' => new AcademicPeriod]);
    }

    public function store(AcademicPeriodRequest $request): RedirectResponse
    {
        AcademicPeriod::create($request->validated()); // Siempre nace "planned".

        return redirect()->route('admin.academic-periods.index')->with('status', 'Período creado.');
    }

    public function edit(AcademicPeriod $academicPeriod): View
    {
        return view('admin.academic-periods.form', ['period' => $academicPeriod]);
    }

    public function update(AcademicPeriodRequest $request, AcademicPeriod $academicPeriod): RedirectResponse
    {
        $academicPeriod->update($request->validated());

        return redirect()->route('admin.academic-periods.index')->with('status', 'Período actualizado.');
    }

    public function destroy(AcademicPeriod $academicPeriod): RedirectResponse
    {
        return $this->deleteRestricted($academicPeriod, 'admin.academic-periods.index', 'el período');
    }
}
