<?php

namespace Tests\Feature\Admin;

use App\Models\CourseOffering;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    #[Test]
    public function crea_asignatura_normalizando_el_codigo(): void
    {
        $this->post('/admin/subjects', ['code' => '  comp-a0g11 ', 'name' => ' Programación   Web ', 'is_active' => '1'])
            ->assertRedirect('/admin/subjects')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('subjects', ['code' => 'COMP-A0G11', 'name' => 'Programación Web']);
    }

    #[Test]
    public function rechaza_codigo_duplicado_que_solo_difiere_en_mayusculas(): void
    {
        Subject::factory()->create(['code' => 'COMP-A0G11']);

        $this->post('/admin/subjects', ['code' => 'comp-a0g11', 'name' => 'Otra'])
            ->assertSessionHasErrors('code');

        $this->assertDatabaseCount('subjects', 1);
    }

    #[Test]
    public function valida_campos_obligatorios(): void
    {
        $this->post('/admin/subjects', [])->assertSessionHasErrors(['code', 'name']);
    }

    #[Test]
    public function actualiza_conservando_su_propio_codigo(): void
    {
        $subject = Subject::factory()->create(['code' => 'COMP-1']);

        $this->put("/admin/subjects/{$subject->id}", ['code' => 'COMP-1', 'name' => 'Nuevo nombre', 'is_active' => '0'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('subjects', ['id' => $subject->id, 'name' => 'Nuevo nombre', 'is_active' => false]);
    }

    #[Test]
    public function borra_asignatura_sin_dependencias(): void
    {
        $subject = Subject::factory()->create();

        $this->delete("/admin/subjects/{$subject->id}")->assertRedirect('/admin/subjects');

        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
    }

    #[Test]
    public function rechaza_borrar_asignatura_con_ofertas_con_mensaje_comprensible(): void
    {
        $offering = CourseOffering::factory()->create();

        $this->from('/admin/subjects')->delete("/admin/subjects/{$offering->subject_id}")
            ->assertRedirect('/admin/subjects')
            ->assertSessionHasErrors('general');

        $this->assertDatabaseHas('subjects', ['id' => $offering->subject_id]);
    }

    #[Test]
    public function la_busqueda_no_distingue_mayusculas(): void
    {
        Subject::factory()->create(['code' => 'COMP-A0G11', 'name' => 'Programación Orientada a Objetos']);
        Subject::factory()->create(['code' => 'MATE-1', 'name' => 'Cálculo']);

        $this->get('/admin/subjects?q=orientada')
            ->assertSee('COMP-A0G11')
            ->assertDontSee('MATE-1');
    }
}
