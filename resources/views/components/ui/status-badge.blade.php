{{-- Estado con texto e ícono (nunca solo color). Acepta un enum de estado o un booleano "activo". --}}
@props(['status' => null, 'active' => null])
@php
    [$variant, $icon, $text] = match (true) {
        $status instanceof \App\Enums\GradeStatus => match ($status) {
            \App\Enums\GradeStatus::Aprobado => ['success', 'bi-check-circle', $status->value],
            \App\Enums\GradeStatus::Reprobado => ['danger', 'bi-x-circle', $status->value],
            \App\Enums\GradeStatus::Incompleto => ['warning', 'bi-hourglass-split', $status->value],
        },
        $status instanceof \App\Enums\PeriodStatus => match ($status) {
            \App\Enums\PeriodStatus::Planned => ['neutral', 'bi-calendar', $status->label()],
            \App\Enums\PeriodStatus::Open => ['success', 'bi-unlock', $status->label()],
            \App\Enums\PeriodStatus::Closed => ['dark', 'bi-lock', $status->label()],
        },
        $status instanceof \App\Enums\AuditOperation => match ($status) {
            \App\Enums\AuditOperation::Registro => ['info', 'bi-pencil', $status->label()],
            \App\Enums\AuditOperation::Correccion => ['warning', 'bi-pencil-square', $status->label()],
        },
        default => $active ? ['success', 'bi-check2', 'Activo'] : ['neutral', 'bi-slash-circle', 'Inactivo'],
    };
@endphp
<span {{ $attributes->class(['badge badge-status', 'badge-'.$variant]) }}><i class="bi {{ $icon }}" aria-hidden="true"></i>{{ $text }}</span>
