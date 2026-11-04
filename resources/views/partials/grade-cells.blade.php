{{-- Celdas derivadas: el cálculo viene del servidor (GradeCalculator), nunca de la vista. --}}
@php($result = \App\Models\Grade::resultFor($grade))
@unless ($withoutPartials ?? false)
    <td class="text-end">{{ $result->partialFormatted(0) }}</td>
    <td class="text-end">{{ $result->partialFormatted(1) }}</td>
    <td class="text-end">{{ $result->partialFormatted(2) }}</td>
@endunless
<td class="text-end fw-semibold">{{ $result->averageFormatted() }}</td>
<td class="text-end">{{ $result->totalFormatted() }}</td>
<td><span class="badge {{ $result->status->badgeClass() }}">{{ $result->status->value }}</span></td>
