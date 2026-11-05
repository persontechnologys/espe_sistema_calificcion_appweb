@extends('layouts.app')
@section('title', $student->exists ? 'Editar estudiante' : 'Nuevo estudiante')
@section('content')
<x-ui.page-header :title="$student->exists ? 'Editar estudiante' : 'Nuevo estudiante'" :subtitle="$student->exists ? $student->institutional_id.' · '.$student->full_name : null" />
<div class="card form-narrow"><div class="card-body p-4">
<form method="POST" action="{{ $student->exists ? route('admin.students.update', $student) : route('admin.students.store') }}" novalidate data-validate>
    @csrf
    @if ($student->exists) @method('PUT') @endif
    <x-form.section title="Identificación institucional" icon="bi-person-vcard">
        <x-form.input name="institutional_id" label="ID institucional" :value="$student->institutional_id" required maxlength="20" placeholder="L00000000"
                      help="Texto: conserva letras y ceros iniciales. Se guarda en mayúsculas." />
    </x-form.section>
    <x-form.section title="Datos personales" icon="bi-person">
        <div class="row">
            <x-form.input col="col-sm-6" name="last_names" label="Apellidos" :value="$student->last_names" required maxlength="100" />
            <x-form.input col="col-sm-6" name="first_names" label="Nombres" :value="$student->first_names" required maxlength="100" />
        </div>
        <x-form.input name="email" label="Correo (opcional)" type="email" :value="$student->email" maxlength="255" autocomplete="off" />
        <x-form.active-switch :value="$student->is_active" />
    </x-form.section>
    <p class="required-note"><span class="text-danger">*</span> Campo obligatorio.</p>
    <x-form.actions :cancel="route('admin.students.index')" />
</form>
</div></div>
@endsection
