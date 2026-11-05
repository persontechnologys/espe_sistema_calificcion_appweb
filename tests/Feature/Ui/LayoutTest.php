<?php

namespace Tests\Feature\Ui;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Identidad visual y menú por rol. Ocultar enlaces es solo presentación:
 * la autorización se sigue verificando en el servidor (403).
 */
class LayoutTest extends TestCase
{
    use RefreshDatabase;

    /** Textos visibles sin etiquetas HTML, para buscar palabras en la interfaz. */
    private function visibleText(string $html): string
    {
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html);

        return mb_strtolower(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function assertOwnIdentity(string $html): void
    {
        $text = $this->visibleText($html);

        $this->assertStringContainsString('person technologys', $text);
        $this->assertStringContainsString('https://dc.persontechnologys.com/', $html);
        foreach (['demo', 'demostración', 'oficial'] as $word) {
            $this->assertStringNotContainsString($word, $text, "La interfaz no debe decir «{$word}».");
        }
    }

    #[Test]
    public function el_login_tiene_identidad_propia_sin_textos_de_demostracion(): void
    {
        $this->assertOwnIdentity($this->get('/login')->assertOk()->getContent());
    }

    #[Test]
    public function el_layout_tiene_identidad_propia_sin_textos_de_demostracion(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->assertOwnIdentity($this->get('/dashboard')->assertOk()->getContent());
    }

    public static function adminLinks(): array
    {
        return [
            'períodos' => ['/admin/academic-periods'],
            'asignaturas' => ['/admin/subjects'],
            'ofertas' => ['/admin/course-offerings'],
            'estudiantes' => ['/admin/students'],
            'usuarios' => ['/admin/users'],
            'auditoría' => ['/admin/audit-logs'],
        ];
    }

    #[Test]
    #[DataProvider('adminLinks')]
    public function el_administrador_ve_los_enlaces_de_administracion(string $path): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/dashboard')->assertOk()->assertSee('href="'.url($path).'"', false);
    }

    #[Test]
    #[DataProvider('adminLinks')]
    public function el_docente_no_ve_los_enlaces_de_administracion_y_recibe_403(string $path): void
    {
        $this->actingAs(User::factory()->teacher()->create());

        $this->get('/dashboard')->assertOk()->assertDontSee('href="'.url($path).'"', false);
        $this->get($path)->assertForbidden();
    }

    #[Test]
    public function el_item_activo_del_menu_tiene_aria_current(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $html = $this->get('/admin/students')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#href="'.preg_quote(url('/admin/students'), '#').'"[^>]*aria-current="page"#', $html);
    }

    #[Test]
    public function ninguna_vista_referencia_el_logotipo_oficial(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            $this->assertStringNotContainsString('espe.jpeg', $file->getContents(), $file->getRelativePathname());
        }
    }

    #[Test]
    public function ninguna_vista_imprime_html_sin_escapar(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            $this->assertStringNotContainsString('{!!', $file->getContents(), $file->getRelativePathname());
        }
    }

    #[Test]
    public function el_layout_carga_los_recursos_locales_sin_cdn_ni_scripts_inline(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $html = $this->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('vendor/bootstrap-icons/bootstrap-icons.min.css', $html);
        $this->assertStringContainsString('css/app.css?v=', $html);
        $this->assertStringContainsString('js/app.js?v=', $html);
        $this->assertDoesNotMatchRegularExpression('#<script(?![^>]*\bsrc=)[^>]*>#i', $html, 'Sin scripts inline.');
        $this->assertDoesNotMatchRegularExpression('#\son[a-z]+="#i', $html, 'Sin manejadores inline (onclick…).');
        $this->assertStringNotContainsString('cdn.', $html);
    }
}
