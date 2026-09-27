<?php

namespace App\Services;

use App\Enums\LoteEstado;
use App\Models\Galpon;
use App\Models\Lote;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EstructuraFichaService
{
    public function __construct(private OperarioGalponResumenService $galponResumen) {}

    /**
     * @return array{
     *     galpon: Galpon,
     *     lotes: Collection<int, Lote>,
     *     lotes_activos: int,
     *     registros_count: int,
     *     vacunaciones_count: int,
     *     resumen: array<string, mixed>,
     *     saldo_nota: string,
     * }
     */
    public function galpon(Galpon $galpon): array
    {
        $galpon->loadMissing(['granja', 'lotes' => fn ($q) => $q->orderByDesc('fecha_ingreso')]);

        $lotesActivos = $galpon->lotes
            ->filter(fn (Lote $lote): bool => in_array($lote->estado, [LoteEstado::Activo, LoteEstado::EnProduccion], true));

        $saldoNota = $lotesActivos->count() > 1
            ? 'El saldo vivo ('.number_format($galpon->aves_actuales, 0, ',', '.').' aves) es del galpón completo; con varios lotes activos no se reparte por lote.'
            : 'Saldo vivo del galpón según muertes, descartes y altas registradas.';

        return [
            'galpon' => $galpon,
            'lotes' => $galpon->lotes,
            'lotes_activos' => $lotesActivos->count(),
            'registros_count' => $galpon->registrosOperativos()->count(),
            'vacunaciones_count' => $galpon->vacunaciones()->count(),
            'resumen' => $this->galponResumen->resumen($galpon),
            'saldo_nota' => $saldoNota,
        ];
    }

    /**
     * @return array{
     *     lote: Lote,
     *     edad_semanas: int,
     *     metricas_atribuibles: bool,
     *     metricas: ?array{huevos_hoy: int, muertes_hoy: int, aves_actuales: int},
     *     metricas_aviso: ?string,
     *     saldo_nota: string,
     *     estado_historial: list<array{fecha: string, transicion: string, motivo: string, actor: string}>,
     * }
     */
    public function lote(Lote $lote): array
    {
        $lote->loadMissing(['galpon.granja']);

        $lotesActivosEnGalpon = Lote::query()
            ->where('galpon_id', $lote->galpon_id)
            ->where('empresa_id', $lote->empresa_id)
            ->whereIn('estado', [LoteEstado::Activo->value, LoteEstado::EnProduccion->value])
            ->count();

        $metricasAtribuibles = $lotesActivosEnGalpon === 1
            && in_array($lote->estado, [LoteEstado::Activo, LoteEstado::EnProduccion], true);

        $metricas = null;
        $metricasAviso = null;

        if ($metricasAtribuibles) {
            $resumen = $this->galponResumen->resumen($lote->galpon);
            $metricas = [
                'huevos_hoy' => $resumen['huevos_hoy'],
                'muertes_hoy' => $resumen['muertes_hoy'],
                'aves_actuales' => $resumen['aves_actuales'],
            ];
        } elseif ($lotesActivosEnGalpon > 1) {
            $metricasAviso = 'Hay varios lotes activos en el galpón: la producción y el saldo vivo se registran por galpón, no por lote.';
        }

        $saldoNota = 'Población inicial al ingreso: '
            .number_format($lote->cantidad_inicial, 0, ',', '.')
            .' aves. El saldo vivo actual vive en el galpón ('.number_format($lote->galpon->aves_actuales, 0, ',', '.').' aves).';

        return [
            'lote' => $lote,
            'edad_semanas' => $this->galponResumen->edadSemanas($lote),
            'metricas_atribuibles' => $metricasAtribuibles,
            'metricas' => $metricas,
            'metricas_aviso' => $metricasAviso,
            'saldo_nota' => $saldoNota,
            'estado_historial' => $this->formatearEstadoHistorial($lote),
        ];
    }

    /**
     * @return list<array{fecha: string, transicion: string, motivo: string, actor: string}>
     */
    private function formatearEstadoHistorial(Lote $lote): array
    {
        $historial = $lote->estado_historial ?? [];

        return collect($historial)
            ->map(function (array $entrada): array {
                $anterior = LoteEstado::tryFrom($entrada['estado_anterior'] ?? '')?->label() ?? '—';
                $nuevo = LoteEstado::tryFrom($entrada['estado_nuevo'] ?? '')?->label() ?? '—';

                return [
                    'fecha' => isset($entrada['fecha'])
                        ? Carbon::parse($entrada['fecha'])->format('d/m/Y H:i')
                        : '—',
                    'transicion' => $anterior.' → '.$nuevo,
                    'motivo' => (string) ($entrada['motivo'] ?? ''),
                    'actor' => (string) ($entrada['actor_name'] ?? '—'),
                ];
            })
            ->reverse()
            ->values()
            ->all();
    }
}
