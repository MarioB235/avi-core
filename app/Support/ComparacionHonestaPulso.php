<?php

namespace App\Support;

/**
 * Comparación huevos hoy vs ayer en Inicio (RES-07): % solo con día D03 cerrado y base de ayer.
 */
final class ComparacionHonestaPulso
{
    public const MOTIVO_SIN_HUEVOS_AMBOS = 'sin_huevos_ambos';

    public const MOTIVO_SIN_BASE_AYER = 'sin_base_ayer';

    public const MOTIVO_DIA_EN_CURSO = 'dia_en_curso';

    public static function deltaHuevosPct(int $huevosHoy, int $huevosAyer, bool $capturasD03CompletasHoy): ?float
    {
        if ($huevosAyer <= 0 || ! $capturasD03CompletasHoy) {
            return null;
        }

        return round((($huevosHoy - $huevosAyer) / $huevosAyer) * 100, 1);
    }

    public static function motivoPctNoCalculable(int $huevosHoy, int $huevosAyer, bool $capturasD03CompletasHoy): ?string
    {
        if ($huevosHoy < 1 && $huevosAyer < 1) {
            return self::MOTIVO_SIN_HUEVOS_AMBOS;
        }

        if ($huevosAyer < 1) {
            return self::MOTIVO_SIN_BASE_AYER;
        }

        if (! $capturasD03CompletasHoy) {
            return self::MOTIVO_DIA_EN_CURSO;
        }

        return null;
    }
}
