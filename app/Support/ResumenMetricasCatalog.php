<?php

namespace App\Support;

/**
 * Catálogo canónico de métricas de Inicio/Resumen (RES-02).
 * Narrativa completa: `.cursor/skills/avicore-negocio/references/metricas-resumen.md`.
 */
final class ResumenMetricasCatalog
{
    /** Umbral de referencia UI (no diagnóstico automático; RES-06). */
    public const UMBRAL_MORTALIDAD_REFERENCIA_PCT = 1.1;

    public static function superaReferenciaMortalidad(float $mortalidadAcumuladaPct): bool
    {
        return $mortalidadAcumuladaPct > self::UMBRAL_MORTALIDAD_REFERENCIA_PCT;
    }

    /**
     * Copy y metadatos de la referencia de mortalidad (RES-06) — encuestas sector, no norma legal.
     *
     * @return array{
     *     umbral_pct: float,
     *     periodo: string,
     *     etiqueta_umbral: string,
     *     etiqueta_kpi: string,
     *     etiqueta_badge: string,
     *     disclaimer: string,
     * }
     */
    public static function referenciaAlimentoEntregado(): array
    {
        return AlimentoEntregaSemantica::presentacionResumen();
    }

    public static function referenciaMortalidad(): array
    {
        $umbral = self::UMBRAL_MORTALIDAD_REFERENCIA_PCT;

        return [
            'umbral_pct' => $umbral,
            'periodo' => 'Mortalidad acumulada del ciclo del galpón (ventana RES-05; no es un día aislado).',
            'etiqueta_umbral' => 'Referencia orientativa ~'.number_format($umbral, 1, ',', '.').' % (encuestas avícolas; no norma universal ni diagnóstico).',
            'etiqueta_kpi' => 'Galpones sobre referencia',
            'etiqueta_badge' => 'Sobre referencia',
            'disclaimer' => 'AviCore solo compara con el umbral configurado. La interpretación y las decisiones de manejo las define el responsable de la empresa.',
        ];
    }

    /**
     * @return array<string, array{
     *     pantalla: string,
     *     fuente: string,
     *     unidad: string,
     *     periodo: string,
     *     poblacion: string,
     *     exclusiones: string,
     *     ausencia: string,
     *     implementacion: string
     * }>
     */
    public static function definiciones(): array
    {
        return [
            'huevos_hoy' => [
                'pantalla' => 'Resumen, Inicio (pulso)',
                'fuente' => 'registros_operativos.tipo=huevos, activos',
                'unidad' => 'huevos (entero); presentación maples/cajas vía HuevosUnidad',
                'periodo' => 'día operativo empresa (DiaOperativoEmpresa + scope delDia)',
                'poblacion' => 'galpones en scope: disponiblesParaCarga + empresa del actor + filtros granja/galpón',
                'exclusiones' => 'estado anulado; galpones de granja inactiva; otras empresas',
                'ausencia' => '0 si no hay carga de huevos ese día',
                'implementacion' => 'OperarioGalponResumenService::resumen → AdminResumenService::for (suma)',
            ],
            'huevos_descarte_hoy' => [
                'pantalla' => 'Resumen',
                'fuente' => 'registros_operativos.huevos_descarte, tipo huevos',
                'unidad' => 'huevos descarte',
                'periodo' => 'día operativo actual',
                'poblacion' => 'mismo scope que huevos_hoy',
                'exclusiones' => 'anulados',
                'ausencia' => '0',
                'implementacion' => 'AdminResumenService::for',
            ],
            'muertes_hoy' => [
                'pantalla' => 'Resumen, Inicio',
                'fuente' => 'registros_operativos.tipo=muertes',
                'unidad' => 'aves (entero)',
                'periodo' => 'día operativo actual',
                'poblacion' => 'mismo scope',
                'exclusiones' => 'anulados',
                'ausencia' => '0',
                'implementacion' => 'OperarioGalponResumenService → suma en AdminResumenService',
            ],
            'alimento_kg_hoy' => [
                'pantalla' => 'Resumen, gráficos semanales',
                'fuente' => 'registros_operativos.tipo=alimento, SUM(alimento_kg)',
                'unidad' => 'kg entregados (remito/camión); no consumo diario',
                'periodo' => 'día operativo actual (fecha de la carga, no reparto en el día)',
                'poblacion' => 'mismo scope',
                'exclusiones' => 'anulados; sin conversión alimenticia ni eficiencia huevos/kg (RES-11)',
                'ausencia' => '0.0 si no hubo entrega ese día; omisión en serie semanal = «—»',
                'implementacion' => 'AdminResumenService::for, ResumenGraficosSemanalesService',
            ],
            'aves_actuales' => [
                'pantalla' => 'Resumen',
                'fuente' => 'galpones.aves_actuales (saldo vivo persistido)',
                'unidad' => 'aves',
                'periodo' => 'instantáneo al consultar',
                'poblacion' => 'galpones en scope',
                'exclusiones' => 'no se infiere desde registros de mortalidad del día',
                'ausencia' => 'suma 0 si no hay galpones en scope',
                'implementacion' => 'OperarioGalponResumenService::resumen',
            ],
            'mortalidad_acumulada_pct' => [
                'pantalla' => 'Resumen, Inicio (alertas)',
                'fuente' => 'muertes operativas / población inicial del ciclo (MortalidadVentanaGalpon)',
                'unidad' => 'porcentaje (2 decimales)',
                'periodo' => 'ciclo del galpón (activos o último cierre; RES-05)',
                'poblacion' => 'por galpón; varios lotes activos = referencia agregada, no tasa por lote',
                'exclusiones' => 'registros anulados',
                'ausencia' => '0% si población inicial 0',
                'implementacion' => 'MortalidadVentanaGalpon::metricaParaGalpon',
            ],
            'alerta_mortalidad' => [
                'pantalla' => 'Resumen, Inicio',
                'fuente' => 'ResumenMetricasCatalog::superaReferenciaMortalidad',
                'unidad' => 'booleano / contador alertas_count',
                'periodo' => 'misma ventana que mortalidad acumulada',
                'poblacion' => 'por galpón',
                'exclusiones' => 'no implica diagnóstico sanitario (RES-06)',
                'ausencia' => 'false si ≤ umbral referencia 1,1 %',
                'implementacion' => 'AdminResumenService::for, pulsoFor',
            ],
            'huevos_ayer' => [
                'pantalla' => 'Inicio (pulso)',
                'fuente' => 'registros huevos, día operativo anterior',
                'unidad' => 'huevos',
                'periodo' => 'DiaOperativoEmpresa::ayerParaEmpresa',
                'poblacion' => 'galpones activos en scope sin filtro UI',
                'exclusiones' => 'anulados',
                'ausencia' => '0',
                'implementacion' => 'AdminResumenService::pulsoFor',
            ],
            'delta_huevos' => [
                'pantalla' => 'Inicio (pulso)',
                'fuente' => 'huevos_hoy − huevos_ayer',
                'unidad' => 'huevos; delta_huevos_pct si ayer > 0 y D03 completo hoy (RES-07)',
                'periodo' => 'hoy vs ayer operativo',
                'poblacion' => 'empresa',
                'exclusiones' => '—',
                'ausencia' => 'pct null si ayer = 0 o capturas productivas pendientes',
                'implementacion' => 'ComparacionHonestaPulso + AdminResumenService::pulsoFor',
            ],
            'galpones_sin_carga' => [
                'pantalla' => 'Inicio (pulso)',
                'fuente' => 'galpones con omisión D03 en huevos, muertes o descarte (`CompletitudDiariaD03`)',
                'unidad' => 'lista de galpones',
                'periodo' => 'día operativo actual',
                'poblacion' => 'todos los galpones del resumen base',
                'exclusiones' => 'alimento no cierra el día; registro o cero confirmado por tipo productivo',
                'ausencia' => 'lista vacía',
                'implementacion' => 'AdminResumenService::pulsoFor',
            ],
            'postura_semanal' => [
                'pantalla' => 'Resumen (gráfico)',
                'fuente' => 'ResumenGraficosSemanalesService — huevos aptos por día',
                'unidad' => 'huevos por punto',
                'periodo' => '7 días lógicos incluyendo hoy',
                'poblacion' => 'scope filtros granja/galpón',
                'exclusiones' => 'anulados; omisión ≠ cero (RES-08)',
                'ausencia' => 'value null / display —',
                'implementacion' => 'AdminResumenService::posturaSemanal',
            ],
            'graficos_semanales' => [
                'pantalla' => 'Resumen (tabla + gráficos)',
                'fuente' => 'ResumenSemanaOperativa + registros activos por día',
                'unidad' => 'aptos, huevos descarte, muertes, kg',
                'periodo' => '7 días lógicos',
                'poblacion' => 'scope filtros',
                'exclusiones' => 'anulados',
                'ausencia' => '— por métrica y día',
                'implementacion' => 'ResumenGraficosSemanalesService::for',
            ],
        ];
    }

    /**
     * Casos verificables en tests (RES-02) → clase de test.
     *
     * @return array<string, string>
     */
    public static function casosVerificables(): array
    {
        return [
            'agregacion_huevos_scope' => 'Tests\\Feature\\Services\\AdminResumenServiceTest::test_for_aggregates_kpis_for_company_galpones',
            'filtro_granja' => 'Tests\\Feature\\Services\\AdminResumenServiceTest::test_for_filters_by_granja_id',
            'mortalidad_umbral' => 'Tests\\Feature\\Services\\AdminResumenServiceTest::test_for_flags_mortality_alert_above_reference',
            'multiempresa' => 'Tests\\Feature\\Services\\AdminResumenServiceTest::test_for_excludes_other_company_galpones',
            'pulso_sin_carga' => 'Tests\\Feature\\Services\\AdminResumenServiceTest::test_pulso_for_lists_galpones_with_d03_omission',
            'pulso_alimento_no_completa_d03' => 'Tests\\Feature\\Services\\AdminResumenServiceTest::test_pulso_for_alimento_solo_no_completa_d03',
            'pulso_d03_completo' => 'Tests\\Feature\\Services\\AdminResumenServiceTest::test_pulso_for_ok_when_d03_complete',
            'conciliacion_totales_dia' => 'Tests\\Feature\\Services\\AdminResumenTotalesConciliacionTest::test_inicio_resumen_historial_coinciden_en_fixture_conocido',
            'conciliacion_anulados' => 'Tests\\Feature\\Services\\AdminResumenTotalesConciliacionTest::test_anulados_excluidos_de_totales_conciliados',
            'conciliacion_correccion' => 'Tests\\Feature\\Services\\AdminResumenTotalesConciliacionTest::test_correccion_supervisor_reflejada_igual_en_tres_fuentes',
            'mortalidad_post_cierre' => 'Tests\\Feature\\Services\\AdminResumenMortalidadVentanaTest::test_cierre_lote_no_resetea_mortalidad_ni_denominador',
            'mortalidad_solo_galpon' => 'Tests\\Feature\\Services\\AdminResumenMortalidadVentanaTest::test_varios_lotes_activos_marca_solo_galpon_sin_tasa_por_lote',
            'pulso_ayer' => 'Tests\\Feature\\Services\\AdminResumenServiceTest::test_pulso_for_compares_huevos_with_yesterday',
            'comparacion_honesta_d03' => 'Tests\\Feature\\Services\\AdminResumenServiceTest::test_pulso_for_no_pct_when_d03_incomplete_res07',
            'comparacion_honesta_base' => 'Tests\\Feature\\Services\\AdminResumenServiceTest::test_pulso_for_null_pct_when_yesterday_zero_res07',
            'comparacion_honesta_unidad' => 'Tests\\Unit\\Support\\ComparacionHonestaPulsoTest::test_delta_huevos_pct_res07',
            'postura_7_dias' => 'Tests\\Feature\\Services\\AdminResumenServiceTest::test_postura_semanal_sums_huevos_by_day_for_scope',
            'graficos_omision' => 'Tests\\Feature\\Services\\AdminResumenServiceTest::test_graficos_semanales_dia_sin_carga_no_es_cero_res08',
            'graficos_cero_confirmado' => 'Tests\\Feature\\Services\\AdminResumenServiceTest::test_graficos_semanales_cero_confirmado_visible_res08',
            'aves_actuales_galpon' => 'Tests\\Feature\\Services\\AdminResumenMetricasContractTest::test_aves_actuales_usa_saldo_galpon',
            'anulados_excluidos' => 'Tests\\Feature\\Services\\AdminResumenMetricasContractTest::test_registros_anulados_no_suman_en_huevos_hoy',
            'referencia_mortalidad_ui' => 'Tests\\Feature\\Admin\\AdminResumenTest::test_resumen_muestra_referencia_mortalidad_sin_diagnostico',
            'umbral_mortalidad_catalogo' => 'Tests\\Unit\\Support\\ResumenMetricasCatalogTest::test_referencia_mortalidad_y_umbral_res06',
            'alimento_entrega_no_consumo' => 'Tests\\Feature\\Admin\\AdminResumenTest::test_resumen_alimento_entrega_no_consumo_res11',
            'alimento_semantica_v1' => 'Tests\\Unit\\Support\\AlimentoEntregaSemanticaTest::test_conversion_prohibida_en_v1',
        ];
    }
}
