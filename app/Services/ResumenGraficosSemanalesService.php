<?php

namespace App\Services;

use App\Models\RegistroOperativo;
use App\Models\User;
use App\Support\DiaOperativoEmpresa;
use App\Support\ResumenSemanaOperativa;

class ResumenGraficosSemanalesService
{
    public function __construct(
        private EmpresaContextService $empresaContext,
        private SoporteEmpresaService $soporte,
        private TotalesCapturaDiaService $totalesCapturaDia,
    ) {}

    /**
     * @return array{
     *     tabla: list<array{
     *         label: string,
     *         date: string,
     *         huevos_aptos: array{value: int|null, estado: string, display: string},
     *         huevos_descarte: array{value: int|null, estado: string, display: string},
     *         muertes: array{value: int|null, estado: string, display: string},
     *         alimento_kg: array{value: float|null, estado: string, display: string},
     *     }>,
     *     series: array{
     *         huevos_aptos: list<array{label: string, value: int|null, date: string, display: string, estado: string}>,
     *         huevos_descarte: list<array{label: string, value: int|null, date: string, display: string, estado: string}>,
     *         muertes: list<array{label: string, value: int|null, date: string, display: string, estado: string}>,
     *         alimento_kg: list<array{label: string, value: float|null, date: string, display: string, estado: string}>,
     *     }
     * }
     */
    public function for(User $user, ?int $granjaId = null, ?int $galponId = null): array
    {
        $vacios = $this->vacios();

        if (! $this->soporte->canViewResumenOperativo($user)) {
            return $vacios;
        }

        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return $vacios;
        }

        $galponIds = $this->totalesCapturaDia->galponesEnScope($user, $granjaId, $galponId)->modelKeys();

        if ($galponIds === []) {
            return $this->vacios($empresaId);
        }

        $hoy = DiaOperativoEmpresa::hoyParaEmpresa($empresaId);
        $tabla = [];
        $series = [
            'huevos_aptos' => [],
            'huevos_descarte' => [],
            'muertes' => [],
            'alimento_kg' => [],
        ];

        for ($i = 6; $i >= 0; $i--) {
            $dia = DiaOperativoEmpresa::forEmpresa(
                $empresaId,
                $hoy->fechaLogica->copy()->subDays($i),
            );

            $registrosDia = RegistroOperativo::query()
                ->activos()
                ->where('empresa_id', $empresaId)
                ->whereIn('galpon_id', $galponIds)
                ->where('created_at', '>=', $dia->inicioUtc())
                ->where('created_at', '<', $dia->finUtc())
                ->get();

            $label = $dia->fechaLogica->format('j/n');
            $date = $dia->fechaLogica->toDateString();

            $huevosAptos = ResumenSemanaOperativa::celdaHuevosAptos($registrosDia, $galponIds);
            $huevosDescarte = ResumenSemanaOperativa::celdaHuevosDescarte($registrosDia, $galponIds);
            $muertes = ResumenSemanaOperativa::celdaMuertes($registrosDia, $galponIds);
            $alimento = ResumenSemanaOperativa::celdaAlimentoKg($registrosDia, $galponIds);

            $tabla[] = [
                'label' => $label,
                'date' => $date,
                'huevos_aptos' => $huevosAptos,
                'huevos_descarte' => $huevosDescarte,
                'muertes' => $muertes,
                'alimento_kg' => $alimento,
            ];

            foreach ([
                'huevos_aptos' => $huevosAptos,
                'huevos_descarte' => $huevosDescarte,
                'muertes' => $muertes,
                'alimento_kg' => $alimento,
            ] as $clave => $celda) {
                $series[$clave][] = [
                    'label' => $label,
                    'date' => $date,
                    'value' => $celda['value'],
                    'display' => $celda['display'],
                    'estado' => $celda['estado'],
                ];
            }
        }

        return [
            'tabla' => $tabla,
            'series' => $series,
        ];
    }

    /**
     * @return array{tabla: list<array<string, mixed>>, series: array<string, list<array<string, mixed>>>}
     */
    private function vacios(?int $empresaId = null): array
    {
        $puntos = [];

        if ($empresaId !== null) {
            $hoy = DiaOperativoEmpresa::hoyParaEmpresa($empresaId);

            for ($i = 6; $i >= 0; $i--) {
                $fecha = $hoy->fechaLogica->copy()->subDays($i);
                $puntos[] = [
                    'label' => $fecha->format('j/n'),
                    'date' => $fecha->toDateString(),
                ];
            }
        } else {
            $inicio = now()->subDays(6)->startOfDay();

            for ($i = 0; $i < 7; $i++) {
                $fecha = $inicio->copy()->addDays($i);
                $puntos[] = [
                    'label' => $fecha->format('j/n'),
                    'date' => $fecha->toDateString(),
                ];
            }
        }

        $omision = [
            'value' => null,
            'estado' => 'omision',
            'display' => '—',
        ];

        $tabla = [];
        $series = [
            'huevos_aptos' => [],
            'huevos_descarte' => [],
            'muertes' => [],
            'alimento_kg' => [],
        ];

        foreach ($puntos as $punto) {
            $tabla[] = [
                'label' => $punto['label'],
                'date' => $punto['date'],
                'huevos_aptos' => $omision,
                'huevos_descarte' => $omision,
                'muertes' => $omision,
                'alimento_kg' => $omision,
            ];

            foreach (array_keys($series) as $clave) {
                $series[$clave][] = [
                    'label' => $punto['label'],
                    'date' => $punto['date'],
                    'value' => null,
                    'display' => '—',
                    'estado' => 'omision',
                ];
            }
        }

        return [
            'tabla' => $tabla,
            'series' => $series,
        ];
    }
}
