<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditOperation;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Consulta de la auditoría de calificaciones (solo lectura).
 */
class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'operation' => ['nullable', Rule::enum(AuditOperation::class)],
            'student' => ['nullable', 'string', 'max:100'],
            'nrc' => ['nullable', 'string', 'max:20'],
        ]);

        $logs = AuditLog::query()
            ->with(['user', 'enrollment.student', 'enrollment.courseOffering'])
            ->when($filters['operation'] ?? null, fn ($q, $op) => $q->where('operation', $op))
            ->when($filters['student'] ?? null, fn ($q, $term) => $q->whereHas(
                'enrollment.student', fn (Builder $s) => $s->search($term, ['institutional_id', 'first_names', 'last_names'])
            ))
            ->when($filters['nrc'] ?? null, fn ($q, $term) => $q->whereHas(
                'enrollment.courseOffering', fn (Builder $o) => $o->search($term, ['nrc'])
            ))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit-logs.index', compact('logs'));
    }
}
