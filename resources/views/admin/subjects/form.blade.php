@extends('layouts.app')
@section('title', $subject->exists ? 'Editar asignatura' : 'Nueva asignatura')
@section('content')
<x-ui.page-header :title="$subject->exists ? 'Editar asignatura '.$subject->code : 'Nueva asignatura'" />
<div class="card form-narrow"><div class="card-body p-4">
<form method="POST" action="{{ $subject->exists ? route('admin.subjects.update', $subject) : route('admin.subjects.store') }}" novalidate data-validate>
    @csrf
    @if ($subject->exists) @method('PUT') @endif
    <x-form.section title="Datos de la asignatura" icon="bi-journal-bookmark">
        <div class="row">
            <x-form.input col="col-sm-5" name="code" label="Código" :value="$subject->code" required maxlength="20" placeholder="COMP-A0G11" help="Único. Se guarda en mayúsculas." />
            <x-form.input col="col-sm-7" name="name" label="Nombre" :value="$subject->name" required maxlength="150" />
        </div>
        <x-form.active-switch :value="$subject->is_active" />
    </x-form.section>
    <p class="required-note"><span class="text-danger">*</span> Campo obligatorio.</p>
    <x-form.actions :cancel="route('admin.subjects.index')" />
</form>
</div></div>
@endsection
