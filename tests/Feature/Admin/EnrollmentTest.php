<?php

namespace Tests\Feature\Admin;

use App\Enums\PeriodStatus;
use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private CourseOffering $offering;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
        $this->offering = CourseOffering::factory()->create();
    }

    private function url(): string
    {
        return "/admin/course-offerings/{$this->offering->id}/enrollments";
    }

    #[Test]
    public function matricula_un_estudiante(): void
    {
        $student = Student::factory()->create();

        $this->post($this->url(), ['student_id' => $student->id])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('enrollments', ['student_id' => $student->id, 'course_offering_id' => $this->offering->id]);
    }

    #[Test]
    public function matricula_duplicada_es_rechazada_con_mensaje(): void
    {
        $student = Student::factory()->create();
        Enrollment::factory()->create(['student_id' => $student->id, 'course_offering_id' => $this->offering->id]);

        $this->post($this->url(), ['student_id' => $student->id])
            ->assertSessionHasErrors(['student_id' => 'El estudiante ya está matriculado en esta oferta.']);

        $this->assertSame(1, Enrollment::count());
    }

    #[Test]
    public function matricula_de_estudiante_inexistente_o_inactivo_es_rechazada(): void
    {
        $inactive = Student::factory()->create(['is_active' => false]);

        $this->post($this->url(), ['student_id' => 999999])->assertSessionHasErrors('student_id');
        $this->post($this->url(), ['student_id' => $inactive->id])->assertSessionHasErrors('student_id');
        $this->assertSame(0, Enrollment::count());
    }

    #[Test]
    public function matricula_en_periodo_cerrado_es_rechazada(): void
    {
        $closed = AcademicPeriod::factory()->closed()->create();
        $offering = CourseOffering::factory()->create(['academic_period_id' => $closed->id]);

        $this->post("/admin/course-offerings/{$offering->id}/enrollments", ['student_id' => Student::factory()->create()->id])
            ->assertSessionHasErrors('student_id');

        $this->assertSame(0, Enrollment::count());
    }

    #[Test]
    public function matricula_en_periodo_planificado_es_permitida(): void
    {
        $period = AcademicPeriod::factory()->status(PeriodStatus::Planned)->create();
        $offering = CourseOffering::factory()->create(['academic_period_id' => $period->id]);

        $this->post("/admin/course-offerings/{$offering->id}/enrollments", ['student_id' => Student::factory()->create()->id])
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function elimina_matricula_sin_notas(): void
    {
        $enrollment = Enrollment::factory()->create(['course_offering_id' => $this->offering->id]);

        $this->delete($this->url()."/{$enrollment->id}")->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('enrollments', ['id' => $enrollment->id]);
    }

    #[Test]
    public function no_elimina_matricula_con_notas(): void
    {
        $enrollment = Enrollment::factory()->create(['course_offering_id' => $this->offering->id]);
        Grade::factory()->create(['enrollment_id' => $enrollment->id]);

        $this->delete($this->url()."/{$enrollment->id}")->assertSessionHasErrors('general');
        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id]);
    }

    #[Test]
    public function no_elimina_una_matricula_a_traves_de_otra_oferta(): void
    {
        $other = Enrollment::factory()->create();

        $this->delete($this->url()."/{$other->id}")->assertNotFound();
        $this->assertDatabaseHas('enrollments', ['id' => $other->id]);
    }

    #[Test]
    public function la_ficha_de_la_oferta_y_del_estudiante_muestran_las_notas(): void
    {
        $enrollment = Enrollment::factory()->create(['course_offering_id' => $this->offering->id]);
        Grade::factory()->create(['enrollment_id' => $enrollment->id, 'partial_1' => '13.99', 'partial_2' => '14', 'partial_3' => '14']);

        $this->get("/admin/course-offerings/{$this->offering->id}")
            ->assertOk()->assertSee('41,99')->assertSee('14,00')->assertSee('REPROBADO');

        $this->get("/admin/students/{$enrollment->student_id}")
            ->assertOk()->assertSee($this->offering->nrc)->assertSee('41,99');
    }
}
