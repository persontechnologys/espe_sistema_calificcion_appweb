<dl class="row summary-dl mb-0">
    <div class="col-sm-6 col-lg-4"><dt>Período</dt><dd>{{ $offering->academicPeriod->label }} <x-ui.status-badge :status="$offering->academicPeriod->status" /></dd></div>
    <div class="col-sm-6 col-lg-4"><dt>Asignatura</dt><dd>{{ $offering->subject->code }} · {{ $offering->subject->name }}</dd></div>
    <div class="col-sm-6 col-lg-4"><dt>NRC</dt><dd class="tabular">{{ $offering->nrc }}</dd></div>
    <div class="col-sm-6 col-lg-4"><dt>Docente</dt><dd>{{ $offering->teacher->name }} ({{ $offering->teacher->institutional_id }})</dd></div>
    <div class="col-sm-6 col-lg-4"><dt>Aula / Campus</dt><dd>{{ $offering->classroom ?? '—' }} / {{ $offering->campus ?? '—' }}</dd></div>
</dl>
