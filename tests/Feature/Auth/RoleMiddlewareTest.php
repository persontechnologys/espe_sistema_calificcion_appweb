<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'active', 'role:admin'])
            ->get('/_test/solo-admin', fn () => 'ok');
    }

    #[Test]
    public function administrador_accede_a_rutas_de_administracion(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/_test/solo-admin')->assertOk();
    }

    #[Test]
    public function docente_recibe_403_en_rutas_de_administracion(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->get('/_test/solo-admin')->assertForbidden();
    }
}
