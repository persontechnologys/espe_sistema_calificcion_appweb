<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * La base de datos refuerza las reglas aunque la aplicación se salte la validación (§8.15).
 * Se ejecuta en PostgreSQL (local) y MySQL 8 (CI).
 */
class SchemaConstraintsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function id_institucional_de_estudiante_es_unico_sin_importar_mayusculas(): void
    {
        Student::factory()->create(['institutional_id' => 'L00423704']);

        $this->expectException(UniqueConstraintViolationException::class);
        // Se normaliza a mayúsculas antes de guardar, así ambos motores lo tratan igual.
        Student::factory()->create(['institutional_id' => ' l00423704 ']);
    }

    #[Test]
    public function id_institucional_de_docente_es_unico(): void
    {
        User::factory()->teacher()->create(['institutional_id' => 'L00012345']);

        $this->expectException(UniqueConstraintViolationException::class);
        User::factory()->teacher()->create(['institutional_id' => 'l00012345']);
    }

    #[Test]
    public function varios_administradores_pueden_no_tener_id_institucional(): void
    {
        User::factory()->admin()->count(2)->create();

        $this->assertSame(2, User::whereNull('institutional_id')->count());
    }

    #[Test]
    public function docente_sin_id_institucional_es_rechazado_por_la_base_de_datos(): void
    {
        $this->expectException(QueryException::class);

        User::factory()->create(['role' => UserRole::Teacher, 'institutional_id' => null]);
    }

    #[Test]
    public function rol_desconocido_es_rechazado_por_la_base_de_datos(): void
    {
        $this->expectException(QueryException::class);

        DB::table('users')->insert([
            'name' => 'X', 'email' => 'x@demo.test', 'password' => 'x', 'role' => 'superuser',
        ]);
    }

    #[Test]
    public function codigo_de_asignatura_es_unico(): void
    {
        Subject::factory()->create(['code' => 'COMP-A0G11']);

        $this->expectException(UniqueConstraintViolationException::class);
        Subject::factory()->create(['code' => 'comp-a0g11']);
    }

    #[Test]
    public function nrc_repetido_en_el_mismo_periodo_es_rechazado(): void
    {
        $period = AcademicPeriod::factory()->open()->create();
        CourseOffering::factory()->create(['academic_period_id' => $period->id, 'nrc' => '10234']);

        $this->expectException(UniqueConstraintViolationException::class);
        CourseOffering::factory()->create(['academic_period_id' => $period->id, 'nrc' => '10234']);
    }

    #[Test]
    public function nrc_repetido_en_otro_periodo_es_permitido(): void
    {
        CourseOffering::factory()->create(['nrc' => '10234']);
        CourseOffering::factory()->create(['nrc' => '10234']);

        $this->assertSame(2, CourseOffering::where('nrc', '10234')->count());
    }

    #[Test]
    public function matricula_duplicada_es_rechazada(): void
    {
        $enrollment = Enrollment::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);
        Enrollment::create([
            'student_id' => $enrollment->student_id,
            'course_offering_id' => $enrollment->course_offering_id,
        ]);
    }

    #[Test]
    public function matricula_con_relaciones_inexistentes_es_rechazada(): void
    {
        $this->expectException(QueryException::class);

        Enrollment::create(['student_id' => 999999, 'course_offering_id' => 999999]);
    }

    #[Test]
    public function solo_un_registro_de_notas_por_matricula(): void
    {
        $grade = Grade::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);
        Grade::factory()->create(['enrollment_id' => $grade->enrollment_id]);
    }

    #[Test]
    public function la_base_de_datos_rechaza_parciales_fuera_de_rango(): void
    {
        $this->expectException(QueryException::class);

        Grade::factory()->create(['partial_1' => '20.01']);
    }

    #[Test]
    public function la_base_de_datos_rechaza_parciales_negativos(): void
    {
        $this->expectException(QueryException::class);

        Grade::factory()->create(['partial_2' => '-0.01']);
    }

    #[Test]
    public function parcial_null_y_parcial_cero_se_almacenan_distinto(): void
    {
        $grade = Grade::factory()->create(['partial_1' => '0', 'partial_2' => null, 'partial_3' => '0.00']);
        $grade->refresh();

        $this->assertSame('0.00', $grade->partial_1);
        $this->assertNull($grade->partial_2);
    }

    #[Test]
    public function no_se_puede_borrar_un_estudiante_con_matriculas(): void
    {
        $enrollment = Enrollment::factory()->create();

        $this->expectException(QueryException::class);
        Student::whereKey($enrollment->student_id)->delete();
    }

    #[Test]
    public function no_se_puede_borrar_una_oferta_con_matriculas(): void
    {
        $enrollment = Enrollment::factory()->create();

        $this->expectException(QueryException::class);
        CourseOffering::whereKey($enrollment->course_offering_id)->delete();
    }

    #[Test]
    public function periodo_con_fecha_fin_anterior_al_inicio_es_rechazado(): void
    {
        $this->expectException(QueryException::class);

        AcademicPeriod::factory()->create(['start_date' => '2025-05-01', 'end_date' => '2025-04-01']);
    }
}
