<?php

namespace App\Support;

use App\Models\Empresa;
use App\Services\EmpresaHuevosUnidad;

final class HuevosUnidad
{
    public const HUEVOS_POR_MAPLE = EmpresaConfiguracion::DEFAULT_HUEVOS_POR_MAPLE;

    public const MAPLES_POR_CAJA = EmpresaConfiguracion::DEFAULT_MAPLES_POR_CAJON;

    public const HUEVOS_POR_CAJA = self::HUEVOS_POR_MAPLE * self::MAPLES_POR_CAJA;

    public static function para(?Empresa $empresa = null): EmpresaHuevosUnidad
    {
        return $empresa !== null
            ? EmpresaHuevosUnidad::for($empresa)
            : EmpresaHuevosUnidad::defaults();
    }

    public static function maplesDesdeHuevos(int $huevos, ?Empresa $empresa = null): int
    {
        return self::para($empresa)->maplesDesdeHuevos($huevos);
    }

    /**
     * @return array{cajas: int, maples: int, huevos: int}
     */
    public static function desgloseDesdeHuevos(int $huevos, ?Empresa $empresa = null): array
    {
        return self::para($empresa)->desgloseDesdeHuevos($huevos);
    }

    public static function etiquetaCompacta(int $huevos, ?Empresa $empresa = null): string
    {
        return self::para($empresa)->etiquetaCompacta($huevos);
    }

    public static function etiquetaSoloCajasMaples(int $huevos, ?Empresa $empresa = null): string
    {
        return self::para($empresa)->etiquetaSoloCajasMaples($huevos);
    }
}
