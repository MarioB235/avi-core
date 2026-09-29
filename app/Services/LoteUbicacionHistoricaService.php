<?php

namespace App\Services;

use App\Enums\MovimientoAvesEstado;
use App\Enums\MovimientoAvesOrigen;
use App\Enums\MovimientoAvesTipo;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\RegistroOperativo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LoteUbicacionHistoricaService
{
    public function __construct(private MovimientoAvesConciliacionService $conciliacion) {}

    /**
     * Galpón donde estaba el expediente del lote en un instante (MOV-10).
     * Se reconstruye desde saldo inicial + traslados que cambiaron ubicación administrativa.
     */
    public function galponEnMomento(Lote $lote, Carbon $momento): int
    {
        $momento = $momento->copy();

        $galponActual = $this->galponAltaDesdeLedger($lote);

        foreach ($this->trasladosQueCambiaronUbicacion($lote, $momento) as $traslado) {
            if ((int) $traslado->galpon_origen_id === $galponActual) {
                $galponActual = (int) $traslado->galpon_destino_id;
            }
        }

        return $galponActual;
    }

    /**
     * @return list<array{galpon_id: int, desde: string, hasta: ?string}>
     */
    public function segmentosUbicacion(Lote $lote): array
    {
        $galponActual = $this->galponAltaDesdeLedger($lote);
        $desdeActual = Carbon::parse($lote->fecha_ingreso)->startOfDay();

        $segmentos = [];
        $traslados = $this->trasladosQueCambiaronUbicacion($lote, now()->endOfDay());

        foreach ($traslados as $traslado) {
            if ((int) $traslado->galpon_origen_id !== $galponActual) {
                continue;
            }

            $segmentos[] = [
                'galpon_id' => $galponActual,
                'desde' => $desdeActual->toDateString(),
                'hasta' => $traslado->fecha_efectiva->toDateString(),
            ];

            $galponActual = (int) $traslado->galpon_destino_id;
            $desdeActual = $traslado->fecha_efectiva->copy()->startOfDay();
        }

        $segmentos[] = [
            'galpon_id' => $galponActual,
            'desde' => $desdeActual->toDateString(),
            'hasta' => null,
        ];

        return $segmentos;
    }

    /**
     * El galpón del hecho operativo es el capturado en el registro, no la ubicación actual del lote.
     */
    public function galponDelHechoOperativo(RegistroOperativo $registro): int
    {
        return (int) $registro->galpon_id;
    }

    /**
     * Suma de huevos aptos atribuibles al galpón según dónde se cargó el hecho (no reasigna por traslado posterior).
     */
    public function huevosAptosPorGalponEnPeriodo(Galpon $galpon, Carbon $desde, Carbon $hasta): int
    {
        $totales = RegistroOperativo::query()
            ->forEmpresa((int) $galpon->empresa_id)
            ->where('galpon_id', $galpon->id)
            ->activos()
            ->where('created_at', '>=', $desde->copy()->startOfDay())
            ->where('created_at', '<=', $hasta->copy()->endOfDay())
            ->selectRaw('COALESCE(SUM(huevos), 0) as huevos')
            ->first();

        return (int) ($totales->huevos ?? 0);
    }

    /**
     * Traslado total del remanente del lote en origen: actualiza expediente y marca metadata.
     */
    public function debeReasignarExpedienteLote(
        Galpon $galponOrigen,
        Lote $lote,
        int $cantidad,
        ?int $muertesImputadasLote,
        ?int $descarteImputadoLote,
    ): bool {
        $saldoDeclarado = $this->conciliacion->saldoDeclaradoLoteEnGalpon(
            $galponOrigen,
            $lote,
            $muertesImputadasLote ?? 0,
            $descarteImputadoLote ?? 0,
        );

        $remanente = $saldoDeclarado - $this->cantidadLedgerSaldoInicialEnGalpon($lote, $galponOrigen);

        return $cantidad > 0 && $cantidad === $remanente;
    }

    public function galponAltaDesdeLedger(Lote $lote): int
    {
        $saldoInicial = MovimientoAves::query()
            ->where('lote_id', $lote->id)
            ->where('estado', MovimientoAvesEstado::Activo->value)
            ->where('tipo', MovimientoAvesTipo::Entrada->value)
            ->where('metadata->origen', MovimientoAvesOrigen::SaldoInicialLote->value)
            ->orderBy('fecha_efectiva')
            ->first();

        if ($saldoInicial?->galpon_destino_id) {
            return (int) $saldoInicial->galpon_destino_id;
        }

        return (int) $lote->galpon_id;
    }

    private function cantidadLedgerSaldoInicialEnGalpon(Lote $lote, Galpon $galpon): int
    {
        $movimiento = MovimientoAves::query()
            ->where('lote_id', $lote->id)
            ->where('galpon_destino_id', $galpon->id)
            ->where('estado', MovimientoAvesEstado::Activo->value)
            ->where('tipo', MovimientoAvesTipo::Entrada->value)
            ->where('metadata->origen', MovimientoAvesOrigen::SaldoInicialLote->value)
            ->first();

        return $movimiento ? (int) $movimiento->cantidad : 0;
    }

    /**
     * @return Collection<int, MovimientoAves>
     */
    private function trasladosQueCambiaronUbicacion(Lote $lote, Carbon $hasta): Collection
    {
        return MovimientoAves::query()
            ->where('lote_id', $lote->id)
            ->where('empresa_id', $lote->empresa_id)
            ->where('tipo', MovimientoAvesTipo::Traslado->value)
            ->where('estado', MovimientoAvesEstado::Activo->value)
            ->where('fecha_efectiva', '<=', $hasta)
            ->where('metadata->cambio_ubicacion_expediente', true)
            ->orderBy('fecha_efectiva')
            ->orderBy('id')
            ->get();
    }
}
