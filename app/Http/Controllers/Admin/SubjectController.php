<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DeletesRestricted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubjectRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    use DeletesRestricted;

    public function index(Request $request): View
    {
        $subjects = Subject::query()
            ->search($request->query('q'), ['code', 'name'])
            ->withCount('courseOfferings')
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('admin.subjects.form', ['subject' => new Subject(['is_active' => true])]);
    }

    public function store(SubjectRequest $request): RedirectResponse
    {
        Subject::create($request->validated());

        return redirect()->route('admin.subjects.index')->with('status', 'Asignatura creada.');
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.form', compact('subject'));
    }

    public function update(SubjectRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());

        return redirect()->route('admin.subjects.index')->with('status', 'Asignatura actualizada.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        return $this->deleteRestricted($subject, 'admin.subjects.index', 'la asignatura');
    }
}
