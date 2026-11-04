<?php

namespace Tests\Feature;

use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function envia_cabeceras_de_seguridad(): void
    {
        $this->get('/login')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    #[Test]
    public function escapa_datos_del_usuario_en_las_vistas(): void
    {
        $teacher = User::factory()->teacher()->create();
        $offering = CourseOffering::factory()->create([
            'teacher_id' => $teacher->id, 'academic_period_id' => AcademicPeriod::factory()->open()->create()->id,
        ]);
        $student = Student::factory()->create(['last_names' => '<script>alert(1)</script>']);
        Enrollment::factory()->create(['student_id' => $student->id, 'course_offering_id' => $offering->id]);

        $this->actingAs($teacher)->get("/reports/offerings/{$offering->id}")
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;SCRIPT&gt;ALERT(1)&lt;/SCRIPT&gt;', false);
    }

    #[Test]
    public function la_busqueda_no_permite_inyeccion_sql(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        Student::factory()->create(['last_names' => 'PÉREZ']);

        $this->get('/admin/students?q='.urlencode("' OR 1=1 --"))->assertOk()->assertDontSee('PÉREZ');
        $this->get('/admin/students?q=%25')->assertOk()->assertDontSee('PÉREZ'); // % se escapa, no es comodín
    }

    #[Test]
    public function la_pagina_403_esta_en_espanol(): void
    {
        $this->actingAs(User::factory()->teacher()->create())->get('/admin/users')
            ->assertForbidden()->assertSee('Acceso denegado');
    }
}
