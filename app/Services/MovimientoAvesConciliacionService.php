<?php

namespace App\Services;

use App\Enums\LoteEstado;
use App\Enums\MovimientoAvesEstado;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Support\MovimientoAvesLoteSaldo;
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
        return MovimientoAves::query()
            ->where('empresa_id', $galpon->empresa_id)
            ->where('estado', MovimientoAvesEstado::Activo->value)
            ->where(function ($query) use ($galpon): void {
                $query->where('galpon_origen_id', $galpon->id)
                    ->orWhere('galpon_destino_id', $galpon->id);
            })
            ->get();
    }
}
