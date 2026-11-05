<?php

namespace Tests\Feature\Ui;

use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Presentación de la planilla y de la corrección. El cálculo sigue en el servidor:
 * estas pruebas solo verifican la estructura que necesitan el bloqueo optimista y la accesibilidad.
 */
class GradeSheetTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private AcademicPeriod $period;

    private CourseOffering $offering;

    private Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacher = User::factory()->teacher()->create();
        $this->period = AcademicPeriod::factory()->open()->create();
        $this->offering = CourseOffering::factory()->create(['academic_period_id' => $this->period->id, 'teacher_id' => $this->teacher->id]);
        $this->enrollment = Enrollment::factory()->create(['course_offering_id' => $this->offering->id]);
        Grade::factory()->create(['enrollment_id' => $this->enrollment->id, 'partial_1' => '10', 'partial_2' => '15', 'partial_3' => null, 'version' => 3]);
    }

    private function sheet(): string
    {
        return $this->actingAs($this->teacher)->get("/offerings/{$this->offering->id}/grades")->assertOk()->getContent();
    }

    #[Test]
    public function cada_fila_tiene_su_formulario_con_version_y_entradas_accesibles(): void
    {
        $html = $this->sheet();
        $id = $this->enrollment->id;
        $student = $this->enrollment->student()->first();

        $this->assertStringContainsString('<form id="f'.$id.'"', $html);
        $this->assertStringContainsString('name="version" value="3"', $html);
        $this->assertStringContainsString('form="f'.$id.'" name="partial_1"', $html);
        $this->assertStringContainsString('inputmode="decimal"', $html);
        $this->assertStringContainsString('pattern="^\d{1,2}([.,]\d{1,2})?$"', $html);
        $this->assertStringContainsString('aria-label="1P de '.$student->institutional_id, $html);
        $this->assertStringContainsString('0 a 20, máx. 2 decimales', $html);
        $this->assertStringContainsString('aprobado con total ≥ 42,00', $html);
    }

    #[Test]
    public function la_planilla_muestra_el_resultado_calculado_por_el_servidor(): void
    {
        $this->sheet(); // 3P pendiente
        $this->actingAs($this->teacher)->get("/offerings/{$this->offering->id}/grades")
            ->assertSee('INCOMPLETO')->assertSee('—');
    }

    #[Test]
    public function con_el_periodo_cerrado_la_planilla_es_de_solo_lectura(): void
    {
        $this->period->forceFill(['status' => 'closed'])->save();

        $html = $this->sheet();

        $this->assertStringContainsString('Solo lectura.', $html);
        $this->assertStringNotContainsString('<form id="f'.$this->enrollment->id.'"', $html);
        $this->assertMatchesRegularExpression('#name="partial_1"[^>]*disabled#s', $html);
    }

    #[Test]
    public function el_error_del_servidor_se_muestra_en_la_fila(): void
    {
        $this->actingAs($this->teacher)
            ->from("/offerings/{$this->offering->id}/grades")
            ->put("/offerings/{$this->offering->id}/enrollments/{$this->enrollment->id}/grade", [
                'row' => $this->enrollment->id, 'version' => 3, 'partial_1' => '25', 'partial_2' => '', 'partial_3' => '',
            ]);

        $this->get("/offerings/{$this->offering->id}/grades")
            ->assertOk()
            ->assertSee('id="msg-f'.$this->enrollment->id.'"', false)
            ->assertSee('data-server-messages', false)
            ->assertSee('Revise los campos marcados.');
    }

    #[Test]
    public function la_correccion_del_administrador_confirma_con_valores_anteriores_y_nuevos(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get("/admin/enrollments/{$this->enrollment->id}/correction")
            ->assertOk()
            ->assertSee('data-correction-form', false)
            ->assertSee('id="correction-modal"', false)
            ->assertSee('data-old="10.00"', false)
            ->assertSee('Motivo de la corrección');
    }
}
