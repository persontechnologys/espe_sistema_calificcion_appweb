<?php

namespace App\Models;

use App\Enums\PeriodStatus;
use App\Support\Normalize;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicPeriod extends Model
{
    use HasFactory;

    // status no es asignable masivamente: solo cambia mediante la acción de transición.
    protected $fillable = ['code', 'description', 'start_date', 'end_date'];

    protected $attributes = ['status' => 'planned'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => PeriodStatus::class,
        ];
    }

    protected function code(): Attribute
    {
        return Attribute::make(set: fn ($value) => Normalize::code($value));
    }

    /** "202451 - PREGRADO S-II OCT 24 - MAR 25" (se compone al mostrar). */
    protected function label(): Attribute
    {
        return Attribute::get(fn () => "{$this->code} - {$this->description}");
    }

    public function courseOfferings(): HasMany
    {
        return $this->hasMany(CourseOffering::class);
    }
}
