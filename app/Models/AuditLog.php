<?php

namespace App\Models;

use App\Enums\AuditOperation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Auditoría de calificaciones. Solo inserciones: la aplicación no edita ni borra.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'enrollment_id', 'operation', 'old_values', 'new_values', 'reason'];

    protected function casts(): array
    {
        return [
            'operation' => AuditOperation::class,
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('La auditoría no se puede modificar.'));
        static::deleting(fn () => throw new LogicException('La auditoría no se puede eliminar.'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }
}
