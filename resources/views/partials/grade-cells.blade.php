{{-- Celdas derivadas: el cálculo viene del servidor (GradeCalculator), nunca de la vista ni de JavaScript. --}}
@php($result = \App\Models\Grade::resultFor($grade))
@unless ($withoutPartials ?? false)
    <td class="num">{{ $result->partialFormatted(0) }}</td>
    <td class="num">{{ $result->partialFormatted(1) }}</td>
    <td class="num">{{ $result->partialFormatted(2) }}</td>
@endunless
<td class="num fw-semibold">{{ $result->averageFormatted() }}</td>
<td class="num">{{ $result->totalFormatted() }}</td>
<td><x-ui.status-badge :status="$result->status" /></td>
