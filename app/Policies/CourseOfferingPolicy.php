<?php

namespace App\Policies;

use App\Models\CourseOffering;
use App\Models\User;

/**
 * Acceso a una oferta concreta. La pertenencia se evalúa con el docente ACTUAL:
 * si la oferta se reasigna, el docente anterior pierde el acceso de inmediato.
 */
class CourseOfferingPolicy
{
    /** Ver la oferta, sus matriculados y su reporte. */
    public function view(User $user, CourseOffering $offering): bool
    {
        return $user->isAdmin() || $this->isCurrentTeacher($user, $offering);
    }

    /** Abrir la planilla de registro de notas (solo el docente de la oferta). */
    public function recordGrades(User $user, CourseOffering $offering): bool
    {
        return $this->isCurrentTeacher($user, $offering);
    }

    private function isCurrentTeacher(User $user, CourseOffering $offering): bool
    {
        return $user->isTeacher() && (int) $offering->teacher_id === (int) $user->id;
    }
}
