<?php

namespace App\Policies;

use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    /** El docente registra notas solo en matrículas de sus ofertas. */
    public function recordGrade(User $user, Enrollment $enrollment): bool
    {
        if (! $user->isTeacher()) {
            return false;
        }

        // Se consulta el docente actual de la oferta (no se confía en relaciones ya cargadas).
        $teacherId = CourseOffering::whereKey($enrollment->course_offering_id)->value('teacher_id');

        return (int) $teacherId === (int) $user->id;
    }

    /** Corrección de notas: solo administrador. */
    public function correctGrade(User $user, Enrollment $enrollment): bool
    {
        return $user->isAdmin();
    }
}
