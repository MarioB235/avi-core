<?php

namespace App\Support;

use App\Enums\RegistroOperativoTipo;
use App\Models\RegistroOperativo;
use Illuminate\Database\Eloquent\Collection;

/**
 * Celdas de la semana operativa en Resumen (RES-08): omisión ≠ cero confirmado.
 */
final class ResumenSemanaOperativa
{
    /**
     * @param  list<int>  $galponIds
     * @param  Collection<int, RegistroOperativo>  $registrosDia
     * @return array{value: int|null, estado: string, display: string}
     */
    public static function celdaHuevosAptos(Collection $registrosDia, array $galponIds): array
    {
        return self::celdaPorTipoProductivo(
            $registrosDia,
            $galponIds,
            RegistroOperativoTipo::Huevos,
            fn (Collection $regs): int => (int) $regs->sum('huevos'),
        );
    }

    /**
     * @param  list<int>  $galponIds
     * @param  Collection<int, RegistroOperativo>  $registrosDia
     * @return array{value: int|null, estado: string, display: string}
     */
    public static function celdaHuevosDescarte(Collection $registrosDia, array $galponIds): array
    {
        return self::celdaPorTipoProductivo(
            $registrosDia,
            $galponIds,
            RegistroOperativoTipo::Huevos,
            fn (Collection $regs): int => (int) $regs->sum('huevos_descarte'),
        );
    }

    /**
     * @param  list<int>  $galponIds
     * @param  Collection<int, RegistroOperativo>  $registrosDia
     * @return array{value: int|null, estado: string, display: string}
     */
    public static function celdaMuertes(Collection $registrosDia, array $galponIds): array
    {
        return self::celdaPorTipoProductivo(
            $registrosDia,
            $galponIds,
            RegistroOperativoTipo::Muertes,
            fn (Collection $regs): int => (int) $regs->sum('muertes'),
        );
    }

    /**
     * @param  list<int>  $galponIds
     * @param  Collection<int, RegistroOperativo>  $registrosDia
     * @return array{value: int|null, estado: string, display: string}
     */
    public static function celdaDescarteAves(Collection $registrosDia, array $galponIds): array
    {
        return self::celdaPorTipoProductivo(
            $registrosDia,
            $galponIds,
            RegistroOperativoTipo::Descarte,
            fn (Collection $regs): int => (int) $regs->sum('descarte_aves'),
        );
    }

    /**
     * @param  list<int>  $galponIds
     * @param  Collection<int, RegistroOperativo>  $registrosDia
     * @return array{value: float|null, estado: string, display: string}
     */
    public static function celdaAlimentoKg(Collection $registrosDia, array $galponIds): array
    {
        $total = 0.0;
        $tieneRegistro = false;

        foreach ($galponIds as $galponId) {
            $regs = $registrosDia
                ->where('galpon_id', $galponId)
                ->where('tipo', RegistroOperativoTipo::Alimento);

            if ($regs->isEmpty()) {
                continue;
            }

            $tieneRegistro = true;
            $total += (float) $regs->sum('alimento_kg');
        }

        if (! $tieneRegistro) {
            return self::omision();
        }

        return [
            'value' => $total,
            'estado' => CapturaCeroEstado::REGISTRADO,
            'display' => self::formatearKg($total),
        ];
    }

    /**
     * @param  list<int>  $galponIds
     * @param  Collection<int, RegistroOperativo>  $registrosDia
     * @param  callable(Collection): (int|float)  $sumarGalpon
     * @return array{value: int|null, estado: string, display: string}
     */
    private static function celdaPorTipoProductivo(
        Collection $registrosDia,
        array $galponIds,
        RegistroOperativoTipo $tipo,
        callable $sumarGalpon,
    ): array {
        $total = 0;

        foreach ($galponIds as $galponId) {
            $regs = $registrosDia
                ->where('galpon_id', $galponId)
                ->where('tipo', $tipo);

            $estadoGalpon = CapturaCeroEstado::resolverEstadoDia($regs, $tipo);

            if ($estadoGalpon === CapturaCeroEstado::OMISION) {
                return self::omision();
            }

            $total += (int) $sumarGalpon($regs);
        }

        if ($total > 0) {
            return [
                'value' => $total,
                'estado' => CapturaCeroEstado::REGISTRADO,
                'display' => number_format($total, 0, ',', '.'),
            ];
        }

        return [
            'value' => 0,
            'estado' => CapturaCeroEstado::CERO_CONFIRMADO,
            'display' => '0 (confirmado)',
        ];
    }

    /**
     * @return array{value: null, estado: string, display: string}
     */
    private static function omision(): array
    {
        return [
            'value' => null,
            'estado' => CapturaCeroEstado::OMISION,
            'display' => '—',
        ];
    }

    private static function formatearKg(float $kg): string
    {
        $redondeado = round($kg, 1);
        $partes = explode('.', number_format($redondeado, 1, '.', ''));
        $entero = number_format((int) $partes[0], 0, ',', '.');
        $decimal = $partes[1] ?? '0';

        return $decimal === '0'
            ? $entero.' kg'
            : $entero.','.$decimal.' kg';
    }
}
