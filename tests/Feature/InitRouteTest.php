<?php

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InitRouteTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'abcdefghijklmnopqrstuvwxyz0123456789ABCD';

    #[Test]
    public function desactivada_si_no_hay_token_configurado(): void
    {
        config(['app.init_token' => null]);

        $this->get('/init?token=')->assertNotFound();
    }

    #[Test]
    public function desactivada_si_el_token_configurado_es_corto(): void
    {
        config(['app.init_token' => 'corto']);

        $this->get('/init?token=corto')->assertNotFound();
    }

    #[Test]
    public function token_incorrecto_o_ausente_responde_404(): void
    {
        config(['app.init_token' => self::TOKEN]);

        $this->get('/init')->assertNotFound();
        $this->get('/init?token=otro-token-cualquiera-de-32-caracteres!!')->assertNotFound();
    }

    #[Test]
    public function con_token_valido_ejecuta_solo_los_comandos_fijos(): void
    {
        config(['app.init_token' => self::TOKEN]);

        $called = [];
        Artisan::shouldReceive('call')->andReturnUsing(function ($command) use (&$called) {
            $called[] = $command;

            return 0;
        });
        Artisan::shouldReceive('output')->andReturn('ok');

        $this->get('/init?token='.self::TOKEN.'&command=db:wipe')
            ->assertOk()
            ->assertSee('Inicialización completa');

        $this->assertSame(['config:clear', 'migrate', 'optimize:clear', 'route:cache', 'view:cache'], $called);
    }

    #[Test]
    public function no_usa_sesion_para_funcionar_sin_tablas(): void
    {
        $route = app('router')->getRoutes()->getByName('init');
        $middleware = app('router')->gatherRouteMiddleware($route);

        $this->assertNotContains(StartSession::class, $middleware);
        $this->assertContains(SecurityHeaders::class, $middleware);
        $this->assertNotSame('database', config('cache.limiter'));
    }

    #[Test]
    public function seed_es_opcional(): void
    {
        config(['app.init_token' => self::TOKEN]);

        $called = [];
        Artisan::shouldReceive('call')->andReturnUsing(function ($command) use (&$called) {
            $called[] = $command;

            return 0;
        });
        Artisan::shouldReceive('output')->andReturn('');

        $this->get('/init?token='.self::TOKEN.'&seed=1')->assertOk();

        $this->assertContains('db:seed', $called);
    }

    #[Test]
    public function se_detiene_ante_un_error_sin_mostrar_trazas(): void
    {
        config(['app.init_token' => self::TOKEN]);

        Artisan::shouldReceive('call')->andReturnUsing(function ($command) {
            if ($command === 'migrate') {
                throw new \RuntimeException('Fallo de conexión simulado');
            }

            return 0;
        });
        Artisan::shouldReceive('output')->andReturn('');

        $this->get('/init?token='.self::TOKEN)
            ->assertStatus(500)
            ->assertSee('Fallo de conexión simulado')
            ->assertDontSee('route:cache')
            ->assertDontSee('#0 ');
    }
}
