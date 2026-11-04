<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Concerns\SearchesText;
use App\Support\Normalize;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Usuario con acceso: administrador o docente (role = teacher).
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SearchesText;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'institutional_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    protected function email(): Attribute
    {
        return Attribute::make(set: fn ($value) => Normalize::email($value));
    }

    protected function institutionalId(): Attribute
    {
        return Attribute::make(set: fn ($value) => Normalize::code($value));
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isTeacher(): bool
    {
        return $this->role === UserRole::Teacher;
    }

    /** Ofertas de las que el usuario es el docente actual. */
    public function courseOfferings(): HasMany
    {
        return $this->hasMany(CourseOffering::class, 'teacher_id');
    }

    public function scopeTeachers(Builder $query): void
    {
        $query->where('role', UserRole::Teacher->value);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
