<?php

namespace Tests\Unit;

use App\Enums\GradeStatus;
use App\Services\Grades\GradeCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GradeCalculatorTest extends TestCase
{
    private GradeCalculator $calc;

    protected function setUp(): void
    {
        $this->calc = new GradeCalculator;
    }

    #[Test]
    public function aprueba_con_42_exactos(): void
    {
        $r = $this->calc->calculate(['14.00', '14.00', '14.00']);

        $this->assertSame(4200, $r->totalCents);
        $this->assertSame('42,00', $r->totalFormatted());
        $this->assertSame('14,00', $r->averageFormatted());
        $this->assertSame(GradeStatus::Aprobado, $r->status);
    }

    #[Test]
    public function aprueba_aunque_un_parcial_sea_menor_a_14(): void
    {
        // Ejemplo literal de CLAUDE.md §4.2.
        $r = $this->calc->calculate(['10.00', '15.00', '17.00']);

        $this->assertSame('42,00', $r->totalFormatted());
        $this->assertSame('14,00', $r->averageFormatted());
        $this->assertSame(GradeStatus::Aprobado, $r->status);
    }

    #[Test]
    public function reprueba_con_menos_de_42(): void
    {
        $r = $this->calc->calculate(['14.00', '14.00', '13.99']);

        $this->assertSame(GradeStatus::Reprobado, $r->status);
    }

    #[Test]
    public function total_41_99_muestra_promedio_14_00_y_reprueba(): void
    {
        // Caso límite §4.3.1: 41,99 / 3 = 13,9966... se muestra 14,00, pero el estado
        // se decide sobre el total exacto (41,99 < 42,00).
        $r = $this->calc->calculate(['13.99', '14.00', '14.00']);

        $this->assertSame(4199, $r->totalCents);
        $this->assertSame('41,99', $r->totalFormatted());
        $this->assertSame('14,00', $r->averageFormatted());
        $this->assertSame(GradeStatus::Reprobado, $r->status);
    }

    #[Test]
    public function suma_exacta_de_decimales_sin_error_de_punto_flotante(): void
    {
        // En float, 10.1 + 15.2 + 16.7 puede dar 41.999999...
        $r = $this->calc->calculate(['10.10', '15.20', '16.70']);

        $this->assertSame(4200, $r->totalCents);
        $this->assertSame(GradeStatus::Aprobado, $r->status);
    }

    #[Test]
    public function valores_limite_0_y_20(): void
    {
        $max = $this->calc->calculate(['20', '20.00', '20']);
        $this->assertSame('60,00', $max->totalFormatted());
        $this->assertSame('20,00', $max->averageFormatted());
        $this->assertSame(GradeStatus::Aprobado, $max->status);

        $min = $this->calc->calculate(['0', '0.00', '0']);
        $this->assertSame('0,00', $min->totalFormatted());
        $this->assertSame(GradeStatus::Reprobado, $min->status);
    }

    #[Test]
    public function cero_es_nota_valida_y_null_es_pendiente(): void
    {
        $conCero = $this->calc->calculate(['20', '20', '0']);
        $this->assertTrue($conCero->isComplete());
        $this->assertSame(GradeStatus::Reprobado, $conCero->status);

        $pendiente = $this->calc->calculate(['20', '20', null]);
        $this->assertFalse($pendiente->isComplete());
        $this->assertSame(GradeStatus::Incompleto, $pendiente->status);
        $this->assertNull($pendiente->totalCents);
        $this->assertNull($pendiente->averageCents);
        $this->assertSame('—', $pendiente->totalFormatted());
        $this->assertSame('—', $pendiente->partialFormatted(2));
    }

    #[Test]
    public function sin_ningun_parcial_es_incompleto(): void
    {
        $r = $this->calc->calculate([null, null, null]);

        $this->assertSame(GradeStatus::Incompleto, $r->status);
    }

    #[Test]
    public function no_redondea_parciales_antes_de_sumar(): void
    {
        // Si se redondeara cada parcial a entero: 14 + 14 + 14 = 42 (aprobado). Correcto: 41,97.
        $r = $this->calc->calculate(['13.99', '13.99', '13.99']);

        $this->assertSame('41,97', $r->totalFormatted());
        $this->assertSame('13,99', $r->averageFormatted());
        $this->assertSame(GradeStatus::Reprobado, $r->status);
    }

    #[Test]
    #[DataProvider('promedios')]
    public function redondeo_de_presentacion_del_promedio(array $partials, string $expected): void
    {
        $this->assertSame($expected, $this->calc->calculate($partials)->averageFormatted());
    }

    public static function promedios(): array
    {
        return [
            '0,01/3 = 0,0033 -> 0,00' => [['0.01', '0', '0'], '0,00'],
            '0,02/3 = 0,0067 -> 0,01' => [['0.02', '0', '0'], '0,01'],
            '50/3 = 16,6667 -> 16,67' => [['20', '20', '10'], '16,67'],
            '41,98/3 = 13,9933 -> 13,99' => [['13.98', '14', '14'], '13,99'],
            '42,01/3 = 14,0033 -> 14,00' => [['14.01', '14', '14'], '14,00'],
        ];
    }

    #[Test]
    #[DataProvider('invalidos')]
    public function rechaza_valores_invalidos(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calc->calculate([$value, '10', '10']);
    }

    public static function invalidos(): array
    {
        return [
            'negativo' => ['-1'],
            'mayor a 20' => ['20.01'],
            'muy grande' => ['100'],
            'tres decimales' => ['10.123'],
            'texto' => ['diez'],
            'coma decimal' => ['10,5'],
            'vacío' => [''],
        ];
    }

    #[Test]
    public function exige_exactamente_tres_parciales(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calc->calculate(['10', '10']);
    }
}
