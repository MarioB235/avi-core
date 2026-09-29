# REP-07 — Vacíos y extremos en export

| Campo | Valor |
|--------|--------|
| ID | REP-07 |
| Estado | VERIFICADA |
| Fecha | 2026-09-29 |

## Objetivo

Vacío explícito cuando no hay filas; consulta inválida → 422, no archivo “exitoso” vacío.

## Piezas

- `ReporteEstadoConsulta`, `ReporteExportGuard`, `ReporteConsultaNoDisponibleException`
- Controllers con `GeneraDescargaReporte`
- `PdfTexto::latin1Recortado`

## Verificación

- `ReporteVaciosExtremosTest` — 4 tests OK
- Suite **1035/1035** (3773 aserciones)
- `pnpm run check:agent-docs` OK

## Siguiente

REP-08 — generación/descarga autorizadas.
