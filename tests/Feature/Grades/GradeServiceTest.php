<?php

namespace Tests\Feature\Grades;

use App\Enums\AuditOperation;
use App\Enums\GradeStatus;
use App\Enums\PeriodStatus;
use App\Models\AcademicPeriod;
use App\Models\AuditLog;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\User;
use App\Services\Grades\GradeConflictException;
use App\Services\Grades\GradeNotAllowedException;
use App\Services\Grades\GradeService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class GradeServiceTest extends TestCase
{
    use RefreshDatabase;

    private GradeService $service;

    private User $teacher;

    private User $admin;

    private AcademicPeriod $period;

    private Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(GradeService::class);
        $this->teacher = User::factory()->teacher()->create();
        $this->admin = User::factory()->admin()->create();
        $this->period = AcademicPeriod::factory()->open()->create();
        $offering = CourseOffering::factory()->create([
            'academic_period_id' => $this->period->id,
            'teacher_id' => $this->teacher->id,
        ]);
        $this->enrollment = Enrollment::factory()->create(['course_offering_id' => $offering->id]);
    }

    private function setPeriod(PeriodStatus $status): void
    {
        $this->period->status = $status;
        $this->period->save();
    }

    #[Test]
    public function el_docente_registra_notas_y_se_calcula_el_resultado(): void
    {
        $grade = $this->service->register($this->teacher, $this->enrollment, ['10.10', '15.2', '16.70'], 0);

        $grade->refresh();
        $this->assertSame(['10.10', '15.20', '16.70'], $grade->partials());
        $this->assertSame(1, $grade->version);
        $this->assertSame('42,00', $grade->result()->totalFormatted());
        $this->assertSame(GradeStatus::Aprobado, $grade->result()->status);
    }

    #[Test]
    public function el_registro_crea_auditoria_con_valores_anteriores_y_nuevos(): void
    {
        $this->service->register($this->teacher, $this->enrollment, ['10', null, null], 0);
        $this->service->register($this->teacher, $this->enrollment, ['10', '12.5', null], 1);

        $logs = AuditLog::orderBy('id')->get();
        $this->assertCount(2, $logs);
        $this->assertNull($logs[0]->old_values);
        $this->assertSame(['partial_1' => '10.00', 'partial_2' => null, 'partial_3' => null], $logs[0]->new_values);
        $this->assertSame(['partial_1' => '10.00', 'partial_2' => null, 'partial_3' => null], $logs[1]->old_values);
        $this->assertSame('12.50', $logs[1]->new_values['partial_2']);
        $this->assertSame(AuditOperation::Registro, $logs[1]->operation);
        $this->assertSame($this->teacher->id, $logs[1]->user_id);
        $this->assertNull($logs[1]->reason);
    }

    #[Test]
    public function guardar_sin_cambios_no_audita_ni_cambia_la_version(): void
    {
        $this->service->register($this->teacher, $this->enrollment, ['10', '10', '10'], 0);
        $grade = $this->service->register($this->teacher, $this->enrollment, ['10.00', '10', '10.0'], 1);

        $this->assertSame(1, $grade->fresh()->version);
        $this->assertSame(1, AuditLog::count());
    }

    #[Test]
    public function parciales_pendientes_se_guardan_como_null_y_el_registro_es_incompleto(): void
    {
        $grade = $this->service->register($this->teacher, $this->enrollment, ['0', '', null], 0)->fresh();

        $this->assertSame('0.00', $grade->partial_1);
        $this->assertNull($grade->partial_2);
        $this->assertNull($grade->partial_3);
        $this->assertSame(GradeStatus::Incompleto, $grade->result()->status);
    }

    #[Test]
    public function rechaza_valores_fuera_de_rango_o_con_mas_de_dos_decimales(): void
    {
        foreach (['20.01', '-1', '10.123'] as $bad) {
            try {
                $this->service->register($this->teacher, $this->enrollment, [$bad, '10', '10'], 0);
                $this->fail("Se aceptó {$bad}");
            } catch (InvalidArgumentException) {
                $this->assertSame(0, Grade::count());
            }
        }
    }

    #[Test]
    public function otro_docente_no_puede_registrar_en_una_oferta_ajena(): void
    {
        $other = User::factory()->teacher()->create();

        $this->expectException(AuthorizationException::class);
        $this->service->register($other, $this->enrollment, ['10', '10', '10'], 0);
    }

    #[Test]
    public function el_docente_reasignado_pierde_el_acceso(): void
    {
        $newTeacher = User::factory()->teacher()->create();
        CourseOffering::whereKey($this->enrollment->course_offering_id)->update(['teacher_id' => $newTeacher->id]);

        $this->service->register($newTeacher, $this->enrollment, ['10', '10', '10'], 0);

        $this->expectException(AuthorizationException::class);
        $this->service->register($this->teacher, $this->enrollment, ['11', '10', '10'], 1);
    }

    #[Test]
    public function el_docente_no_registra_en_periodo_planificado(): void
    {
        $this->setPeriod(PeriodStatus::Planned);

        $this->expectException(GradeNotAllowedException::class);
        $this->service->register($this->teacher, $this->enrollment, ['10', '10', '10'], 0);
    }

    #[Test]
    public function el_docente_no_registra_en_periodo_cerrado(): void
    {
        $this->service->register($this->teacher, $this->enrollment, ['10', '10', '10'], 0);
        $this->setPeriod(PeriodStatus::Closed);

        try {
            $this->service->register($this->teacher, $this->enrollment, ['20', '20', '20'], 1);
            $this->fail('El docente modificó una nota en período cerrado.');
        } catch (GradeNotAllowedException) {
            $this->assertSame('10.00', Grade::sole()->partial_1);
        }
    }

    #[Test]
    public function el_administrador_corrige_con_motivo_en_periodo_cerrado(): void
    {
        $this->service->register($this->teacher, $this->enrollment, ['10', '15', '16.99'], 0);
        $this->setPeriod(PeriodStatus::Closed);

        $grade = $this->service->correct($this->admin, $this->enrollment, ['10', '15', '17'], 1, '  Error de digitación en 3P  ');

        $this->assertSame(GradeStatus::Aprobado, $grade->fresh()->result()->status);
        $log = AuditLog::latest('id')->first();
        $this->assertSame(AuditOperation::Correccion, $log->operation);
        $this->assertSame('Error de digitación en 3P', $log->reason);
        $this->assertSame($this->admin->id, $log->user_id);
        $this->assertSame('16.99', $log->old_values['partial_3']);
        $this->assertSame('17.00', $log->new_values['partial_3']);
    }

    #[Test]
    public function la_correccion_sin_motivo_es_rechazada(): void
    {
        $this->expectException(GradeNotAllowedException::class);
        $this->service->correct($this->admin, $this->enrollment, ['10', '10', '10'], 0, '   ');
    }

    #[Test]
    public function la_correccion_no_se_permite_en_periodo_planificado(): void
    {
        $this->setPeriod(PeriodStatus::Planned);

        $this->expectException(GradeNotAllowedException::class);
        $this->service->correct($this->admin, $this->enrollment, ['10', '10', '10'], 0, 'Motivo');
    }

    #[Test]
    public function un_docente_no_puede_usar_la_correccion(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->service->correct($this->teacher, $this->enrollment, ['10', '10', '10'], 0, 'Motivo');
    }

    #[Test]
    public function el_administrador_no_usa_el_registro_ordinario(): void
    {
        $this->expectException(AuthorizationException::class);
        $this->service->register($this->admin, $this->enrollment, ['10', '10', '10'], 0);
    }

    #[Test]
    public function bloqueo_optimista_rechaza_una_version_desactualizada(): void
    {
        // Docente y administrador abren el formulario con la versión 1.
        $this->service->register($this->teacher, $this->enrollment, ['10', '10', '10'], 0);

        // El administrador guarda primero (versión pasa a 2).
        $this->service->correct($this->admin, $this->enrollment, ['12', '10', '10'], 1, 'Revisión');

        // El docente guarda con la versión 1: se rechaza en lugar de sobrescribir.
        try {
            $this->service->register($this->teacher, $this->enrollment, ['10', '10', '15'], 1);
            $this->fail('Se sobrescribió una nota modificada por otra persona.');
        } catch (GradeConflictException) {
            $grade = Grade::sole();
            $this->assertSame('12.00', $grade->partial_1);
            $this->assertSame('10.00', $grade->partial_3);
            $this->assertSame(2, $grade->version);
        }
    }

    #[Test]
    public function crear_cuando_ya_existe_registro_es_conflicto(): void
    {
        Grade::factory()->create(['enrollment_id' => $this->enrollment->id]);

        $this->expectException(GradeConflictException::class);
        $this->service->register($this->teacher, $this->enrollment, ['10', '10', '10'], 0);
    }

    #[Test]
    public function si_falla_la_auditoria_no_se_guarda_la_nota(): void
    {
        AuditLog::creating(fn () => throw new RuntimeException('Fallo simulado de auditoría'));

        try {
            $this->service->register($this->teacher, $this->enrollment, ['10', '10', '10'], 0);
            $this->fail('Se esperaba una excepción.');
        } catch (RuntimeException $e) {
            $this->assertSame('Fallo simulado de auditoría', $e->getMessage());
        }

        $this->assertSame(0, Grade::count());
        $this->assertSame(0, AuditLog::count());
    }

    #[Test]
    public function la_auditoria_no_se_puede_modificar_ni_borrar_desde_la_aplicacion(): void
    {
        $this->service->register($this->teacher, $this->enrollment, ['10', '10', '10'], 0);
        $log = AuditLog::sole();

        try {
            $log->update(['reason' => 'manipulado']);
            $this->fail('Se modificó la auditoría.');
        } catch (\LogicException) {
        }

        try {
            $log->delete();
            $this->fail('Se borró la auditoría.');
        } catch (\LogicException) {
        }

        $this->assertNull(DB::table('audit_logs')->value('reason'));
        $this->assertSame(1, AuditLog::count());
    }
}
