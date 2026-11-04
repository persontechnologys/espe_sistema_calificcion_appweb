<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Services\Grades\GradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditLogViewTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function el_administrador_consulta_y_filtra_la_auditoria(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create(['name' => 'Docente Auditado']);
        $offering = CourseOffering::factory()->create([
            'teacher_id' => $teacher->id, 'nrc' => '77777',
            'academic_period_id' => AcademicPeriod::factory()->open()->create()->id,
        ]);
        $student = Student::factory()->create(['institutional_id' => 'L00777777']);
        $enrollment = Enrollment::factory()->create(['student_id' => $student->id, 'course_offering_id' => $offering->id]);

        $service = app(GradeService::class);
        $service->register($teacher, $enrollment, ['10', '10', '10'], 0);
        $service->correct($admin, $enrollment, ['11', '10', '10'], 1, 'Motivo de prueba auditado');

        $this->actingAs($admin)->get('/admin/audit-logs')
            ->assertOk()->assertSee('Docente Auditado')->assertSee('Motivo de prueba auditado')->assertSee('L00777777');

        $this->actingAs($admin)->get('/admin/audit-logs?operation=correccion')
            ->assertSee('Motivo de prueba auditado')->assertDontSee('Docente Auditado');

        $this->actingAs($admin)->get('/admin/audit-logs?student=l00777')->assertSee('77777');
        $this->actingAs($admin)->get('/admin/audit-logs?nrc=99999')->assertDontSee('L00777777');
    }

    #[Test]
    public function el_docente_no_accede_a_la_auditoria(): void
    {
        $this->actingAs(User::factory()->teacher()->create())->get('/admin/audit-logs')->assertForbidden();
    }
}
