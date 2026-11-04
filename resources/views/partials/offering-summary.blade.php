<dl class="row mb-0 small">
    <dt class="col-sm-3 col-lg-2">Período</dt><dd class="col-sm-9 col-lg-4">{{ $offering->academicPeriod->label }}
        <span class="badge {{ $offering->academicPeriod->status->badgeClass() }}">{{ $offering->academicPeriod->status->label() }}</span></dd>
    <dt class="col-sm-3 col-lg-2">Asignatura</dt><dd class="col-sm-9 col-lg-4">{{ $offering->subject->code }} · {{ $offering->subject->name }}</dd>
    <dt class="col-sm-3 col-lg-2">NRC</dt><dd class="col-sm-9 col-lg-4">{{ $offering->nrc }}</dd>
    <dt class="col-sm-3 col-lg-2">Docente</dt><dd class="col-sm-9 col-lg-4">{{ $offering->teacher->name }} ({{ $offering->teacher->institutional_id }})</dd>
    <dt class="col-sm-3 col-lg-2">Aula / Campus</dt><dd class="col-sm-9 col-lg-4">{{ $offering->classroom ?? '—' }} / {{ $offering->campus ?? '—' }}</dd>
</dl>
