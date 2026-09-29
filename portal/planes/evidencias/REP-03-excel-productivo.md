# REP-03 — Excel productivo (producción diaria)

| Campo | Valor |
|--------|--------|
| ID | REP-03 |
| Estado | VERIFICADA |
| Fecha | 2026-09-29 |

## Objetivo

Exportar producción diaria en `.xlsx` con fechas/números nativos, mismos totales que `ReporteConsultaService`.

## Implementación

| Pieza | Detalle |
|--------|---------|
| `openspout/openspout` | Generación XLSX |
| `ReporteProduccionDiariaExcelExporter` | Hoja con cabecera, datos y total |
| Ruta | `/{rol}/reportes/produccion-diaria.xlsx` |
| UI | Enlace «Exportar Excel (hoy)» en Resumen |

## Verificación

```bash
php artisan test --compact tests/Feature/Reportes/ReporteProduccionDiariaExcelTest.php tests/Unit/Support/ExcelExportSeguroTest.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

Resultado 2026-09-29: **1021/1021** tests, Pint OK, `check:agent-docs` OK.

## Siguiente

REP-04 — PDF operativo.
