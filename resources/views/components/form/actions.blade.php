@props(['cancel', 'label' => 'Guardar'])
<div class="form-actions">
    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1" aria-hidden="true"></i>{{ $label }}</button>
    <a href="{{ $cancel }}" class="btn btn-outline-secondary">Cancelar</a>
    {{ $slot }}
</div>
