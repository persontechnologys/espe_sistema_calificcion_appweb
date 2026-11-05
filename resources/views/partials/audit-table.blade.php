@php($fmt = fn ($values, $key) => $values === null ? '—' : \App\Services\Grades\GradeCalculator::formatCents(isset($values[$key]) ? \App\Services\Grades\GradeCalculator::toCents($values[$key]) : null))
<div class="table-responsive">
<table class="table table-sm table-hover align-middle">
    <thead><tr>
        <th>Fecha y hora</th><th>Usuario</th><th>Operación</th>
        @if ($showEnrollment ?? true)<th>Estudiante</th><th>NRC</th>@endif
        <th class="num">Anterior (1P / 2P / 3P)</th><th class="num">Nuevo (1P / 2P / 3P)</th><th>Motivo</th>
    </tr></thead>
    <tbody>
    @forelse ($logs as $log)
        <tr>
            <td class="text-nowrap tabular">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
            <td>{{ $log->user->name }}</td>
            <td><x-ui.status-badge :status="$log->operation" /></td>
            @if ($showEnrollment ?? true)
                <td class="text-nowrap">{{ $log->enrollment->student->institutional_id }}</td>
                <td class="tabular">{{ $log->enrollment->courseOffering->nrc }}</td>
            @endif
            <td class="num text-nowrap">
                @if ($log->old_values === null) <span class="text-muted">(sin registro)</span>
                @else {{ $fmt($log->old_values, 'partial_1') }} / {{ $fmt($log->old_values, 'partial_2') }} / {{ $fmt($log->old_values, 'partial_3') }}
                @endif
            </td>
            <td class="num text-nowrap fw-semibold">{{ $fmt($log->new_values, 'partial_1') }} / {{ $fmt($log->new_values, 'partial_2') }} / {{ $fmt($log->new_values, 'partial_3') }}</td>
            <td>{{ $log->reason ?? '—' }}</td>
        </tr>
    @empty
        <x-ui.empty-row :colspan="($showEnrollment ?? true) ? 8 : 6" icon="bi-clock-history" title="Sin cambios registrados" />
    @endforelse
    </tbody>
</table>
</div>
