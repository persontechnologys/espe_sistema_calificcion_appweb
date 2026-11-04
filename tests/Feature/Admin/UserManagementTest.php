<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\CourseOffering;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->actingAs($this->admin);
    }

    private function teacherData(array $overrides = []): array
    {
        return [
            'role' => 'teacher', 'institutional_id' => 'l00012345', 'name' => 'Docente Demo',
            'email' => 'Docente@Demo.test', 'password' => 'Secreta123', 'password_confirmation' => 'Secreta123',
            'is_active' => '1', ...$overrides,
        ];
    }

    #[Test]
    public function crea_docente_con_contrasena_hasheada(): void
    {
        $this->post('/admin/users', $this->teacherData())->assertSessionHasNoErrors();

        $user = User::where('email', 'docente@demo.test')->sole();
        $this->assertSame(UserRole::Teacher, $user->role);
        $this->assertSame('L00012345', $user->institutional_id);
        $this->assertNotSame('Secreta123', $user->password);
        $this->assertTrue(Hash::check('Secreta123', $user->password));
    }

    #[Test]
    public function docente_sin_id_institucional_es_rechazado(): void
    {
        $this->post('/admin/users', $this->teacherData(['institutional_id' => '']))
            ->assertSessionHasErrors('institutional_id');
    }

    #[Test]
    public function administrador_puede_no_tener_id_institucional(): void
    {
        $this->post('/admin/users', $this->teacherData(['role' => 'admin', 'institutional_id' => '']))
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function rechaza_id_institucional_de_docente_duplicado(): void
    {
        User::factory()->teacher()->create(['institutional_id' => 'L00012345']);

        $this->post('/admin/users', $this->teacherData())->assertSessionHasErrors('institutional_id');
    }

    #[Test]
    public function rechaza_rol_desconocido(): void
    {
        $this->post('/admin/users', $this->teacherData(['role' => 'superuser']))->assertSessionHasErrors('role');
    }

    #[Test]
    public function exige_contrasena_robusta_y_confirmada(): void
    {
        $this->post('/admin/users', $this->teacherData(['password' => 'corta', 'password_confirmation' => 'corta']))
            ->assertSessionHasErrors('password');
        $this->post('/admin/users', $this->teacherData(['password_confirmation' => 'Otra12345']))
            ->assertSessionHasErrors('password');
    }

    #[Test]
    public function el_administrador_restablece_la_contrasena(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->put("/admin/users/{$teacher->id}", $this->teacherData([
            'email' => $teacher->email, 'institutional_id' => $teacher->institutional_id,
            'password' => 'Nueva12345', 'password_confirmation' => 'Nueva12345',
        ]))->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('Nueva12345', $teacher->fresh()->password));
    }

    #[Test]
    public function editar_sin_contrasena_no_la_cambia(): void
    {
        $teacher = User::factory()->teacher()->create();
        $hash = $teacher->password;

        $this->put("/admin/users/{$teacher->id}", $this->teacherData([
            'email' => $teacher->email, 'institutional_id' => $teacher->institutional_id,
            'password' => '', 'password_confirmation' => '',
        ]))->assertSessionHasNoErrors();

        $this->assertSame($hash, $teacher->fresh()->password);
    }

    #[Test]
    public function el_administrador_no_puede_desactivarse_ni_quitarse_el_rol(): void
    {
        $this->put("/admin/users/{$this->admin->id}", [
            'role' => 'teacher', 'institutional_id' => 'X1', 'name' => 'A', 'email' => $this->admin->email, 'is_active' => '0',
        ])->assertSessionHasErrors(['is_active', 'role']);
    }

    #[Test]
    public function docente_con_ofertas_no_puede_cambiar_de_rol(): void
    {
        $offering = CourseOffering::factory()->create();
        $teacher = $offering->teacher()->first();

        $this->put("/admin/users/{$teacher->id}", $this->teacherData([
            'role' => 'admin', 'email' => $teacher->email, 'institutional_id' => $teacher->institutional_id,
            'password' => '', 'password_confirmation' => '',
        ]))->assertSessionHasErrors('role');
    }

    #[Test]
    public function rechaza_borrar_docente_con_ofertas(): void
    {
        $offering = CourseOffering::factory()->create();

        $this->delete("/admin/users/{$offering->teacher_id}")->assertSessionHasErrors('general');
        $this->assertDatabaseHas('users', ['id' => $offering->teacher_id]);
    }

    #[Test]
    public function filtra_por_rol(): void
    {
        User::factory()->teacher()->create(['name' => 'Docente Visible']);

        $this->get('/admin/users?role=admin')->assertOk()->assertDontSee('Docente Visible');
        $this->get('/admin/users?role=teacher')->assertOk()->assertSee('Docente Visible');
    }
}
