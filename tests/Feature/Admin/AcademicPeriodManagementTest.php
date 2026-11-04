<?php

namespace Tests\Feature\Admin;

use App\Enums\PeriodStatus;
use App\Models\AcademicPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AcademicPeriodManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    private function validData(array $overrides = []): array
    {
        return [
            'code' => '202451',
            'description' => 'pregrado s-ii oct 24 - mar 25',
            'start_date' => '2024-10-01',
            'end_date' => '2025-03-31',
            ...$overrides,
        ];
    }

    #[Test]
    public function crea_periodo_planificado_y_compone_la_etiqueta(): void
    {
        $this->post('/admin/academic-periods', $this->validData())->assertSessionHasNoErrors();

        $period = AcademicPeriod::sole();
        $this->assertSame(PeriodStatus::Planned, $period->fresh()->status);
        $this->assertSame('202451 - PREGRADO S-II OCT 24 - MAR 25', $period->label);
    }

    #[Test]
    public function no_acepta_el_estado_desde_el_formulario(): void
    {
        $this->post('/admin/academic-periods', $this->validData(['status' => 'closed']));

        $this->assertSame(PeriodStatus::Planned, AcademicPeriod::sole()->fresh()->status);
    }

    #[Test]
    public function rechaza_fecha_fin_anterior_al_inicio(): void
    {
        $this->post('/admin/academic-periods', $this->validData(['end_date' => '2024-09-01']))
            ->assertSessionHasErrors('end_date');
    }

    #[Test]
    public function rechaza_codigo_duplicado(): void
    {
        AcademicPeriod::factory()->create(['code' => '202451']);

        $this->post('/admin/academic-periods', $this->validData())->assertSessionHasErrors('code');
    }

    #[Test]
    public function abre_y_cierra_el_periodo_en_orden(): void
    {
        $period = AcademicPeriod::factory()->create();

        $this->patch("/admin/academic-periods/{$period->id}/status", ['status' => 'open'])->assertSessionHasNoErrors();
        $this->assertSame(PeriodStatus::Open, $period->fresh()->status);

        $this->patch("/admin/academic-periods/{$period->id}/status", ['status' => 'closed'])->assertSessionHasNoErrors();
        $this->assertSame(PeriodStatus::Closed, $period->fresh()->status);
    }

    #[Test]
    public function no_permite_reabrir_un_periodo_cerrado(): void
    {
        $period = AcademicPeriod::factory()->closed()->create();

        $this->patch("/admin/academic-periods/{$period->id}/status", ['status' => 'open'])
            ->assertSessionHasErrors('general');

        $this->assertSame(PeriodStatus::Closed, $period->fresh()->status);
    }

    #[Test]
    public function no_permite_saltar_de_planificado_a_cerrado(): void
    {
        $period = AcademicPeriod::factory()->create();

        $this->patch("/admin/academic-periods/{$period->id}/status", ['status' => 'closed'])
            ->assertSessionHasErrors('general');

        $this->assertSame(PeriodStatus::Planned, $period->fresh()->status);
    }

    #[Test]
    public function el_docente_no_puede_cambiar_el_estado(): void
    {
        $period = AcademicPeriod::factory()->create();

        $this->actingAs(User::factory()->teacher()->create())
            ->patch("/admin/academic-periods/{$period->id}/status", ['status' => 'open'])
            ->assertForbidden();
    }
}
