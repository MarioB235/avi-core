<?php

namespace App\Support;

/**
 * Semántica de alimento en v1 (RES-11): kg del remito al entregar, no consumo ni eficiencia.
 */
final class AlimentoEntregaSemantica
{
    /**
     * Métricas que v1 no debe calcular ni mostrar a partir de kg entregados.
     *
     * @var list<string>
     */
    public const METRICAS_PROHIBIDAS_V1 = [
        'conversion_alimenticia',
        'eficiencia_alimentaria',
        'gramos_por_ave_dia',
        'huevos_por_kg_alimento',
        'fcr',
    ];

    public static function conversionProhibidaEnV1(string $metricaId): bool
    {
        return in_array($metricaId, self::METRICAS_PROHIBIDAS_V1, true);
    }

    /**
     * @return array{
     *     etiqueta_kpi: string,
     *     hint_kpi: string,
     *     etiqueta_columna: string,
     *     etiqueta_grafico: string,
     *     disclaimer: string,
     * }
     */
    public static function presentacionResumen(): array
    {
        return [
            'etiqueta_kpi' => 'Alimento entregado hoy',
            'hint_kpi' => 'Kg del remito al llegar el camión (no es consumo diario)',
            'etiqueta_columna' => 'Kg entregados',
            'etiqueta_grafico' => 'Alimento entregado',
            'disclaimer' => 'Los kg registrados son entregas de camión. AviCore no calcula consumo diario ni conversión alimenticia en v1.',
        ];
    }
}
