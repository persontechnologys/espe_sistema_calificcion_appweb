<?php

namespace App\Services\Grades;

use RuntimeException;

/** Bloqueo optimista: la nota cambió desde que se abrió el formulario (actualización perdida evitada). */
class GradeConflictException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('La calificación fue modificada por otra persona mientras usted la editaba. Se muestran los valores actuales; revise y vuelva a guardar.');
    }
}
