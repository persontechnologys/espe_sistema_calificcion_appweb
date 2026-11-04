<?php

namespace App\Models;

use App\Models\Concerns\SearchesText;
use App\Support\Normalize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Oferta académica (NRC) = asignatura + período + docente.
 */
class CourseOffering extends Model
{
    use HasFactory, SearchesText;

    protected $fillable = ['academic_period_id', 'subject_id', 'teacher_id', 'nrc', 'classroom', 'campus'];

    protected function nrc(): Attribute
    {
        return Attribute::make(set: fn ($value) => Normalize::code($value));
    }

    protected function classroom(): Attribute
    {
        return Attribute::make(set: fn ($value) => Normalize::text($value));
    }

    protected function campus(): Attribute
    {
        return Attribute::make(set: fn ($value) => Normalize::text($value));
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Matrículas con estudiante y notas, ordenadas por apellidos y nombres (carga anticipada: sin N+1).
     *
     * @return Collection<int, Enrollment>
     */
    public function enrollmentsWithGrades(): Collection
    {
        return $this->enrollments()
            ->with(['student', 'grade'])
            ->join('students', 'students.id', '=', 'enrollments.student_id')
            ->orderBy('students.last_names')->orderBy('students.first_names')
            ->select('enrollments.*')
            ->get();
    }

    /** Ofertas visibles para el usuario: todas para admin, solo las propias para docente. */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if (! $user->isAdmin()) {
            $query->where('teacher_id', $user->id);
        }
    }
}
