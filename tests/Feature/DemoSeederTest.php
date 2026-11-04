<?php

namespace Tests\Feature;

use App\Enums\AuditOperation;
use App\Enums\GradeStatus;
use App\Models\AuditLog;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function exige_contrasena_de_demo(): void
    {
        config(['app.demo_password' => null]);

        $this->expectException(RuntimeException::class);
        $this->seed(DemoSeeder::class);
    }

    #[Test]
    public function crea_los_casos_de_la_demostracion(): void
    {
        config(['app.demo_password' => 'Demo12345']);
        $this->seed(DemoSeeder::class);

        $offering = CourseOffering::whereHas('academicPeriod', fn ($q) => $q->where('code', '202451'))->where('nrc', '10234')->sole();
        $byId = Enrollment::with(['student', 'grade'])->where('course_offering_id', $offering->id)->get()
            ->keyBy(fn ($e) => $e->student->institutional_id)
            ->map(fn ($e) => Grade::resultFor($e->grade));

        $this->assertSame(['42,00', GradeStatus::Aprobado], [$byId['L00900001']->totalFormatted(), $byId['L00900001']->status]);
        $this->assertSame(['42,00', GradeStatus::Aprobado], [$byId['L00900002']->totalFormatted(), $byId['L00900002']->status]);
        $this->assertSame(['41,99', '14,00', GradeStatus::Reprobado], [$byId['L00900003']->totalFormatted(), $byId['L00900003']->averageFormatted(), $byId['L00900003']->status]);
        $this->assertSame(GradeStatus::Incompleto, $byId['L00900006']->status);
        $this->assertSame(GradeStatus::Incompleto, $byId['L00900007']->status);
        $this->assertSame(GradeStatus::Aprobado, $byId['L00900008']->status);

        // Correcciones auditadas (una en período abierto y otra en período cerrado).
        $this->assertSame(2, AuditLog::where('operation', AuditOperation::Correccion)->count());

        // El usuario de demo puede iniciar sesión con la contraseña de .env.
        $this->post('/login', ['email' => 'docente1@demo.test', 'password' => 'Demo12345'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs(User::where('email', 'docente1@demo.test')->sole());
    }

    #[Test]
    public function no_duplica_datos_si_se_ejecuta_dos_veces(): void
    {
        config(['app.demo_password' => 'Demo12345']);
        $this->seed(DemoSeeder::class);
        $this->seed(DemoSeeder::class);

        $this->assertSame(4, User::count());
    }
}
