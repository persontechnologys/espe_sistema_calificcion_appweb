<?php

namespace App\Services\Grades;

use App\Enums\AuditOperation;
use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Único punto de escritura de calificaciones (especificación §6.1, §7, §12).
 *
 * En una sola transacción:
 *  1. Bloqueo compartido de la oferta y del período: nadie puede reasignar la oferta ni
 *     cerrar el período hasta que termine el guardado (y viceversa).
 *  2. Reglas: docente actual de la oferta + período abierto (registro), o administrador
 *     + período abierto/cerrado + motivo (corrección).
 *  3. lockForUpdate() de la fila de notas y comparación de versión (bloqueo optimista).
 *  4. Guardado de parciales y registro de auditoría. Si la auditoría falla, no se guarda la nota.
 *
 * Total, promedio y estado nunca se reciben del cliente: se derivan con GradeCalculator.
 */
class GradeService
{
    public function __construct(private readonly GradeCalculator $calculator) {}

    /**
     * Registro ordinario por el docente de la oferta.
     *
     * @param  array{0: ?string, 1: ?string, 2: ?string}  $partials
     */
    public function register(User $teacher, Enrollment $enrollment, array $partials, int $expectedVersion): Grade
    {
        return $this->save($teacher, $enrollment, $partials, $expectedVersion, AuditOperation::Registro, null);
    }

    /**
     * Corrección del administrador, con motivo obligatorio. Único modo de cambiar notas de un período cerrado.
     *
     * @param  array{0: ?string, 1: ?string, 2: ?string}  $partials
     */
    public function correct(User $admin, Enrollment $enrollment, array $partials, int $expectedVersion, string $reason): Grade
    {
        return $this->save($admin, $enrollment, $partials, $expectedVersion, AuditOperation::Correccion, $reason);
    }

    private function save(
        User $actor,
        Enrollment $enrollment,
        array $partials,
        int $expectedVersion,
        AuditOperation $operation,
        ?string $reason,
    ): Grade {
        $newValues = $this->normalizePartials($partials);
        $reason = $reason === null ? null : trim($reason);

        try {
            return DB::transaction(function () use ($actor, $enrollment, $newValues, $expectedVersion, $operation, $reason) {
                $offering = CourseOffering::whereKey($enrollment->course_offering_id)->sharedLock()->firstOrFail();
                $period = AcademicPeriod::whereKey($offering->academic_period_id)->sharedLock()->firstOrFail();

                $this->assertAllowed($actor, $offering, $period, $operation, $reason);

                $grade = Grade::where('enrollment_id', $enrollment->id)->lockForUpdate()->first();
                $currentVersion = $grade?->version ?? 0;

                if ($currentVersion !== $expectedVersion) {
                    throw new GradeConflictException;
                }

                $oldValues = $grade ? $this->valuesOf($grade) : null;

                if ($oldValues === $newValues) {
                    return $grade; // Sin cambios: no se incrementa la versión ni se audita.
                }

                $grade ??= new Grade(['enrollment_id' => $enrollment->id]);
                $grade->fill($newValues);
                $grade->version = $currentVersion + 1;
                $grade->save();

                AuditLog::create([
                    'user_id' => $actor->id,
                    'enrollment_id' => $enrollment->id,
                    'operation' => $operation,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                    'reason' => $reason,
                ]);

                return $grade;
            });
        } catch (UniqueConstraintViolationException) {
            // Dos solicitudes crearon a la vez el primer registro de la matrícula:
            // UNIQUE(enrollment_id) rechazó la segunda. Se trata como conflicto.
            throw new GradeConflictException;
        }
    }

    private function assertAllowed(User $actor, CourseOffering $offering, AcademicPeriod $period, AuditOperation $operation, ?string $reason): void
    {
        if ($operation === AuditOperation::Registro) {
            // Pertenencia evaluada con el docente ACTUAL de la oferta (leída con bloqueo).
            if (! $actor->isTeacher() || (int) $offering->teacher_id !== (int) $actor->id) {
                throw new AuthorizationException('Solo el docente asignado a la oferta puede registrar sus notas.');
            }
            if (! $period->status->allowsTeacherGrading()) {
                throw new GradeNotAllowedException("El período {$period->code} está {$period->status->label()}: el docente no puede registrar notas.");
            }

            return;
        }

        if (! $actor->isAdmin()) {
            throw new AuthorizationException('Solo el administrador puede corregir calificaciones.');
        }
        if (! $period->status->allowsAdminCorrection()) {
            throw new GradeNotAllowedException("El período {$period->code} está {$period->status->label()}: no admite correcciones.");
        }
        if ($reason === null || $reason === '') {
            throw new GradeNotAllowedException('La corrección requiere un motivo.');
        }
    }

    /**
     * Valida con el calculador (0–20, máx. 2 decimales) y devuelve el formato canónico "10.10" o NULL.
     *
     * @return array{partial_1: ?string, partial_2: ?string, partial_3: ?string}
     */
    private function normalizePartials(array $partials): array
    {
        $partials = array_values($partials);

        if (count($partials) !== 3) {
            throw new InvalidArgumentException('Se requieren exactamente tres parciales.');
        }

        $result = $this->calculator->calculate(array_map(
            fn ($v) => ($v === null || trim((string) $v) === '') ? null : (string) $v,
            $partials,
        ));

        return [
            'partial_1' => GradeCalculator::centsToDecimalString($result->partialCents[0]),
            'partial_2' => GradeCalculator::centsToDecimalString($result->partialCents[1]),
            'partial_3' => GradeCalculator::centsToDecimalString($result->partialCents[2]),
        ];
    }

    /** @return array{partial_1: ?string, partial_2: ?string, partial_3: ?string} */
    private function valuesOf(Grade $grade): array
    {
        return [
            'partial_1' => $grade->partial_1,
            'partial_2' => $grade->partial_2,
            'partial_3' => $grade->partial_3,
        ];
    }
}
