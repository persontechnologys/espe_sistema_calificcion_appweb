@extends('layouts.app')
@section('title', 'Períodos académicos')
@section('content')
@include('partials.page-header', ['title' => 'Períodos académicos', 'createRoute' => route('admin.academic-periods.create'), 'createLabel' => 'Nuevo período'])
@include('partials.search', ['placeholder' => 'Código o descripción'])
<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>Período</th><th>Fechas</th><th>Estado</th><th class="text-end">Ofertas</th><th></th></tr></thead>
    <tbody>
    @forelse ($periods as $period)
        <tr>
            <td><strong>{{ $period->code }}</strong> - {{ $period->description }}</td>
            <td class="text-nowrap">{{ $period->start_date->format('d/m/Y') }} – {{ $period->end_date->format('d/m/Y') }}</td>
            <td><span class="badge {{ $period->status->badgeClass() }}">{{ $period->status->label() }}</span></td>
            <td class="text-end">{{ $period->course_offerings_count }}</td>
            <td class="text-end text-nowrap">
                @if ($next = $period->status->next())
                    <form method="POST" action="{{ route('admin.academic-periods.status', $period) }}" class="d-inline"
                          data-confirm="¿Cambiar el período {{ $period->code }} a «{{ $next->label() }}»?{{ $next === \App\Enums\PeriodStatus::Closed ? ' Un período cerrado no se puede reabrir.' : '' }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $next->value }}">
                        <button class="btn btn-sm {{ $next === \App\Enums\PeriodStatus::Closed ? 'btn-outline-dark' : 'btn-outline-success' }}">
                            {{ $next === \App\Enums\PeriodStatus::Open ? 'Abrir' : 'Cerrar' }}
                        </button>
                    </form>
                @endif
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.academic-periods.edit', $period) }}">Editar</a>
                @include('partials.delete-button', ['action' => route('admin.academic-periods.destroy', $period)])
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted py-4">Sin períodos.</td></tr>
    @endforelse
    </tbody>
</table>
</div></div>
<div class="mt-3">{{ $periods->links() }}</div>
@endsection
