<?php

namespace Tests\Feature\Grades;

use App\Enums\PeriodStatus;
use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TeacherGradingTest extends TestCase
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
    }

    private function url(?Enrollment $enrollment = null, ?CourseOffering $offering = null): string
    {
        $offering ??= $this->offering;
        $enrollment ??= $this->enrollment;

        return "/offerings/{$offering->id}/enrollments/{$enrollment->id}/grade";
    }

    #[Test]
    public function el_docente_ve_su_planilla(): void
    {
        $this->actingAs($this->teacher)->get("/offerings/{$this->offering->id}/grades")
            ->assertOk()->assertSee($this->enrollment->student()->first()->institutional_id);
    }

    #[Test]
    public function el_docente_ve_solo_sus_ofertas_en_el_inicio(): void
    {
        $ajena = CourseOffering::factory()->create(['nrc' => 'AJENA9']);

        $this->actingAs($this->teacher)->get('/dashboard')
            ->assertSee($this->offering->nrc)->assertDontSee('AJENA9');
    }

    #[Test]
    public function el_docente_no_ve_la_planilla_de_una_oferta_ajena(): void
    {
        $other = CourseOffering::factory()->create();

        $this->actingAs($this->teacher)->get("/offerings/{$other->id}/grades")->assertForbidden();
    }

    #[Test]
    public function el_administrador_no_usa_la_planilla_del_docente(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get("/offerings/{$this->offering->id}/grades")->assertForbidden();
    }

    #[Test]
    public function registra_notas_con_coma_o_punto_decimal_y_muestra_el_calculo(): void
    {
        $this->actingAs($this->teacher)
            ->put($this->url(), ['partial_1' => '10,10', 'partial_2' => '15.2', 'partial_3' => '16,70', 'version' => 0])
            ->assertSessionHasNoErrors();

        $this->assertSame(['10.10', '15.20', '16.70'], Grade::sole()->partials());

        $this->get("/offerings/{$this->offering->id}/grades")->assertSee('42,00')->assertSee('APROBADO');
    }

    #[Test]
    public function ignora_promedio_total_y_estado_enviados_por_el_cliente(): void
    {
        $this->actingAs($this->teacher)->put($this->url(), [
            'partial_1' => '5', 'partial_2' => '5', 'partial_3' => '5', 'version' => 0,
            'average' => '20', 'total' => '60', 'status' => 'APROBADO',
        ])->assertSessionHasNoErrors();

        $result = Grade::sole()->result();
        $this->assertSame('15,00', $result->totalFormatted());
        $this->assertSame('REPROBADO', $result->status->value);
        $this->assertArrayNotHasKey('status', AuditLog::sole()->new_values);
    }

    #[Test]
    public function acepta_los_limites_0_y_20(): void
    {
        $this->actingAs($this->teacher)
            ->put($this->url(), ['partial_1' => '0', 'partial_2' => '20', 'partial_3' => '20.00', 'version' => 0])
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function rechaza_valores_invalidos_sin_guardar(): void
    {
        $bag = 'grade_'.$this->enrollment->id;

        foreach (['-1', '20.01', '21', '10.123', 'abc', '1e1'] as $bad) {
            $this->actingAs($this->teacher)
                ->put($this->url(), ['partial_1' => $bad, 'partial_2' => '10', 'partial_3' => '10', 'version' => 0])
                ->assertSessionHasErrorsIn($bag, 'partial_1');
        }

        $this->assertSame(0, Grade::count());
    }

    #[Test]
    public function un_parcial_vacio_queda_pendiente(): void
    {
        $this->actingAs($this->teacher)
            ->put($this->url(), ['partial_1' => '0', 'partial_2' => '', 'partial_3' => '15', 'version' => 0]);

        $grade = Grade::sole();
        $this->assertSame('0.00', $grade->partial_1);
        $this->assertNull($grade->partial_2);
        $this->get("/offerings/{$this->offering->id}/grades")->assertSee('INCOMPLETO');
    }

    #[Test]
    public function el_docente_no_modifica_notas_de_una_oferta_ajena(): void
    {
        $other = CourseOffering::factory()->create(['academic_period_id' => $this->period->id]);
        $otherEnrollment = Enrollment::factory()->create(['course_offering_id' => $other->id]);

        $this->actingAs($this->teacher)
            ->put($this->url($otherEnrollment, $other), ['partial_1' => '20', 'partial_2' => '20', 'partial_3' => '20', 'version' => 0])
            ->assertForbidden();

        $this->assertSame(0, Grade::count());
    }

    #[Test]
    public function no_acepta_una_matricula_de_otra_oferta_en_la_url_de_la_propia(): void
    {
        $otherEnrollment = Enrollment::factory()->create();

        $this->actingAs($this->teacher)
            ->put($this->url($otherEnrollment, $this->offering), ['partial_1' => '20', 'partial_2' => '20', 'partial_3' => '20', 'version' => 0])
            ->assertNotFound();

        $this->assertSame(0, Grade::count());
    }

    #[Test]
    public function el_docente_reasignado_pierde_el_acceso_a_la_oferta(): void
    {
        $this->offering->update(['teacher_id' => User::factory()->teacher()->create()->id]);

        $this->actingAs($this->teacher)->get("/offerings/{$this->offering->id}/grades")->assertForbidden();
        $this->actingAs($this->teacher)
            ->put($this->url(), ['partial_1' => '20', 'partial_2' => '20', 'partial_3' => '20', 'version' => 0])
            ->assertForbidden();
    }

    #[Test]
    public function registro_bloqueado_en_periodo_cerrado_y_planificado(): void
    {
        foreach ([PeriodStatus::Closed, PeriodStatus::Planned] as $status) {
            $this->period->status = $status;
            $this->period->save();

            $this->actingAs($this->teacher)->get("/offerings/{$this->offering->id}/grades")
                ->assertOk()->assertSee('el registro de notas está bloqueado', false);

            $this->actingAs($this->teacher)
                ->put($this->url(), ['partial_1' => '20', 'partial_2' => '20', 'partial_3' => '20', 'version' => 0])
                ->assertSessionHasErrorsIn('grade_'.$this->enrollment->id, 'general');
        }

        $this->assertSame(0, Grade::count());
    }

    #[Test]
    public function conflicto_de_bloqueo_optimista_se_informa_al_usuario(): void
    {
        Grade::factory()->create(['enrollment_id' => $this->enrollment->id, 'version' => 3]);

        $this->actingAs($this->teacher)
            ->put($this->url(), ['partial_1' => '1', 'partial_2' => '1', 'partial_3' => '1', 'version' => 2])
            ->assertSessionHasErrorsIn('grade_'.$this->enrollment->id, 'general');

        $this->assertSame('14.00', Grade::sole()->partial_1);
    }

    #[Test]
    public function usuario_inactivo_no_puede_registrar_notas(): void
    {
        $this->teacher->update(['is_active' => false]);

        $this->actingAs($this->teacher)
            ->put($this->url(), ['partial_1' => '20', 'partial_2' => '20', 'partial_3' => '20', 'version' => 0])
            ->assertRedirect('/login');

        $this->assertSame(0, Grade::count());
    }
}
