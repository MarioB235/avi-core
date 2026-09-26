<?php

namespace App\Services;

use App\Models\Empresa;
use App\Support\EmpresaConfiguracion;

class EmpresaHuevosUnidad
{
    public function __construct(private EmpresaConfiguracion $configuracion) {}

    public static function for(Empresa $empresa): self
    {
        return new self(EmpresaConfiguracion::fromEmpresa($empresa));
    }

    public static function defaults(): self
    {
        return new self(new EmpresaConfiguracion(
            zonaHoraria: EmpresaConfiguracion::DEFAULT_ZONA_HORARIA,
            huevosPorMaple: EmpresaConfiguracion::DEFAULT_HUEVOS_POR_MAPLE,
            maplesPorCajon: EmpresaConfiguracion::DEFAULT_MAPLES_POR_CAJON,
        ));
    }

    public function configuracion(): EmpresaConfiguracion
    {
        return $this->configuracion;
    }

    public function maplesDesdeHuevos(int $huevos): int
    {
        return intdiv(max(0, $huevos), $this->configuracion->huevosPorMaple);
    }

    /**
     * @return array{cajas: int, maples: int, huevos: int}
     */
    public function desgloseDesdeHuevos(int $huevos): array
    {
        $huevos = max(0, $huevos);
        $maples = $this->maplesDesdeHuevos($huevos);
        $maplesPorCajon = $this->configuracion->maplesPorCajon;
        $cajas = intdiv($maples, $maplesPorCajon);
        $maplesResto = $maples % $maplesPorCajon;
        $huevosResto = $huevos % $this->configuracion->huevosPorMaple;

        return [
            'cajas' => $cajas,
            'maples' => $maplesResto,
            'huevos' => $huevosResto,
        ];
    }

    public function etiquetaCompacta(int $huevos): string
    {
        if ($huevos < 1) {
            return '0 huevos';
        }

        $partes = [number_format($huevos, 0, ',', '.').' huevos'];
        $desglose = $this->desgloseDesdeHuevos($huevos);

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

    public function etiquetaSoloCajasMaples(int $huevos): string
    {
        if ($huevos < 1) {
            return '0 maples';
        }

        $desglose = $this->desgloseDesdeHuevos($huevos);
        $partes = [];

        if ($desglose['cajas'] > 0) {
            $partes[] = $desglose['cajas'].' '.($desglose['cajas'] === 1 ? 'caja' : 'cajas');
        }

        if ($desglose['maples'] > 0) {
            $partes[] = $desglose['maples'].' '.($desglose['maples'] === 1 ? 'maple' : 'maples');
        }

        if ($desglose['huevos'] > 0) {
            $partes[] = $desglose['huevos'].' '.($desglose['huevos'] === 1 ? 'huevo suelto' : 'huevos sueltos');
        }

        if ($partes === []) {
            return '0 maples';
        }

        return implode(' + ', $partes);
    }
}
