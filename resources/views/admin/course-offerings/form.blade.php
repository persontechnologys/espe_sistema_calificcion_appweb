@extends('layouts.app')
@section('title', $offering->exists ? 'Editar oferta' : 'Nueva oferta')
@section('content')
<h1 class="h3 mb-3">{{ $offering->exists ? 'Editar oferta NRC '.$offering->nrc : 'Nueva oferta académica' }}</h1>
<div class="card shadow-sm" style="max-width: 48rem"><div class="card-body">
<form method="POST" action="{{ $offering->exists ? route('admin.course-offerings.update', $offering) : route('admin.course-offerings.store') }}">
    @csrf
    @if ($offering->exists) @method('PUT') @endif
    @foreach ([
        ['academic_period_id', 'Período académico', $periods, fn ($p) => $p->label.' ('.$p->status->label().')'],
        ['subject_id', 'Asignatura', $subjects, fn ($s) => $s->code.' · '.$s->name],
        ['teacher_id', 'Docente responsable', $teachers, fn ($t) => $t->name.' ('.$t->institutional_id.')'],
    ] as [$field, $label, $options, $text])
        <div class="mb-3">
            <label for="{{ $field }}" class="form-label">{{ $label }} <span class="text-danger">*</span></label>
            <select id="{{ $field }}" name="{{ $field }}" class="form-select @error($field) is-invalid @enderror" required>
                <option value="">Seleccione…</option>
                @foreach ($options as $opt)
                    <option value="{{ $opt->id }}" @selected((string) old($field, $offering->{$field}) === (string) $opt->id)>{{ $text($opt) }}</option>
                @endforeach
            </select>
            @include('partials.field-error', ['name' => $field])
        </div>
    @endforeach
    <div class="row">
        @include('partials.input', ['class' => 'col-sm-4', 'name' => 'nrc', 'label' => 'NRC', 'value' => $offering->nrc, 'required' => true, 'maxlength' => 20, 'help' => 'Único dentro del período.'])
        @include('partials.input', ['class' => 'col-sm-4', 'name' => 'classroom', 'label' => 'Aula (opcional)', 'value' => $offering->classroom, 'maxlength' => 50])
        @include('partials.input', ['class' => 'col-sm-4', 'name' => 'campus', 'label' => 'Campus (opcional)', 'value' => $offering->campus, 'maxlength' => 100])
    </div>
    @if ($offering->exists)
        <p class="text-muted small">Si cambia el docente, el anterior pierde el acceso a esta oferta de inmediato. La auditoría conserva quién registró cada nota.</p>
    @endif
    @include('partials.form-actions', ['cancel' => $offering->exists ? route('admin.course-offerings.show', $offering) : route('admin.course-offerings.index')])
</form>
</div></div>
@endsection
