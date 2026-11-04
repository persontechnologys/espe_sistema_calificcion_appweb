<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CourseOfferingManagementTest extends TestCase
{
    use RefreshDatabase;

    private AcademicPeriod $period;

    private Subject $subject;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
        $this->period = AcademicPeriod::factory()->open()->create();
        $this->subject = Subject::factory()->create();
        $this->teacher = User::factory()->teacher()->create();
    }

    private function data(array $overrides = []): array
    {
        return [
            'academic_period_id' => $this->period->id, 'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id, 'nrc' => ' 10234 ', 'classroom' => 'B-204', 'campus' => '',
            ...$overrides,
        ];
    }

    #[Test]
    public function crea_oferta_con_aula_y_campus_opcionales(): void
    {
        $this->post('/admin/course-offerings', $this->data())->assertSessionHasNoErrors();

        $this->assertDatabaseHas('course_offerings', ['nrc' => '10234', 'classroom' => 'B-204', 'campus' => null]);
    }

    #[Test]
    public function rechaza_nrc_repetido_en_el_mismo_periodo(): void
    {
        CourseOffering::factory()->create(['academic_period_id' => $this->period->id, 'nrc' => '10234']);

        $this->post('/admin/course-offerings', $this->data())->assertSessionHasErrors('nrc');
    }

    #[Test]
    public function permite_nrc_repetido_en_otro_periodo(): void
    {
        CourseOffering::factory()->create(['nrc' => '10234']); // otro período

        $this->post('/admin/course-offerings', $this->data())->assertSessionHasNoErrors();
        $this->assertSame(2, CourseOffering::where('nrc', '10234')->count());
    }

    #[Test]
    public function rechaza_asignar_un_usuario_que_no_es_docente(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/admin/course-offerings', $this->data(['teacher_id' => $admin->id]))
            ->assertSessionHasErrors('teacher_id');
    }

    #[Test]
    public function rechaza_asignar_un_docente_inactivo(): void
    {
        $inactive = User::factory()->teacher()->inactive()->create();

        $this->post('/admin/course-offerings', $this->data(['teacher_id' => $inactive->id]))
            ->assertSessionHasErrors('teacher_id');
    }

    #[Test]
    public function rechaza_relaciones_inexistentes(): void
    {
        $this->post('/admin/course-offerings', $this->data(['academic_period_id' => 99999, 'subject_id' => 99999]))
            ->assertSessionHasErrors(['academic_period_id', 'subject_id']);
    }

    #[Test]
    public function reasigna_el_docente(): void
    {
        $offering = CourseOffering::factory()->create(['academic_period_id' => $this->period->id]);
        $newTeacher = User::factory()->teacher()->create();

        $this->put("/admin/course-offerings/{$offering->id}", $this->data([
            'teacher_id' => $newTeacher->id, 'nrc' => $offering->nrc, 'subject_id' => $offering->subject_id,
        ]))->assertSessionHasNoErrors();

        $this->assertSame($newTeacher->id, $offering->fresh()->teacher_id);
    }

    #[Test]
    public function no_permite_mover_de_periodo_una_oferta_con_matriculas(): void
    {
        $enrollment = Enrollment::factory()->create();
        $offering = $enrollment->courseOffering()->first();

        $this->put("/admin/course-offerings/{$offering->id}", $this->data([
            'academic_period_id' => $this->period->id, 'nrc' => $offering->nrc, 'subject_id' => $offering->subject_id,
        ]))->assertSessionHasErrors('academic_period_id');
    }

    #[Test]
    public function rechaza_borrar_oferta_con_matriculas(): void
    {
        $enrollment = Enrollment::factory()->create();

        $this->delete("/admin/course-offerings/{$enrollment->course_offering_id}")->assertSessionHasErrors('general');
        $this->assertDatabaseHas('course_offerings', ['id' => $enrollment->course_offering_id]);
    }

    #[Test]
    public function filtra_por_periodo_y_asignatura(): void
    {
        CourseOffering::factory()->create(['academic_period_id' => $this->period->id, 'subject_id' => $this->subject->id, 'nrc' => 'AAA11']);
        CourseOffering::factory()->create(['nrc' => 'BBB22']);

        $this->get("/admin/course-offerings?academic_period_id={$this->period->id}&subject_id={$this->subject->id}")
            ->assertOk()->assertSee('AAA11')->assertDontSee('BBB22');
    }
}
