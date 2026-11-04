<?php

namespace App\Models;

use App\Support\Normalize;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['institutional_id', 'first_names', 'last_names', 'email', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected function institutionalId(): Attribute
    {
        return Attribute::make(set: fn ($value) => Normalize::code($value));
    }

    protected function email(): Attribute
    {
        return Attribute::make(set: fn ($value) => Normalize::email($value));
    }

    /** "APELLIDOS, NOMBRES" como en el reporte de referencia. */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn () => mb_strtoupper("{$this->last_names} {$this->first_names}"));
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
