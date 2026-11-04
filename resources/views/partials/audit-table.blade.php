@php($fmt = fn ($values, $key) => $values === null ? '—' : \App\Services\Grades\GradeCalculator::formatCents(isset($values[$key]) ? \App\Services\Grades\GradeCalculator::toCents($values[$key]) : null))
<div class="table-responsive">
<table class="table table-sm align-middle mb-0">
    <thead class="table-light"><tr>
        <th>Fecha y hora</th><th>Usuario</th><th>Operación</th>
        @if ($showEnrollment ?? true)<th>Estudiante</th><th>NRC</th>@endif
        <th>Anterior (1P / 2P / 3P)</th><th>Nuevo (1P / 2P / 3P)</th><th>Motivo</th>
    </tr></thead>
    <tbody>
    @forelse ($logs as $log)
        <tr>
            <td class="text-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
            <td>{{ $log->user->name }}</td>
            <td><span class="badge {{ $log->operation === \App\Enums\AuditOperation::Correccion ? 'text-bg-warning' : 'text-bg-info' }}">{{ $log->operation->label() }}</span></td>
            @if ($showEnrollment ?? true)
                <td>{{ $log->enrollment->student->institutional_id }}</td>
                <td>{{ $log->enrollment->courseOffering->nrc }}</td>
            @endif
            <td class="text-nowrap">
                @if ($log->old_values === null) <span class="text-muted">(sin registro)</span>
                @else {{ $fmt($log->old_values, 'partial_1') }} / {{ $fmt($log->old_values, 'partial_2') }} / {{ $fmt($log->old_values, 'partial_3') }}
                @endif
            </td>
            <td class="text-nowrap">{{ $fmt($log->new_values, 'partial_1') }} / {{ $fmt($log->new_values, 'partial_2') }} / {{ $fmt($log->new_values, 'partial_3') }}</td>
            <td>{{ $log->reason ?? '—' }}</td>
        </tr>
    @empty
        <tr><td colspan="8" class="text-center text-muted py-3">Sin cambios registrados.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
