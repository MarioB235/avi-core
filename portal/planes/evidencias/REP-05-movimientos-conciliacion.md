# REP-05 — Movimientos y conciliación (export)

| Campo | Valor |
|--------|--------|
| ID | REP-05 |
| Estado | VERIFICADA |
| Fecha | 2026-09-29 |

## Objetivo

Export Excel/PDF con conciliación acumulada (inicial/final) y ledger con reversiones identificadas; misma aritmética que MOV-09.

## Implementación

| Pieza | Detalle |
|--------|---------|
| `ReporteConsultaService::movimientosExistencias` | Bloques por galpón |
| `ledgerFilasParaReporte` | Filas con flag reversión |
| Rutas | `movimientos-existencias.xlsx` / `.pdf` |

## Verificación

- `ReporteMovimientosExistenciasTest` — 4 tests OK
- Suite completa **1027/1027** (3748 aserciones)
- `pnpm run check:agent-docs` OK

## Siguiente

REP-06 — historia de lote / sanidad.
