# REP-04 — PDF operativo (producción diaria)

| Campo | Valor |
|--------|--------|
| ID | REP-04 |
| Estado | VERIFICADA |
| Fecha | 2026-09-29 |

## Objetivo

PDF A4 descargable con mismos totales que `ReporteConsultaService`, cabecera empresa/DICOSE y pie AviCore.

## Implementación

| Pieza | Detalle |
|--------|---------|
| `setasign/fpdf` | Generación PDF sin dompdf (advisories Composer) |
| `fpdf_magic_quotes_polyfill.php` | Compat PHP 8.3 (magic quotes removidas) |
| `ReporteProduccionDiariaPdfExporter` | Tabla + metadatos |
| Ruta | `/{rol}/reportes/produccion-diaria.pdf` |

## Verificación

```bash
php artisan test --compact tests/Feature/Reportes/ReporteProduccionDiariaPdfTest.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Siguiente

REP-05 — movimientos/conciliación.
