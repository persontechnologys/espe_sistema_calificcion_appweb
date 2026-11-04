<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public static function adminPages(): array
    {
        return [
            ['/admin/subjects'], ['/admin/subjects/create'],
            ['/admin/academic-periods'], ['/admin/academic-periods/create'],
            ['/admin/students'], ['/admin/students/create'],
            ['/admin/users'], ['/admin/users/create'],
            ['/admin/course-offerings'], ['/admin/course-offerings/create'],
        ];
    }

    #[Test]
    #[DataProvider('adminPages')]
    public function el_administrador_accede(string $url): void
    {
        $this->actingAs(User::factory()->admin()->create())->get($url)->assertOk();
    }

    #[Test]
    #[DataProvider('adminPages')]
    public function el_docente_recibe_403(string $url): void
    {
        $this->actingAs(User::factory()->teacher()->create())->get($url)->assertForbidden();
    }

    #[Test]
    public function el_docente_no_puede_crear_registros_por_post_directo(): void
    {
        $this->actingAs(User::factory()->teacher()->create())
            ->post('/admin/subjects', ['code' => 'X-1', 'name' => 'X'])
            ->assertForbidden();

        $this->assertDatabaseCount('subjects', 0);
    }
}
