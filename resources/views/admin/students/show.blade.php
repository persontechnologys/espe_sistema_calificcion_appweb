@extends('layouts.app')
@section('title', 'Calificaciones del estudiante')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $student->full_name }}</h1>
    <a class="btn btn-outline-primary" href="{{ route('admin.students.edit', $student) }}">Editar</a>
</div>
<p class="text-muted">ID institucional: <strong>{{ $student->institutional_id }}</strong>
    @if ($student->email) · {{ $student->email }} @endif
    · @include('partials.active-badge', ['active' => $student->is_active])</p>

<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>Período</th><th>NRC</th><th>Asignatura</th><th>Docente</th>
        <th class="text-end">1P</th><th class="text-end">2P</th><th class="text-end">3P</th><th class="text-end">Promedio /20</th><th class="text-end">Total /60</th><th>Estado</th></tr></thead>
    <tbody>
    @forelse ($student->enrollments as $enrollment)
        @php($o = $enrollment->courseOffering)
        <tr>
            <td>{{ $o->academicPeriod->code }}</td>
            <td><a href="{{ route('admin.course-offerings.show', $o) }}">{{ $o->nrc }}</a></td>
            <td>{{ $o->subject->code }} · {{ $o->subject->name }}</td>
            <td>{{ $o->teacher->name }}</td>
            @include('partials.grade-cells', ['grade' => $enrollment->grade])
        </tr>
    @empty
        <tr><td colspan="10" class="text-center text-muted py-4">Sin matrículas.</td></tr>
    @endforelse
    </tbody>
</table>
</div></div>
@endsection
