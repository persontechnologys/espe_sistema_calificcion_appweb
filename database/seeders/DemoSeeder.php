<?php

namespace Database\Seeders;

use App\Enums\PeriodStatus;
use App\Enums\UserRole;
use App\Models\AcademicPeriod;
use App\Models\CourseOffering;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\Grades\GradeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Datos de demostración FICTICIOS y controlados (IDs, nombres y NRC inventados).
 * Las notas se registran mediante GradeService para que la auditoría sea real.
 *
 * Casos preparados en NRC 10234 (período 202451, abierto):
 *   42,00 aprobado aunque 1P < 14 · 10,10+15,20+16,70 = 42,00 aprobado · 41,99 reprobado (promedio 14,00)
 *   60,00 · 0,00 como nota válida · incompleto (3P pendiente) · sin registro de notas · corrección auditada
 */
class DemoSeeder extends Seeder
{
    public function __construct(private readonly GradeService $grades) {}

    public function run(): void
    {
        $password = config('app.demo_password');

        if (! is_string($password) || strlen($password) < 8) {
            throw new RuntimeException('Defina DEMO_PASSWORD (mínimo 8 caracteres) en .env antes de ejecutar el seeder.');
        }

        if (User::exists()) {
            $this->command?->warn('La base ya tiene usuarios: DemoSeeder no se ejecuta para no duplicar datos.');

            return;
        }

        DB::transaction(fn () => $this->seed($password));

        $this->command?->info('Datos de demostración creados. Usuarios: admin@demo.test, docente1@demo.test, docente2@demo.test');
    }

    private function seed(string $password): void
    {
        // ----- Usuarios
        $admin = User::create([
            'name' => 'Administración Académica (demo)', 'email' => 'admin@demo.test', 'password' => $password,
            'role' => UserRole::Admin, 'is_active' => true,
        ]);
        $t1 = User::create([
            'name' => 'Carlos Andrade Vela', 'email' => 'docente1@demo.test', 'password' => $password,
            'role' => UserRole::Teacher, 'is_active' => true, 'institutional_id' => 'D00000101',
        ]);
        $t2 = User::create([
            'name' => 'María Fernanda López Mora', 'email' => 'docente2@demo.test', 'password' => $password,
            'role' => UserRole::Teacher, 'is_active' => true, 'institutional_id' => 'D00000102',
        ]);
        User::create([
            'name' => 'Docente Inactivo Demo', 'email' => 'inactivo@demo.test', 'password' => $password,
            'role' => UserRole::Teacher, 'is_active' => false, 'institutional_id' => 'D00000199',
        ]);

        // ----- Períodos (todos se crean "planned" y se abren/cierran después)
        $closed = $this->period('202450', 'PREGRADO S-I ABR 24 - AGO 24', '2024-04-15', '2024-08-31');
        $open = $this->period('202451', 'PREGRADO S-II OCT 24 - MAR 25', '2024-10-07', '2025-03-14');
        $this->period('202550', 'PREGRADO S-I ABR 25 - AGO 25', '2025-04-14', '2025-08-29');

        // ----- Asignaturas
        $poo = Subject::create(['code' => 'COMP-A0G11', 'name' => 'PROGRAMACIÓN ORIENTADA A OBJETOS', 'is_active' => true]);
        $bd = Subject::create(['code' => 'COMP-B0F12', 'name' => 'FUNDAMENTOS DE BASES DE DATOS', 'is_active' => true]);
        Subject::create(['code' => 'MATE-A0C10', 'name' => 'CÁLCULO DIFERENCIAL E INTEGRAL', 'is_active' => true]);

        // ----- Estudiantes ficticios
        $names = [
            ['ALVARADO RUIZ', 'ANDREA SOFÍA'], ['BENÍTEZ CAMPOS', 'JUAN PABLO'], ['CEDEÑO MORA', 'LUIS ALBERTO'],
            ['DÁVILA PAREDES', 'CAMILA ESTEFANÍA'], ['ESPINOZA TORRES', 'DIEGO MARTÍN'], ['FLORES VACA', 'VALERIA ISABEL'],
            ['GUERRERO SALAZAR', 'MATEO ANDRÉS'], ['HIDALGO NARANJO', 'PAULA CRISTINA'], ['IZURIETA LEÓN', 'SEBASTIÁN'],
            ['JARAMILLO PAZ', 'NICOLE ALEJANDRA'],
        ];
        $s = [];
        foreach ($names as $i => [$last, $first]) {
            $s[$i + 1] = Student::create([
                'institutional_id' => sprintf('L00900%03d', $i + 1),
                'last_names' => $last, 'first_names' => $first,
                'email' => $i % 2 === 0 ? sprintf('estudiante%02d@demo.test', $i + 1) : null,
                'is_active' => true,
            ]);
        }

        // ----- Ofertas: el NRC 10234 se repite en dos períodos (único por período)
        $nrcOpen = CourseOffering::create([
            'academic_period_id' => $open->id, 'subject_id' => $poo->id, 'teacher_id' => $t1->id,
            'nrc' => '10234', 'classroom' => 'B-204', 'campus' => 'MATRIZ SANGOLQUÍ (DEMO)',
        ]);
        $nrcOther = CourseOffering::create([
            'academic_period_id' => $open->id, 'subject_id' => $bd->id, 'teacher_id' => $t2->id,
            'nrc' => '10235', 'classroom' => 'LAB-3', 'campus' => 'MATRIZ SANGOLQUÍ (DEMO)',
        ]);
        $nrcClosed = CourseOffering::create([
            'academic_period_id' => $closed->id, 'subject_id' => $poo->id, 'teacher_id' => $t1->id,
            'nrc' => '10234', 'classroom' => 'B-110', 'campus' => 'MATRIZ SANGOLQUÍ (DEMO)',
        ]);

        // ----- Período cerrado: se abre, el docente registra, se cierra y el admin rectifica.
        $this->setStatus($closed, PeriodStatus::Open);
        $this->grade($t1, $this->enroll($s[3], $nrcClosed), ['15', '16', '17.5']);
        $e = $this->enroll($s[4], $nrcClosed);
        $this->grade($t1, $e, ['12', '13', '16']);
        $this->setStatus($closed, PeriodStatus::Closed);
        $this->grades->correct($admin, $e, ['12', '14', '16'], 1, 'Rectificación: error de transcripción del 2P según acta firmada (demo).');

        // ----- Período abierto: casos de la regla de aprobación (NRC 10234, docente 1).
        $this->setStatus($open, PeriodStatus::Open);
        $this->grade($t1, $this->enroll($s[1], $nrcOpen), ['10', '15', '17']);        // 42,00 APROBADO con 1P < 14
        $this->grade($t1, $this->enroll($s[2], $nrcOpen), ['10.10', '15.20', '16.70']); // 42,00 exacto APROBADO
        $this->grade($t1, $this->enroll($s[3], $nrcOpen), ['13.99', '14', '14']);     // 41,99 REPROBADO (promedio 14,00)
        $this->grade($t1, $this->enroll($s[4], $nrcOpen), ['20', '20', '20']);        // 60,00 APROBADO
        $this->grade($t1, $this->enroll($s[5], $nrcOpen), ['0', '12', '15']);         // 0 es nota válida: 27,00 REPROBADO
        $this->grade($t1, $this->enroll($s[6], $nrcOpen), ['12', '14', null]);        // INCOMPLETO (3P pendiente)
        $this->enroll($s[7], $nrcOpen);                                                // INCOMPLETO (sin registro)
        $e = $this->enroll($s[8], $nrcOpen);
        $this->grade($t1, $e, ['16', '16', '9.99']);                                  // 41,99 …
        $this->grades->correct($admin, $e, ['16', '16', '10'], 1, 'El docente reportó error de digitación en el 3P (demo).'); // … corregido a 42,00
        // s9 y s10 quedan sin matricular en 10234 para la matrícula en vivo.

        // ----- Oferta de la docente 2 (el docente 1 no tiene acceso).
        $this->grade($t2, $this->enroll($s[1], $nrcOther), ['18', '17.5', '19']);
        $this->grade($t2, $this->enroll($s[9], $nrcOther), ['11', '12', null]);
    }

    private function period(string $code, string $description, string $start, string $end): AcademicPeriod
    {
        return AcademicPeriod::create(['code' => $code, 'description' => $description, 'start_date' => $start, 'end_date' => $end]);
    }

    private function setStatus(AcademicPeriod $period, PeriodStatus $status): void
    {
        $period->status = $status;
        $period->save();
    }

    private function enroll(Student $student, CourseOffering $offering): Enrollment
    {
        return Enrollment::create(['student_id' => $student->id, 'course_offering_id' => $offering->id]);
    }

    private function grade(User $teacher, Enrollment $enrollment, array $partials): void
    {
        $this->grades->register($teacher, $enrollment, $partials, 0);
    }
}
