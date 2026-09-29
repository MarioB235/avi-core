<?php

namespace Tests\Unit\Support;

use App\Support\ComparacionHonestaPulso;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ComparacionHonestaPulsoTest extends TestCase
{
    #[DataProvider('deltaPctProvider')]
    public function test_delta_huevos_pct_res07(int $hoy, int $ayer, bool $d03Completo, ?float $esperado): void
    {
        $this->assertSame(
            $esperado,
            ComparacionHonestaPulso::deltaHuevosPct($hoy, $ayer, $d03Completo),
        );
    }

    /**
     * @return array<string, array{int, int, bool, ?float}>
     */
    public static function deltaPctProvider(): array
    {
        return [
            'dia completo y base ayer' => [250, 200, true, 25.0],
            'denominador cero' => [100, 0, true, null],
            'dia en curso' => [250, 200, false, null],
            'sin huevos hoy con ayer' => [0, 200, true, -100.0],
        ];
    }

    public function test_motivo_dia_en_curso(): void
    {
        $this->assertSame(
            ComparacionHonestaPulso::MOTIVO_DIA_EN_CURSO,
            ComparacionHonestaPulso::motivoPctNoCalculable(300, 200, false),
        );
    }

    public function test_motivo_sin_base_ayer(): void
    {
        $this->assertSame(
            ComparacionHonestaPulso::MOTIVO_SIN_BASE_AYER,
            ComparacionHonestaPulso::motivoPctNoCalculable(50, 0, false),
        );
    }
}
