<?php

namespace App\Support;

final class HuevosUnidad
{
    public const HUEVOS_POR_MAPLE = 30;

    public const MAPLES_POR_CAJA = 12;

    public const HUEVOS_POR_CAJA = self::HUEVOS_POR_MAPLE * self::MAPLES_POR_CAJA;

    public static function maplesDesdeHuevos(int $huevos): int
    {
        return intdiv(max(0, $huevos), self::HUEVOS_POR_MAPLE);
    }

    /**
     * @return array{cajas: int, maples: int, huevos: int}
     */
    public static function desgloseDesdeHuevos(int $huevos): array
    {
        $huevos = max(0, $huevos);
        $maples = self::maplesDesdeHuevos($huevos);
        $cajas = intdiv($maples, self::MAPLES_POR_CAJA);
        $maplesResto = $maples % self::MAPLES_POR_CAJA;
        $huevosResto = $huevos % self::HUEVOS_POR_MAPLE;

        return [
            'cajas' => $cajas,
            'maples' => $maplesResto,
            'huevos' => $huevosResto,
        ];
    }

    public static function etiquetaCompacta(int $huevos): string
    {
        if ($huevos < 1) {
            return '0 huevos';
        }

        $partes = [number_format($huevos, 0, ',', '.').' huevos'];
        $desglose = self::desgloseDesdeHuevos($huevos);

        $unidades = [];

        if ($desglose['cajas'] > 0) {
            $unidades[] = $desglose['cajas'].' '.($desglose['cajas'] === 1 ? 'caja' : 'cajas');
        }

        if ($desglose['maples'] > 0) {
            $unidades[] = $desglose['maples'].' '.($desglose['maples'] === 1 ? 'maple' : 'maples');
        }

        if ($desglose['huevos'] > 0) {
            $unidades[] = $desglose['huevos'].' '.($desglose['huevos'] === 1 ? 'huevo' : 'huevos');
        }

        if ($unidades !== []) {
            $partes[] = implode(' · ', $unidades);
        }

        return implode(' — ', $partes);
    }

    public static function etiquetaSoloCajasMaples(int $huevos): string
    {
        if ($huevos < 1) {
            return '0 maples';
        }

        $desglose = self::desgloseDesdeHuevos($huevos);
        $partes = [];

        if ($desglose['cajas'] > 0) {
            $partes[] = $desglose['cajas'].' '.($desglose['cajas'] === 1 ? 'caja' : 'cajas');
        }

        if ($desglose['maples'] > 0) {
            $partes[] = $desglose['maples'].' '.($desglose['maples'] === 1 ? 'maple' : 'maples');
        }

        if ($partes === [] && $desglose['huevos'] > 0) {
            return $desglose['huevos'].' '.($desglose['huevos'] === 1 ? 'huevo suelto' : 'huevos sueltos');
        }

        return implode(' + ', $partes);
    }
}
