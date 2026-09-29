<?php

namespace App\Services;

use App\Enums\LoteEstado;
use App\Enums\MovimientoAvesEstado;
use App\Enums\MovimientoAvesOrigen;
use App\Enums\MovimientoAvesTipo;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\RegistroOperativo;
use App\Support\MovimientoAvesLoteSaldo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MovimientoAvesConciliacionService
{
    public function __construct(private OperarioGalponResumenService $galponResumen) {}

    /**
     * Snapshot D01: hechos del galpón y atribución por lote sin reparto silencioso de muertes.
     *
     * @return array{
     *     galpon_id: int,
     *     aves_actuales: int,
     *     muertes_galpon: int,
     *     descarte_galpon: int,
     *     multiples_lotes: bool,
     *     lotes: list<array{
     *         lote_id: int,
     *         codigo: string,
     *         cantidad_inicial: int,
     *         movimientos_netos: int,
     *         saldo_atribuible: ?int,
     *         saldo_es_hecho: bool,
     *         nota: ?string,
     *     }>,
     * }
     */
    public function snapshot(Galpon $galpon): array
    {
        $resumen = $this->galponResumen->resumen($galpon);
        $multiples = (bool) $resumen['multiples_lotes'];
        $movimientos = $this->movimientosActivosDelGalpon($galpon);

        $lotes = $resumen['lotes']->map(function (Lote $lote) use ($galpon, $multiples, $movimientos): array {
            $movimientosNetos = MovimientoAvesLoteSaldo::saldoMovimientosLoteEnGalpon(
                $movimientos,
                $lote->id,
                $galpon->id,
            );

            if (! $multiples) {
                return [
                    'lote_id' => $lote->id,
                    'codigo' => $lote->codigo,
                    'cantidad_inicial' => (int) $lote->cantidad_inicial,
                    'movimientos_netos' => $movimientosNetos,
                    'saldo_atribuible' => (int) $galpon->aves_actuales,
                    'saldo_es_hecho' => true,
                    'nota' => null,
                ];
            }

            return [
                'lote_id' => $lote->id,
                'codigo' => $lote->codigo,
                'cantidad_inicial' => (int) $lote->cantidad_inicial,
                'movimientos_netos' => $movimientosNetos,
                'saldo_atribuible' => null,
                'saldo_es_hecho' => false,
                'nota' => 'Imputá muertes y descartes del galpón al lote antes de trasladar o cerrar; no se estima saldo por lote.',
            ];
        })->values()->all();

        return [
            'galpon_id' => $galpon->id,
            'aves_actuales' => (int) $galpon->aves_actuales,
            'muertes_galpon' => (int) $resumen['muertes_acumuladas'],
            'descarte_galpon' => (int) $resumen['descarte_aves_acumuladas'],
            'multiples_lotes' => $multiples,
            'lotes' => $lotes,
        ];
    }

    /**
     * Saldo declarado del lote en el galpón tras imputar mortalidad/descarte (D01).
     */
    public function saldoDeclaradoLoteEnGalpon(
        Galpon $galpon,
        Lote $lote,
        int $muertesImputadas,
        int $descarteImputado,
    ): int {
        $movimientos = $this->movimientosActivosDelGalpon($galpon);
        $base = (int) $lote->cantidad_inicial
            + MovimientoAvesLoteSaldo::saldoMovimientosLoteEnGalpon($movimientos, $lote->id, $galpon->id);

        return $base - $muertesImputadas - $descarteImputado;
    }

    public function loteActivoUnico(Galpon $galpon): ?Lote
    {
        $lotes = $this->galponResumen->lotesActivos($galpon);

        return $lotes->count() === 1 ? $lotes->first() : null;
    }

    /**
     * MOV-09: Inicial + entradas − salidas − muertes − descartes + ajustes = saldo esperado vs `aves_actuales`.
     * Período por defecto: desde el ingreso del lote activo más antiguo hasta hoy (misma ventana que operario).
     * Los movimientos `saldo_inicial_lote` no suman en entradas (ya están en `inicial`). Reversiones solo informativas.
     *
     * @return array{
     *     galpon_id: int,
     *     fecha_desde: ?string,
     *     fecha_hasta: string,
     *     inicial: int,
     *     entradas: int,
     *     salidas: int,
     *     muertes: int,
     *     descartes: int,
     *     ajustes: int,
     *     reversiones_registradas: int,
     *     saldo_esperado: int,
     *     aves_actuales: int,
     *     diferencia: int,
     *     cuadra: bool,
     * }
     */
    public function conciliacionAcumulada(Galpon $galpon, ?Carbon $desde = null, ?Carbon $hasta = null): array
    {
        $lotes = $this->galponResumen->lotesActivos($galpon);
        $fechaInicioVentana = $this->galponResumen->fechaInicioVentana($lotes);

        $hasta ??= now();
        $hasta = $hasta->copy()->endOfDay();

        if ($desde === null) {
            $desde = $fechaInicioVentana?->copy()->startOfDay();
        } else {
            $desde = $desde->copy()->startOfDay();
        }

        $inicial = (int) $lotes->sum('cantidad_inicial');

        $entradas = 0;
        $salidas = 0;
        $ajustes = 0;
        $reversionesRegistradas = 0;

        foreach ($this->movimientosActivosDelGalponEnPeriodo($galpon, $desde, $hasta) as $movimiento) {
            foreach ($this->bucketsMovimientoEnGalpon($movimiento, (int) $galpon->id) as $clave => $valor) {
                match ($clave) {
                    'entradas' => $entradas += $valor,
                    'salidas' => $salidas += $valor,
                    'ajustes' => $ajustes += $valor,
                    'reversiones_registradas' => $reversionesRegistradas += $valor,
                    default => null,
                };
            }
        }

        $muertes = 0;
        $descartes = 0;

        if ($desde !== null) {
            $totalesOperativos = RegistroOperativo::query()
                ->forEmpresa((int) $galpon->empresa_id)
                ->where('galpon_id', $galpon->id)
                ->activos()
                ->where('created_at', '>=', $desde)
                ->where('created_at', '<=', $hasta)
                ->selectRaw('COALESCE(SUM(muertes), 0) as muertes')
                ->selectRaw('COALESCE(SUM(descarte_aves), 0) as descarte_aves')
                ->first();

            $muertes = (int) ($totalesOperativos->muertes ?? 0);
            $descartes = (int) ($totalesOperativos->descarte_aves ?? 0);
        }

        $saldoEsperado = $inicial + $entradas - $salidas - $muertes - $descartes + $ajustes;
        $avesActuales = (int) $galpon->aves_actuales;
        $diferencia = $avesActuales - $saldoEsperado;

        return [
            'galpon_id' => $galpon->id,
            'fecha_desde' => $desde?->toDateString(),
            'fecha_hasta' => $hasta->toDateString(),
            'inicial' => $inicial,
            'entradas' => $entradas,
            'salidas' => $salidas,
            'muertes' => $muertes,
            'descartes' => $descartes,
            'ajustes' => $ajustes,
            'reversiones_registradas' => $reversionesRegistradas,
            'saldo_esperado' => $saldoEsperado,
            'aves_actuales' => $avesActuales,
            'diferencia' => $diferencia,
            'cuadra' => $diferencia === 0,
        ];
    }

    public function assertLoteActivoEnGalpon(Galpon $galpon, int $loteId): Lote
    {
        $lote = Lote::query()
            ->whereKey($loteId)
            ->where('galpon_id', $galpon->id)
            ->where('empresa_id', $galpon->empresa_id)
            ->whereIn('estado', [LoteEstado::Activo->value, LoteEstado::EnProduccion->value])
            ->first();

        if (! $lote instanceof Lote) {
            throw ValidationException::withMessages([
                'lote_id' => 'El lote no está activo en este galpón.',
            ]);
        }

        return $lote;
    }

    /**
     * @return Collection<int, MovimientoAves>
     */
    private function movimientosActivosDelGalpon(Galpon $galpon): Collection
    {
        return $this->movimientosActivosDelGalponEnPeriodo($galpon, null, now()->endOfDay());
    }

    /**
     * @return Collection<int, MovimientoAves>
     */
    private function movimientosActivosDelGalponEnPeriodo(Galpon $galpon, ?Carbon $desde, Carbon $hasta): Collection
    {
        $query = MovimientoAves::query()
            ->where('empresa_id', $galpon->empresa_id)
            ->where('estado', MovimientoAvesEstado::Activo->value)
            ->where(function ($query) use ($galpon): void {
                $query->where('galpon_origen_id', $galpon->id)
                    ->orWhere('galpon_destino_id', $galpon->id);
            })
            ->where('fecha_efectiva', '<=', $hasta);

        if ($desde !== null) {
            $query->where('fecha_efectiva', '>=', $desde);
        }

        return $query->get();
    }

    /**
     * @return array{entradas?: int, salidas?: int, ajustes?: int, reversiones_registradas?: int}
     */
    private function bucketsMovimientoEnGalpon(MovimientoAves $movimiento, int $galponId): array
    {
        if ($movimiento->tipo === MovimientoAvesTipo::Reversion) {
            if ($this->movimientoTocaGalpon($movimiento, $galponId)) {
                return ['reversiones_registradas' => 1];
            }

            return [];
        }

        $origenMeta = (string) ($movimiento->metadata['origen'] ?? '');
        if ($origenMeta === MovimientoAvesOrigen::SaldoInicialLote->value) {
            return [];
        }

        $cantidad = (int) $movimiento->cantidad;
        $buckets = [];

        switch ($movimiento->tipo) {
            case MovimientoAvesTipo::Entrada:
                if ($movimiento->galpon_destino_id === $galponId) {
                    $buckets['entradas'] = $cantidad;
                }
                break;
            case MovimientoAvesTipo::Traslado:
                if ($movimiento->galpon_destino_id === $galponId) {
                    $buckets['entradas'] = $cantidad;
                }
                if ($movimiento->galpon_origen_id === $galponId) {
                    $buckets['salidas'] = $cantidad;
                }
                break;
            case MovimientoAvesTipo::CierreLote:
            case MovimientoAvesTipo::Faena:
                if ($movimiento->galpon_origen_id === $galponId) {
                    $buckets['salidas'] = $cantidad;
                }
                break;
            case MovimientoAvesTipo::Ajuste:
                if ($movimiento->galpon_origen_id === $galponId && $movimiento->ajuste_delta !== null) {
                    $buckets['ajustes'] = (int) $movimiento->ajuste_delta;
                }
                break;
            default:
                break;
        }

        return $buckets;
    }

    private function movimientoTocaGalpon(MovimientoAves $movimiento, int $galponId): bool
    {
        return $movimiento->galpon_origen_id === $galponId
            || $movimiento->galpon_destino_id === $galponId;
    }
}
