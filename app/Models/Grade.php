<?php

namespace App\Models;

use App\Services\Grades\GradeCalculator;
use App\Services\Grades\GradeResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tres parciales de una matrícula. Solo se escribe a través de GradeService,
 * que valida, bloquea y audita; los controladores no lo modifican directamente.
 */
class Grade extends Model
{
    use HasFactory;

    protected $fillable = ['enrollment_id', 'partial_1', 'partial_2', 'partial_3', 'version'];

    protected function casts(): array
    {
        // decimal:2 devuelve cadenas ("10.10"): nunca float.
        return [
            'partial_1' => 'decimal:2',
            'partial_2' => 'decimal:2',
            'partial_3' => 'decimal:2',
            'version' => 'integer',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /** @return array{0: ?string, 1: ?string, 2: ?string} */
    public function partials(): array
    {
        return [$this->partial_1, $this->partial_2, $this->partial_3];
    }

    public function result(): GradeResult
    {
        return app(GradeCalculator::class)->calculate($this->partials());
    }

    /** Resultado de una matrícula sin registro de notas: INCOMPLETO. */
    public static function resultFor(?self $grade): GradeResult
    {
        return $grade?->result() ?? app(GradeCalculator::class)->calculate([null, null, null]);
    }
}
