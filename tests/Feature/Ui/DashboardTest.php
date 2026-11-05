<?php

namespace Tests\Feature\Ui;

use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\Grades\GradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Panel de inicio con datos reales. preventLazyLoading está activo fuera de producción:
 * si la vista provocara un N+1, la petición fallaría con LazyLoadingViolationException.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private CourseOffering $offering;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::factory()->teacher()->create(['name' => 'Docente Panel']);
        $period = AcademicPeriod::factory()->open()->create(['code' => '202451', 'description' => 'PREGRADO PANEL']);
        $this->offering = CourseOffering::factory()->create([
            'academic_period_id' => $period->id,
            'subject_id' => Subject::factory()->create(['name' => 'ASIGNATURA PANEL'])->id,
            'teacher_id' => $this->teacher->id,
            'nrc' => '70001',
        ]);

        $service = app(GradeService::class);
        $grade = fn (?array $partials) => tap(
            Enrollment::factory()->create(['student_id' => Student::factory()->create()->id, 'course_offering_id' => $this->offering->id]),
            fn (Enrollment $e) => $partials === null ? null : $service->register($this->teacher, $e, $partials, 0),
        );

        $grade(['14', '14', '14']);   // completo
        $grade(['0', '0', '0']);      // completo: 0,00 es una nota registrada
        $grade(['12', null, '15']);   // incompleto: parcial NULL
        $grade(null);                 // incompleto: sin registro de notas
    }

    #[Test]
    public function el_administrador_ve_el_conteo_correcto_de_registros_incompletos(): void
    {
        Student::factory()->create(['is_active' => false]); // no cuenta como activo

        $this->actingAs(User::factory()->admin()->create())->get('/dashboard')
            ->assertOk()
            ->assertSee('data-stat="incomplete">2<', false)
            ->assertSee('data-stat="enrollments">4<', false)
            ->assertSee('data-stat="students">4<', false)
            ->assertSee('data-stat="teachers">1<', false)
            ->assertSee('data-stat="openOfferings">1<', false)
            ->assertSee('Registros incompletos');
    }

    #[Test]
    public function el_administrador_ve_el_periodo_abierto_y_la_actividad_reciente_sin_n_mas_1(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/dashboard')
            ->assertOk()
            ->assertSee('202451 - PREGRADO PANEL')
            ->assertSee('Actividad reciente')
            ->assertSee('Docente Panel')
            ->assertSee('NRC 70001');
    }

    #[Test]
    public function sin_periodo_abierto_se_muestra_un_estado_vacio(): void
    {
        AcademicPeriod::query()->update(['status' => 'closed']);

        $this->actingAs(User::factory()->admin()->create())->get('/dashboard')
            ->assertOk()->assertSee('No hay un período abierto');
    }

    #[Test]
    public function el_docente_ve_el_progreso_de_registros_completos_de_sus_ofertas(): void
    {
        $this->actingAs($this->teacher)->get('/dashboard')
            ->assertOk()
            ->assertSee('70001')
            ->assertSee('2 de 4 registros completos')
            ->assertSee('Calificaciones')
            ->assertDontSee('Registros incompletos');
    }

    #[Test]
    public function el_docente_con_periodo_cerrado_ve_el_boton_ver_en_lugar_de_calificaciones(): void
    {
        AcademicPeriod::query()->update(['status' => 'closed']);

        $html = $this->actingAs($this->teacher)->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('</i>Ver</a>', $html);
        $this->assertStringNotContainsString('</i>Calificaciones</a>', $html);
    }
}
