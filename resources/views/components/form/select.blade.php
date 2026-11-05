{{-- Lista de selección. $options: iterable de modelos; $text: closure que produce la etiqueta. --}}
@props(['name', 'label', 'options', 'text', 'value' => null, 'required' => false, 'placeholder' => 'Seleccione…', 'help' => null, 'col' => null])
<div @class(['mb-3', $col])>
    <label for="{{ $name }}" class="form-label">{{ $label }}@if ($required)<span class="req" aria-hidden="true">*</span>@endif</label>
    <select id="{{ $name }}" name="{{ $name }}" @if ($required) required aria-required="true" @endif
            @if ($help) aria-describedby="{{ $name }}-help" @endif
            {{ $attributes->class(['form-select', 'is-invalid' => $errors->has($name)]) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $opt)
            <option value="{{ $opt->id }}" @selected((string) old($name, $value) === (string) $opt->id)>{{ $text($opt) }}</option>
        @endforeach
    </select>
    @if ($help)<div id="{{ $name }}-help" class="form-text">{{ $help }}</div>@endif
    <x-form.error :name="$name" />
</div>
