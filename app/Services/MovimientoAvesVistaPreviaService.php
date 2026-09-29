<?php

namespace App\Services;

use App\Enums\LoteEstado;
use App\Models\Galpon;
use App\Models\Lote;
use Illuminate\Support\Collection;

class MovimientoAvesVistaPreviaService
{
    /**
     * @return array{
     *     valido: bool,
     *     lineas: list<string>,
     *     errores: list<string>,
     *     conserva_total_empresa: bool,
     * }
     */
    public function traslado(Galpon $origen, Galpon $destino, int $cantidad): array
    {
        $errores = [];
        $lineas = [];

        if ($origen->id === $destino->id) {
            $errores[] = 'Elegí un galpón destino distinto del origen.';
        }

        if ($cantidad < 1) {
            $errores[] = 'La cantidad debe ser al menos 1.';
        }

        $saldoOrigen = (int) $origen->aves_actuales;

        if ($cantidad > $saldoOrigen) {
            $errores[] = 'La cantidad supera el saldo vivo del galpón origen ('.number_format($saldoOrigen, 0, ',', '.').' aves).';
        }

        if ($errores === []) {
            $lineas[] = $origen->nombre.': '.number_format($saldoOrigen, 0, ',', '.')
                .' → '.number_format($saldoOrigen - $cantidad, 0, ',', '.').' aves';
            $lineas[] = $destino->nombre.': '.number_format((int) $destino->aves_actuales, 0, ',', '.')
                .' → '.number_format((int) $destino->aves_actuales + $cantidad, 0, ',', '.').' aves';
            $lineas[] = 'Total de aves en la empresa: sin cambio (traslado interno).';
        }

        return [
            'valido' => $errores === [],
            'lineas' => $lineas,
            'errores' => $errores,
            'conserva_total_empresa' => true,
        ];
    }

    /**
     * @return array{valido: bool, lineas: list<string>, errores: list<string>, conserva_total_empresa: bool}
     */
    public function entradaExterna(Galpon $galpon, int $cantidad): array
    {
        $errores = [];
        $lineas = [];

        if ($cantidad < 1) {
            $errores[] = 'La cantidad debe ser al menos 1.';
        }

        if ($errores === []) {
            $antes = (int) $galpon->aves_actuales;
            $lineas[] = $galpon->nombre.': '.number_format($antes, 0, ',', '.')
                .' → '.number_format($antes + $cantidad, 0, ',', '.').' aves';
            $lineas[] = 'Total de aves en la empresa: +'.number_format($cantidad, 0, ',', '.').' aves.';
        }

        return [
            'valido' => $errores === [],
            'lineas' => $lineas,
            'errores' => $errores,
            'conserva_total_empresa' => false,
        ];
    }

    /**
     * @return array{valido: bool, lineas: list<string>, errores: list<string>, conserva_total_empresa: bool}
     */
    public function ajusteInventario(Galpon $galpon, int $conteoFisico): array
    {
        $errores = [];
        $lineas = [];

        if ($conteoFisico < 0) {
            $errores[] = 'El conteo físico no puede ser negativo.';
        }

        $sistema = (int) $galpon->aves_actuales;
        $delta = $conteoFisico - $sistema;

        if ($delta === 0) {
            $errores[] = 'El conteo coincide con el sistema; no hace falta ajuste.';
        }

        if ($errores === []) {
            $lineas[] = 'Saldo sistema: '.number_format($sistema, 0, ',', '.').' aves';
            $lineas[] = 'Conteo físico: '.number_format($conteoFisico, 0, ',', '.').' aves';
            $lineas[] = 'Ajuste en ledger: '.($delta > 0 ? '+' : '').number_format($delta, 0, ',', '.').' aves';
            $lineas[] = $galpon->nombre.' quedaría en '.number_format($conteoFisico, 0, ',', '.').' aves.';
            $lineas[] = 'No modifica registros de muertes ni descarte.';
        }

        return [
            'valido' => $errores === [],
            'lineas' => $lineas,
            'errores' => $errores,
            'conserva_total_empresa' => false,
        ];
    }

    /**
     * @return array{valido: bool, lineas: list<string>, errores: list<string>, conserva_total_empresa: bool}
     */
    public function cierreLote(Galpon $galpon, Lote $lote, int $cantidad, bool $cerrarCiclo): array
    {
        $errores = [];
        $lineas = [];

        if ($cantidad < 1) {
            $errores[] = 'La cantidad debe ser al menos 1.';
        }

        $saldo = (int) $galpon->aves_actuales;

        if ($cerrarCiclo && $cantidad !== $saldo) {
            $errores[] = 'Para cerrar el ciclo debés registrar el remanente completo ('.number_format($saldo, 0, ',', '.').' aves).';
        }

        if ($cantidad > $saldo) {
            $errores[] = 'La cantidad supera el saldo vivo del galpón.';
        }

        if ($errores === []) {
            $lineas[] = $galpon->nombre.': '.number_format($saldo, 0, ',', '.')
                .' → '.number_format($saldo - $cantidad, 0, ',', '.').' aves';
            $lineas[] = 'Lote '.$lote->codigo.': salida de '.number_format($cantidad, 0, ',', '.').' aves.';
            if ($cerrarCiclo) {
                $lineas[] = 'El lote pasará a estado cerrado.';
            }
            $lineas[] = 'Total de aves en la empresa: −'.number_format($cantidad, 0, ',', '.').' aves.';
        }

        return [
            'valido' => $errores === [],
            'lineas' => $lineas,
            'errores' => $errores,
            'conserva_total_empresa' => false,
        ];
    }

    /**
     * @return array{valido: bool, lineas: list<string>, errores: list<string>, conserva_total_empresa: bool}
     */
    public function faena(
        Galpon $galpon,
        Lote $lote,
        int $cantidad,
        string $destinoFaena,
        bool $cerrarCiclo,
        ?string $referenciaRemito = null,
    ): array {
        $errores = [];
        $lineas = [];

        if (trim($destinoFaena) === '') {
            $errores[] = 'Indicá la planta o destino de faena.';
        }

        if ($cantidad < 1) {
            $errores[] = 'La cantidad debe ser al menos 1.';
        }

        $saldo = (int) $galpon->aves_actuales;

        if ($cerrarCiclo && $cantidad !== $saldo) {
            $errores[] = 'Para cerrar el ciclo debés registrar el remanente completo ('.number_format($saldo, 0, ',', '.').' aves).';
        }

        if ($cantidad > $saldo) {
            $errores[] = 'La cantidad supera el saldo vivo del galpón.';
        }

        if ($errores === []) {
            $lineas[] = 'Destino faena: '.trim($destinoFaena);
            if ($referenciaRemito !== null && trim($referenciaRemito) !== '') {
                $lineas[] = 'Referencia interna (remito/documento): '.trim($referenciaRemito);
            }
            $lineas[] = $galpon->nombre.': '.number_format($saldo, 0, ',', '.')
                .' → '.number_format($saldo - $cantidad, 0, ',', '.').' aves';
            $lineas[] = 'Lote '.$lote->codigo.': salida a faena de '.number_format($cantidad, 0, ',', '.').' aves.';
            if ($cerrarCiclo) {
                $lineas[] = 'El lote pasará a estado cerrado.';
            }
            $lineas[] = 'Trazabilidad interna en AviCore; no se envía remito SMA automáticamente.';
            $lineas[] = 'Total de aves en la empresa: −'.number_format($cantidad, 0, ',', '.').' aves.';
        }

        return [
            'valido' => $errores === [],
            'lineas' => $lineas,
            'errores' => $errores,
            'conserva_total_empresa' => false,
        ];
    }

    /**
     * @return Collection<int, Lote>
     */
    public function lotesActivosEnGalpon(Galpon $galpon): Collection
    {
        return $galpon->lotes()
            ->whereIn('estado', [LoteEstado::Activo->value, LoteEstado::EnProduccion->value])
            ->orderBy('codigo')
            ->get();
    }
}
