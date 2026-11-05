@extends('layouts.app')
@section('title', 'Calificaciones del estudiante')
@section('content')
<x-ui.page-header :title="$student->full_name">
    <x-slot:meta>
        <p class="page-subtitle d-flex flex-wrap align-items-center gap-2">
            <span>ID institucional: <strong>{{ $student->institutional_id }}</strong></span>
            @if ($student->email)<span>· {{ $student->email }}</span>@endif
            <x-ui.status-badge :active="$student->is_active" />
        </p>
    </x-slot:meta>
    <x-slot:actions>
        <a class="btn btn-outline-primary" href="{{ route('admin.students.edit', $student) }}"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Editar</a>
    </x-slot:actions>
</x-ui.page-header>

<div class="card">
    <div class="card-header"><h2 class="card-title">Matrículas y calificaciones</h2></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr>
                <th>Período</th><th>NRC</th><th>Asignatura</th><th class="d-none d-lg-table-cell">Docente</th>
                <th class="num">1P</th><th class="num">2P</th><th class="num">3P</th><th class="num">Promedio /20</th><th class="num">Total /60</th><th>Estado</th>
            </tr></thead>
            <tbody>
            @forelse ($student->enrollments as $enrollment)
                @php($o = $enrollment->courseOffering)
                <tr>
                    <td class="tabular">{{ $o->academicPeriod->code }}</td>
                    <td><a href="{{ route('admin.course-offerings.show', $o) }}" class="cell-strong">{{ $o->nrc }}</a></td>
                    <td>{{ $o->subject->name }}<span class="cell-sub">{{ $o->subject->code }}</span></td>
                    <td class="d-none d-lg-table-cell">{{ $o->teacher->name }}</td>
                    @include('partials.grade-cells', ['grade' => $enrollment->grade])
                </tr>
            @empty
                <x-ui.empty-row colspan="10" icon="bi-card-checklist" title="Sin matrículas" message="El estudiante aún no está matriculado en ninguna oferta." />
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
