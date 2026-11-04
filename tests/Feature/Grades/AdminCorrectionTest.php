<?php

namespace Tests\Feature\Grades;

use App\Enums\AuditOperation;
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

class AdminCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Enrollment $enrollment;

    private AcademicPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->period = AcademicPeriod::factory()->closed()->create();
        $offering = CourseOffering::factory()->create(['academic_period_id' => $this->period->id]);
        $this->enrollment = Enrollment::factory()->create(['course_offering_id' => $offering->id]);
        Grade::factory()->create(['enrollment_id' => $this->enrollment->id, 'partial_1' => '13.99', 'partial_2' => '14', 'partial_3' => '14', 'version' => 1]);
    }

    private function url(): string
    {
        return "/admin/enrollments/{$this->enrollment->id}/correction";
    }

    #[Test]
    public function corrige_con_motivo_en_periodo_cerrado_y_audita_como_correccion(): void
    {
        $this->actingAs($this->admin)->get($this->url())->assertOk()->assertSee('41,99');

        $this->actingAs($this->admin)->put($this->url(), [
            'partial_1' => '14', 'partial_2' => '14', 'partial_3' => '14', 'version' => 1,
            'reason' => 'Error de digitación en el 1P según acta física',
        ])->assertSessionHasNoErrors()->assertRedirect("/admin/course-offerings/{$this->enrollment->course_offering_id}");

        $this->assertSame('14.00', Grade::sole()->partial_1);
        $log = AuditLog::sole();
        $this->assertSame(AuditOperation::Correccion, $log->operation);
        $this->assertSame('Error de digitación en el 1P según acta física', $log->reason);
        $this->assertSame('13.99', $log->old_values['partial_1']);

        $this->actingAs($this->admin)->get($this->url())->assertSee('Error de digitación en el 1P');
    }

    #[Test]
    public function correccion_sin_motivo_es_rechazada(): void
    {
        $this->actingAs($this->admin)->put($this->url(), [
            'partial_1' => '14', 'partial_2' => '14', 'partial_3' => '14', 'version' => 1, 'reason' => '   ',
        ])->assertSessionHasErrors('reason');

        $this->assertSame('13.99', Grade::sole()->partial_1);
        $this->assertSame(0, AuditLog::count());
    }

    #[Test]
    public function correccion_en_periodo_planificado_es_rechazada(): void
    {
        $this->period->status = PeriodStatus::Planned;
        $this->period->save();

        $this->actingAs($this->admin)->put($this->url(), [
            'partial_1' => '14', 'partial_2' => '14', 'partial_3' => '14', 'version' => 1, 'reason' => 'Motivo válido',
        ])->assertSessionHasErrors('general');
    }

    #[Test]
    public function el_docente_no_puede_usar_la_correccion(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)->get($this->url())->assertForbidden();
        $this->actingAs($teacher)->put($this->url(), [
            'partial_1' => '20', 'partial_2' => '20', 'partial_3' => '20', 'version' => 1, 'reason' => 'Intento',
        ])->assertForbidden();
    }

    #[Test]
    public function el_docente_de_la_oferta_no_modifica_en_periodo_cerrado(): void
    {
        $teacher = CourseOffering::find($this->enrollment->course_offering_id)->teacher()->first();

        $this->actingAs($teacher)->put(
            "/offerings/{$this->enrollment->course_offering_id}/enrollments/{$this->enrollment->id}/grade",
            ['partial_1' => '20', 'partial_2' => '20', 'partial_3' => '20', 'version' => 1],
        )->assertSessionHasErrorsIn('grade_'.$this->enrollment->id, 'general');

        $this->assertSame('13.99', Grade::sole()->partial_1);
    }
}
