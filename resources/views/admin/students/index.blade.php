@extends('layouts.app')
@section('title', 'Estudiantes')
@section('content')
@include('partials.page-header', ['title' => 'Estudiantes', 'createRoute' => route('admin.students.create'), 'createLabel' => 'Nuevo estudiante'])
@include('partials.search', ['placeholder' => 'ID, nombres o apellidos'])
<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>ID</th><th>Apellidos y nombres</th><th>Correo</th><th class="text-end">Matrículas</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @forelse ($students as $student)
        <tr>
            <td><strong>{{ $student->institutional_id }}</strong></td>
            <td>{{ $student->full_name }}</td>
            <td>{{ $student->email ?? '—' }}</td>
            <td class="text-end">{{ $student->enrollments_count }}</td>
            <td>@include('partials.active-badge', ['active' => $student->is_active])</td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.students.show', $student) }}">Calificaciones</a>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.students.edit', $student) }}">Editar</a>
                @include('partials.delete-button', ['action' => route('admin.students.destroy', $student)])
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Sin estudiantes.</td></tr>
    @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-3">{{ $students->links() }}</div>
@endsection
