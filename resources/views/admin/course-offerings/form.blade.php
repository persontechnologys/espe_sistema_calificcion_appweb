@extends('layouts.app')
@section('title', $offering->exists ? 'Editar oferta' : 'Nueva oferta')
@section('content')
<x-ui.page-header :title="$offering->exists ? 'Editar oferta NRC '.$offering->nrc : 'Nueva oferta académica'" />
<div class="card form-wide"><div class="card-body p-4">
<form method="POST" action="{{ $offering->exists ? route('admin.course-offerings.update', $offering) : route('admin.course-offerings.store') }}" novalidate data-validate>
    @csrf
    @if ($offering->exists) @method('PUT') @endif
    <x-form.section title="Oferta" icon="bi-collection">
        <div class="row">
            <x-form.select col="col-md-6" name="academic_period_id" label="Período académico" :options="$periods" :value="$offering->academic_period_id" required
                           :text="fn ($p) => $p->label.' ('.$p->status->label().')'" />
            <x-form.select col="col-md-6" name="subject_id" label="Asignatura" :options="$subjects" :value="$offering->subject_id" required
                           :text="fn ($s) => $s->code.' · '.$s->name" />
        </div>
        <div class="row">
            <x-form.input col="col-sm-4" name="nrc" label="NRC" :value="$offering->nrc" required maxlength="20" help="Único dentro del período." />
            <x-form.input col="col-sm-4" name="classroom" label="Aula (opcional)" :value="$offering->classroom" maxlength="50" />
            <x-form.input col="col-sm-4" name="campus" label="Campus (opcional)" :value="$offering->campus" maxlength="100" />
        </div>
    </x-form.section>
    <x-form.section title="Docente responsable" icon="bi-person-badge">
        <x-form.select name="teacher_id" label="Docente" :options="$teachers" :value="$offering->teacher_id" required
                       :text="fn ($t) => $t->name.' ('.$t->institutional_id.')'"
                       :help="$offering->exists ? 'Si cambia el docente, el anterior pierde el acceso a esta oferta de inmediato. La auditoría conserva quién registró cada nota.' : null" />
    </x-form.section>
    <p class="required-note"><span class="text-danger">*</span> Campo obligatorio.</p>
    <x-form.actions :cancel="$offering->exists ? route('admin.course-offerings.show', $offering) : route('admin.course-offerings.index')" />
</form>
</div></div>
@endsection
