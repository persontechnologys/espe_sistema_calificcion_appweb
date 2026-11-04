@extends('layouts.app')
@section('title', 'Auditoría')
@section('content')
<h1 class="h3 mb-3">Auditoría de calificaciones</h1>
<form method="GET" class="row g-2 align-items-end mb-3">
    <div class="col-md-2">
        <label class="form-label small" for="operation">Operación</label>
        <select id="operation" name="operation" class="form-select">
            <option value="">Todas</option>
            @foreach (\App\Enums\AuditOperation::cases() as $op)
                <option value="{{ $op->value }}" @selected(request('operation') === $op->value)>{{ $op->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small" for="student">Estudiante (ID o nombre)</label>
        <input id="student" name="student" value="{{ request('student') }}" class="form-control">
    </div>
    <div class="col-md-2">
        <label class="form-label small" for="nrc">NRC</label>
        <input id="nrc" name="nrc" value="{{ request('nrc') }}" class="form-control">
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-success" type="submit">Filtrar</button>
        @if (request()->query())<a class="btn btn-link" href="{{ route('admin.audit-logs.index') }}">Limpiar</a>@endif
    </div>
</form>
<div class="card shadow-sm"><div class="card-body">
    @include('partials.audit-table', ['logs' => $logs])
</div></div>
<div class="mt-3">{{ $logs->links() }}</div>
@endsection
