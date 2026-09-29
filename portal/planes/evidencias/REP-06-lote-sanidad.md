# REP-06 — Historia de lote y sanidad básica

| Campo | Valor |
|--------|--------|
| ID | REP-06 |
| Estado | VERIFICADA |
| Fecha | 2026-09-29 |

## Objetivo

Exportar trazabilidad de lote y listado de vacunaciones sin repartir huevos del galpón entre varios lotes activos.

## Implementación

| Pieza | Detalle |
|--------|---------|
| `ReporteConsultaService::historiaLote` | Ficha EST-10 + ubicaciones MOV-10 |
| `ReporteConsultaService::sanidadBasica` | Vacunaciones activas/anuladas |
| Rutas | `historia-lote.xlsx`, `sanidad-basica.xlsx` |

## Verificación

- `ReporteHistoriaLoteSanidadTest` — 4 tests OK
- Suite **1031/1031** (3762 aserciones)
- `pnpm run check:agent-docs` OK

## Siguiente

REP-07 — vacíos y extremos.
