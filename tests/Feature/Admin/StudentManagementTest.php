<?php

namespace Tests\Feature\Admin;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    #[Test]
    public function crea_estudiante_con_id_como_texto_normalizado(): void
    {
        $this->post('/admin/students', [
            'institutional_id' => ' l00423704 ', 'first_names' => 'Ana María', 'last_names' => 'Pérez Ruiz',
            'email' => ' ANA@Demo.TEST ', 'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('students', ['institutional_id' => 'L00423704', 'email' => 'ana@demo.test']);
    }

    #[Test]
    public function conserva_ceros_iniciales_del_id(): void
    {
        $this->post('/admin/students', ['institutional_id' => '000123', 'first_names' => 'A', 'last_names' => 'B']);

        $this->assertSame('000123', Student::sole()->institutional_id);
    }

    #[Test]
    public function rechaza_id_institucional_duplicado(): void
    {
        Student::factory()->create(['institutional_id' => 'L00423704']);

        $this->post('/admin/students', ['institutional_id' => 'l00423704', 'first_names' => 'A', 'last_names' => 'B'])
            ->assertSessionHasErrors('institutional_id');
    }

    #[Test]
    public function el_correo_es_opcional_pero_debe_ser_valido(): void
    {
        $this->post('/admin/students', ['institutional_id' => 'L1', 'first_names' => 'A', 'last_names' => 'B'])
            ->assertSessionHasNoErrors();

        $this->post('/admin/students', ['institutional_id' => 'L2', 'first_names' => 'A', 'last_names' => 'B', 'email' => 'no-es-correo'])
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function rechaza_borrar_estudiante_matriculado(): void
    {
        $enrollment = Enrollment::factory()->create();

        $this->delete("/admin/students/{$enrollment->student_id}")->assertSessionHasErrors('general');

        $this->assertDatabaseHas('students', ['id' => $enrollment->student_id]);
    }
}
