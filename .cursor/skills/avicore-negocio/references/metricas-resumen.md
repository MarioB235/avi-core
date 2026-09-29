# Métricas canónicas — Inicio y Resumen (RES-02)

Referencia de producto alineada con `App\Support\ResumenMetricasCatalog` y tests en `AdminResumenServiceTest` / `AdminResumenMetricasContractTest`.

## Alcance

| Pantalla | Servicio principal | Población base |
|----------|-------------------|----------------|
| Resumen (`/{rol}/resumen`) | `AdminResumenService::for` | Galpones `disponiblesParaCarga()` de la empresa del actor, filtros granja/galpón |
| Inicio pulso | `AdminResumenService::pulsoFor` | Mismo universo sin filtros UI |
| Postura semanal | `AdminResumenService::posturaSemanal` | Mismo scope que Resumen con filtros |

**Día operativo:** `DiaOperativoEmpresa` + scope `delDia` en `RegistroOperativo` (zona horaria de la empresa).

**Registros:** solo `activos()` (sin `anulado_at`).

## Catálogo

| ID | Unidad | Período | Fuente resumida |
|----|--------|---------|-----------------|
| `huevos_hoy` | huevos | hoy operativo | SUM registros tipo huevos |
| `huevos_descarte_hoy` | huevos descarte | hoy | campo `huevos_descarte` en cargas huevos |
| `muertes_hoy` | aves | hoy | tipo muertes |
| `alimento_kg_hoy` | kg | hoy | tipo alimento |
| `aves_actuales` | aves | instantáneo | `galpones.aves_actuales` (no inferido de registros) |
| `mortalidad_acumulada_pct` | % | ciclo activo o último cierre (RES-05) | `MortalidadVentanaGalpon`; varios lotes activos = solo galpón |
| `alerta_mortalidad` | flag | igual mortalidad | > `UMBRAL_MORTALIDAD_REFERENCIA_PCT` (1,1 % referencia UI) |
| `huevos_ayer` | huevos | ayer operativo | pulso |
| `delta_huevos` | huevos / % | hoy vs ayer | pulso |
| `galpones_sin_carga` | lista | hoy | galpones con omisión D03 en huevos, muertes o descarte (alimento no cuenta) |
| `postura_semanal` | huevos/día | 7 días lógicos | solo tipo huevos |

Detalle campo a campo (exclusiones, ausencia, implementación): `ResumenMetricasCatalog::definiciones()`.

## Casos verificables

| Caso | Test |
|------|------|
| Agregación huevos en empresa | `AdminResumenServiceTest::test_for_aggregates_kpis_for_company_galpones` |
| Filtro granja | `test_for_filters_by_granja_id` |
| Alerta mortalidad | `test_for_flags_mortality_alert_above_reference` |
| Aislamiento multiempresa | `test_for_excludes_other_company_galpones` |
| Pulso omisión D03 | `test_pulso_for_lists_galpones_with_d03_omission` |
| Alimento solo no completa día | `test_pulso_for_alimento_solo_no_completa_d03` |
| Pulso OK con D03 completo | `test_pulso_for_ok_when_d03_complete` |
| Conciliación Inicio/Resumen/Historial | `AdminResumenTotalesConciliacionTest` |
| Servicio canónico RES-04 aislado | `TotalesCapturaDiaServiceTest` |
| Comparación ayer | `test_pulso_for_compares_huevos_with_yesterday` |
| Gráfico 7 días | `test_postura_semanal_sums_huevos_by_day_for_scope` |
| Aves actuales = saldo galpón | `AdminResumenMetricasContractTest::test_aves_actuales_usa_saldo_galpon` |
| Anulados excluidos | `AdminResumenMetricasContractTest::test_registros_anulados_no_suman_en_huevos_hoy` |

Mapa máquina: `ResumenMetricasCatalog::casosVerificables()`.

## Fuera de alcance (v1)

- Stock/demanda comercial (RES-01).
- Tasa de mortalidad **por lote** cuando hay varios activos (D01 — solo galpón o lote único en ficha).
- Comparaciones honestas pulso (RES-07).

## Verificación

```bash
php artisan test tests/Unit/Support/ResumenMetricasCatalogTest.php tests/Unit/Support/CompletitudDiariaD03Test.php tests/Feature/Services/AdminResumenMetricasContractTest.php tests/Feature/Services/AdminResumenServiceTest.php tests/Feature/Services/TotalesCapturaDiaServiceTest.php tests/Feature/Services/AdminResumenTotalesConciliacionTest.php
```

## Completitud diaria D03 (RES-03)

- **Tipos obligatorios por galpón y día:** huevos, muertes, descarte — cada uno en `registrado` o `cero_confirmado` (`CapturaCeroEstado`).
- **Alimento:** opcional; no sustituye capturas productivas ni habilita pulso «Todo en orden».
- **Implementación pulso:** `CompletitudDiariaD03::tieneOmisionProductiva` sobre el resumen de `OperarioGalponResumenService`.
- Reglas operativas: `reglas.md` §8.8 y §19.

## Conciliación de totales (RES-04)

- **Fuente canónica:** `TotalesCapturaDiaService::paraUsuario` — registros `activos()`, día operativo empresa, galpones `disponiblesParaCarga` en scope.
- **Consumidores:** KPIs de `AdminResumenService::for`, pulso/teaser/Inicio, `AdminHistorialOperativoService::totalesCapturaDiaActiva`.
- **Anulados:** no suman; **correcciones:** valores vigentes en el registro (post `CorregirRegistroOperativoAction`).
- Tests: `AdminResumenTotalesConciliacionTest`, `TotalesCapturaDiaServiceTest`; reglas `reglas.md` §20.

## Mortalidad y ventana (RES-05)

- Servicio: `MortalidadVentanaGalpon::metricaParaGalpon`.
- Flags en filas Resumen: `mortalidad_solo_galpon`, `mortalidad_incluye_cerrados` (copy en tarjetas).
- Reglas: `reglas.md` §21 · tests `AdminResumenMortalidadVentanaTest`.

## Umbrales de referencia (RES-06)

- Umbral 1,1 % y textos: `ResumenMetricasCatalog::referenciaMortalidad()` + `superaReferenciaMortalidad()`.
- Período asociado: ciclo del galpón (RES-05), no un día suelto.
- UI sin lenguaje de diagnóstico; decisión del responsable de la empresa.
- Reglas: `reglas.md` §22.
