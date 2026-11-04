<?php

namespace App\Services\Grades;

use RuntimeException;

/** La operación no está permitida por el estado del período, el rol o la falta de motivo. */
class GradeNotAllowedException extends RuntimeException {}
