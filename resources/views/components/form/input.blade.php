{{--
    Campo de formulario con label asociado, "*" si es obligatorio, ayuda y error del servidor.
    Uso: <x-form.input name="code" label="Código" :value="$model->code" required maxlength="20" />
    Atributos extra (maxlength, placeholder, pattern, inputmode, autocomplete…) pasan al <input>.
--}}
@props(['name', 'label', 'value' => null, 'type' => 'text', 'required' => false, 'help' => null, 'col' => null])
<div @class(['mb-3', $col])>
    <label for="{{ $name }}" class="form-label">{{ $label }}@if ($required)<span class="req" aria-hidden="true">*</span>@endif</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           value="{{ $type === 'password' ? '' : old($name, $value ?? '') }}"
           @if ($required) required aria-required="true" @endif
           @if ($help) aria-describedby="{{ $name }}-help" @endif
           @if ($type === 'password' && ! $attributes->has('autocomplete')) autocomplete="new-password" @endif
           {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
    @if ($help)<div id="{{ $name }}-help" class="form-text">{{ $help }}</div>@endif
    <x-form.error :name="$name" />
</div>
