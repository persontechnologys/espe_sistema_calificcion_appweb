{{--
    Notificaciones a partir de los mensajes flash de Laravel:
    status → éxito (se cierra a los 5 s) · general → error · validación → "Revise los campos marcados".
    Los errores no se cierran solos.
--}}
@php
    $bags = collect($errors->getBags());
    $general = $bags->map(fn ($bag) => $bag->first('general'))->filter()->first();
    $hasFieldErrors = $bags->contains(fn ($bag) => collect($bag->keys())->diff(['general'])->isNotEmpty());
    $toasts = array_values(array_filter([
        session('status') ? ['success', session('status')] : null,
        $general ? ['danger', $general] : null,
        $hasFieldErrors ? ['danger', 'Revise los campos marcados.'] : null,
    ]));
@endphp
<div class="toast-stack no-print">
    @foreach ($toasts as [$variant, $message])
        <div class="toast toast-{{ $variant }}" role="{{ $variant === 'danger' ? 'alert' : 'status' }}"
             aria-live="{{ $variant === 'danger' ? 'assertive' : 'polite' }}" aria-atomic="true"
             data-bs-autohide="{{ $variant === 'danger' ? 'false' : 'true' }}" data-bs-delay="5000" data-toast="{{ $variant }}">
            <div class="toast-body">
                <i class="bi toast-icon {{ $variant === 'danger' ? 'bi-exclamation-octagon-fill' : 'bi-check-circle-fill' }}" aria-hidden="true"></i>
                <div class="flex-grow-1">{{ $message }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    @endforeach
</div>
