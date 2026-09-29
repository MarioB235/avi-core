<?php

namespace App\Support;

/**
 * Mitiga inyección de fórmulas al abrir Excel (REP-09; usado en REP-03+).
 */
final class ExcelExportSeguro
{
    /** @var list<string> */
    private const PREFIJOS_PELIGROSOS = ['=', '+', '-', '@', '|', "\t", "\r", "\n"];

    public static function texto(?string $valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        $primero = $valor[0];

        if (in_array($primero, self::PREFIJOS_PELIGROSOS, true)) {
            return "'".$valor;
        }

        return $valor;
    }
}
