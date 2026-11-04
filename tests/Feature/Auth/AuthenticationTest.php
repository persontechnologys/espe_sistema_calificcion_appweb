<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function muestra_el_formulario_de_inicio_de_sesion(): void
    {
        $this->get('/login')->assertOk()->assertSee('Iniciar sesión', false);
    }

    #[Test]
    public function invitado_es_redirigido_al_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    #[Test]
    public function inicia_sesion_con_credenciales_validas(): void
    {
        $user = User::factory()->admin()->create(['email' => 'admin@demo.test']);

        // El correo se normaliza (mayúsculas/espacios).
        $this->post('/login', ['email' => '  ADMIN@demo.test ', 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function rechaza_contrasena_incorrecta(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'incorrecta'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => __('auth.inactive')]);

        $this->assertGuest();
    }

    #[Test]
    public function usuario_desactivado_con_sesion_abierta_pierde_el_acceso(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk();

        $user->update(['is_active' => false]);

        $this->actingAs($user->fresh())->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    #[Test]
    public function limita_los_intentos_de_inicio_de_sesion(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'mal']);
        }

        // Ni siquiera la contraseña correcta entra mientras dura el bloqueo.
        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Demasiados intentos', session('errors')->first('email'));
        $this->assertGuest();
    }

    #[Test]
    public function cierra_sesion_e_invalida_la_sesion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }

    #[Test]
    public function logout_por_get_no_esta_permitido(): void
    {
        $this->actingAs(User::factory()->create())->get('/logout')->assertMethodNotAllowed();
    }
}
