@props(['title', 'icon' => null])
<fieldset {{ $attributes->class(['form-section']) }}>
    <legend>@if ($icon)<i class="bi {{ $icon }}" aria-hidden="true"></i>@endif{{ $title }}</legend>
    {{ $slot }}
</fieldset>
