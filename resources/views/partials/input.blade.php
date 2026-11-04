{{-- Uso: @include('partials.input', ['name' => 'code', 'label' => 'Código', 'value' => $model->code, 'required' => true]) --}}
@php($type = $type ?? 'text')
<div class="mb-3 {{ $class ?? '' }}">
    <label for="{{ $name }}" class="form-label">{{ $label }} @if($required ?? false)<span class="text-danger">*</span>@endif</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           value="{{ $type === 'password' ? '' : old($name, $value ?? '') }}"
           class="form-control @error($name) is-invalid @enderror"
           @if($required ?? false) required @endif
           @isset($maxlength) maxlength="{{ $maxlength }}" @endisset
           @isset($placeholder) placeholder="{{ $placeholder }}" @endisset
           @if($type === 'password') autocomplete="new-password" @endif>
    @isset($help)<div class="form-text">{{ $help }}</div>@endisset
    @include('partials.field-error', ['name' => $name])
</div>
