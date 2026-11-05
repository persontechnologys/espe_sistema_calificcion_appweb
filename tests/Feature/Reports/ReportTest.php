<?php

namespace Tests\Feature\Reports;

use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private CourseOffering $offering;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::factory()->teacher()->create(['name' => 'Carlos Andrade', 'institutional_id' => 'D00000101']);
        $period = AcademicPeriod::factory()->open()->create(['code' => '202451', 'description' => 'PREGRADO S-II OCT 24 - MAR 25']);
        $subject = Subject::factory()->create(['code' => 'COMP-A0G11', 'name' => 'PROGRAMACIÓN ORIENTADA A OBJETOS']);
        $this->offering = CourseOffering::factory()->create([
            'academic_period_id' => $period->id, 'subject_id' => $subject->id, 'teacher_id' => $this->teacher->id,
            'nrc' => '10234', 'classroom' => 'B-204', 'campus' => 'MATRIZ',
        ]);

        $this->enrollWithGrade('L00000001', 'ZAMBRANO', ['10', '15', '17']);      // 42,00 APROBADO
        $this->enrollWithGrade('L00000002', 'ALVAREZ', ['13.99', '14', '14']);   // 41,99 REPROBADO
        $this->enrollWithGrade('L00000003', 'MEDINA', ['12', null, '14']);       // INCOMPLETO
        $this->enrollWithGrade('L00000004', 'BRAVO', null);                      // sin registro: INCOMPLETO
    }

    private function enrollWithGrade(string $id, string $lastNames, ?array $partials): void
    {
        $student = Student::factory()->create(['institutional_id' => $id, 'last_names' => $lastNames]);
        $enrollment = Enrollment::factory()->create(['student_id' => $student->id, 'course_offering_id' => $this->offering->id]);

        if ($partials !== null) {
            Grade::factory()->create([
                'enrollment_id' => $enrollment->id,
                'partial_1' => $partials[0], 'partial_2' => $partials[1], 'partial_3' => $partials[2],
            ]);
        }
    }

    #[Test]
    public function muestra_encabezado_y_detalle_del_reporte(): void
    {
        $this->actingAs($this->teacher)->get("/reports/offerings/{$this->offering->id}")
            ->assertOk()
            ->assertSee('UNIVERSIDAD DE LAS FUERZAS ARMADAS - ESPE')
            ->assertSee('REPORTE DE CALIFICACIONES DE ESTUDIANTES')
            ->assertSee('202451 - PREGRADO S-II OCT 24 - MAR 25')
            ->assertSee('PROGRAMACIÓN ORIENTADA A OBJETOS')
            ->assertSee('COMP-A0G11')
            ->assertSee('10234')
            ->assertSee('B-204')
            ->assertSee('MATRIZ')
            ->assertSee('CARLOS ANDRADE')
            ->assertSee('D00000101')
            ->assertSee('Reporte generado por el Sistema de Gestión Académica');
    }

    #[Test]
    public function el_pie_del_reporte_indica_que_lo_genero_el_sistema_sin_afirmar_que_es_oficial(): void
    {
        $html = $this->actingAs($this->teacher)->get("/reports/offerings/{$this->offering->id}")->assertOk()->getContent();

        $footer = mb_strtolower(strip_tags(substr($html, strpos($html, '<footer class="small">'))));
        $this->assertStringContainsString('generado por el sistema de gestión académica', $footer);
        $this->assertStringContainsString('carlos andrade', $footer);
        $this->assertStringNotContainsString('oficial', mb_strtolower(strip_tags($html)));
        $this->assertStringNotContainsString('demostración', mb_strtolower(strip_tags($html)));
    }

    #[Test]
    public function muestra_promedio_total_y_estado_incluido_el_caso_41_99(): void
    {
        $this->actingAs($this->teacher)->get("/reports/offerings/{$this->offering->id}")
            // Ordenado por apellidos: ALVAREZ (41,99) antes que ZAMBRANO (42,00).
            ->assertSeeInOrder(['L00000002', '13,99', '14,00', '14,00', '14,00', '41,99', 'REPROBADO'])
            ->assertSeeInOrder(['L00000001', '10,00', '15,00', '17,00', '14,00', '42,00', 'APROBADO']);
    }

    #[Test]
    public function los_incompletos_muestran_guion_y_el_resumen_los_cuenta(): void
    {
        $response = $this->actingAs($this->teacher)->get("/reports/offerings/{$this->offering->id}");

        $response->assertSeeInOrder(['L00000003', '12,00', '—', '14,00', '—', '—', 'INCOMPLETO']);
        $response->assertSee('data-summary="APROBADO">1<', false);
        $response->assertSee('data-summary="REPROBADO">1<', false);
        $response->assertSee('data-summary="INCOMPLETO">2<', false);
    }

    #[Test]
    public function el_docente_no_ve_el_reporte_de_una_oferta_ajena(): void
    {
        $other = CourseOffering::factory()->create();

        $this->actingAs($this->teacher)->get("/reports/offerings/{$other->id}")->assertForbidden();
    }

    #[Test]
    public function el_listado_del_docente_solo_incluye_sus_ofertas(): void
    {
        CourseOffering::factory()->create(['nrc' => 'AJENA1']);

        $this->actingAs($this->teacher)->get('/reports')
            ->assertOk()->assertSee('10234')->assertDontSee('AJENA1');
    }

    #[Test]
    public function el_administrador_ve_cualquier_reporte(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get("/reports/offerings/{$this->offering->id}")->assertOk();
    }

    #[Test]
    public function filtra_por_periodo_asignatura_y_nrc(): void
    {
        $admin = User::factory()->admin()->create();
        $other = CourseOffering::factory()->create(['nrc' => '55555']);

        $this->actingAs($admin)->get("/reports?academic_period_id={$this->offering->academic_period_id}")
            ->assertSee('10234')->assertDontSee('55555');
        $this->actingAs($admin)->get("/reports?subject_id={$other->subject_id}")
            ->assertSee('55555')->assertDontSee('10234');
        $this->actingAs($admin)->get('/reports?nrc=5555')
            ->assertSee('55555')->assertDontSee('10234');
    }
}
