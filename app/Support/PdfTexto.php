<?php

namespace App\Support;

/**
 * Texto seguro para fuentes core FPDF (ISO-8859-1).
 */
final class PdfTexto
{
    /**
     * Texto de usuario en PDF: sin bytes de control y recorte para celdas.
     */
    public static function usuario(string $texto, int $maximo = 240): string
    {
        $texto = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $texto) ?? $texto;

        return self::latin1Recortado($texto, $maximo);
    }

    public static function latin1(string $texto): string
    {
        $convertido = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $texto);

        if ($convertido === false) {
            return preg_replace('/[^\x20-\x7E]/', '?', $texto) ?? $texto;
        }

        return $convertido;
    }

    public static function latin1Recortado(string $texto, int $maximo = 240): string
    {
        if (mb_strlen($texto) > $maximo) {
            $texto = mb_substr($texto, 0, $maximo - 1).'…';
        }

        return self::latin1($texto);
    }
}
