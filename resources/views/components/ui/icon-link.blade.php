{{-- Acción de tabla como botón de ícono, con nombre accesible y tooltip. --}}
@props(['href', 'icon', 'label', 'variant' => 'outline-secondary'])
<a href="{{ $href }}" {{ $attributes->class(['btn btn-sm btn-icon', 'btn-'.$variant]) }}
   aria-label="{{ $label }}" data-bs-toggle="tooltip" data-bs-title="{{ $label }}"><i class="bi {{ $icon }}" aria-hidden="true"></i></a>
