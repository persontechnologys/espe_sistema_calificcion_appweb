@extends('layouts.app')
@section('title', 'Períodos académicos')
@section('content')
<x-ui.page-header title="Períodos académicos" subtitle="Planificado → Abierto → Cerrado. Un período cerrado no se reabre.">
    <x-slot:actions>
        <a href="{{ route('admin.academic-periods.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nuevo período</a>
    </x-slot:actions>
</x-ui.page-header>

<div class="card">
    <x-ui.filter-bar placeholder="Código o descripción" search-label="Buscar período" />
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr>
                <th>Período</th><th class="d-none d-md-table-cell">Fechas</th><th>Estado</th>
                <th class="num d-none d-sm-table-cell">Ofertas</th><th class="text-end"><span class="visually-hidden">Acciones</span></th>
            </tr></thead>
            <tbody>
            @forelse ($periods as $period)
                <tr>
                    <td><span class="cell-strong">{{ $period->code }}</span><span class="cell-sub">{{ $period->description }}</span></td>
                    <td class="d-none d-md-table-cell text-nowrap tabular">{{ $period->start_date->format('d/m/Y') }} – {{ $period->end_date->format('d/m/Y') }}</td>
                    <td><x-ui.status-badge :status="$period->status" /></td>
                    <td class="num d-none d-sm-table-cell">{{ $period->course_offerings_count }}</td>
                    <td class="text-end">
                        <div class="table-actions">
                            @if ($next = $period->status->next())
                                @php($closing = $next === \App\Enums\PeriodStatus::Closed)
                                <form method="POST" action="{{ route('admin.academic-periods.status', $period) }}" class="d-inline"
                                      data-confirm-title="{{ $closing ? '¿Cerrar el período?' : '¿Abrir el período?' }}"
                                      data-confirm-message="{{ $closing ? 'Los docentes ya no podrán registrar notas ni se admitirán matrículas. Un período cerrado no se puede reabrir; solo el administrador podrá corregir notas con motivo.' : 'Los docentes podrán registrar calificaciones en las ofertas de este período.' }}"
                                      data-confirm-item="{{ $period->code }} - {{ $period->description }}"
                                      data-confirm-button="{{ $closing ? 'Cerrar período' : 'Abrir período' }}"
                                      @if ($closing) data-confirm-variant="danger" @endif>
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $next->value }}">
                                    <button class="btn btn-sm {{ $closing ? 'btn-outline-secondary' : 'btn-outline-primary' }}">
                                        <i class="bi {{ $closing ? 'bi-lock' : 'bi-unlock' }} me-1" aria-hidden="true"></i>{{ $closing ? 'Cerrar' : 'Abrir' }}
                                    </button>
                                </form>
                            @endif
                            <x-ui.icon-link :href="route('admin.academic-periods.edit', $period)" icon="bi-pencil" :label="'Editar período '.$period->code" />
                            <x-ui.delete-button :action="route('admin.academic-periods.destroy', $period)" :item="$period->code.' - '.$period->description" />
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="5" icon="bi-calendar3" title="Sin períodos" message="{{ request()->query() ? 'Ningún período coincide con la búsqueda.' : 'Cree el primer período académico.' }}">
                    @unless (request()->query())
                        <a href="{{ route('admin.academic-periods.create') }}" class="btn btn-sm btn-primary">Nuevo período</a>
                    @endunless
                </x-ui.empty-row>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer-pagination">{{ $periods->links() }}</div>
</div>
@endsection
