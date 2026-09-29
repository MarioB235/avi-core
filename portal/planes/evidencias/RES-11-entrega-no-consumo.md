# RES-11 — Entrega no es consumo

| Campo | Valor |
|--------|--------|
| ID | RES-11 |
| Estado | VERIFICADA |
| Fecha | 2026-09-29 |

## Objetivo

Kg de alimento en Resumen = entregas de camión; v1 no calcula ni muestra conversión alimenticia o eficiencia no medida.

## Implementación

| Pieza | Detalle |
|--------|---------|
| `AlimentoEntregaSemantica` | Copy UI + lista `METRICAS_PROHIBIDAS_V1` |
| `ResumenMetricasCatalog` | `referenciaAlimentoEntregado()`, definición `alimento_kg_hoy` |
| UI Resumen | KPI, disclaimer, tabla semanal y por galpón «Kg entregados» |
| Export (contrato) | `reportes.md` regla 6 |

## Verificación

```bash
php artisan test --compact tests/Unit/Support/AlimentoEntregaSemanticaTest.php tests/Feature/Admin/AdminResumenTest.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

Resultado 2026-09-29: **1011/1011** tests, Pint OK, `check:agent-docs` OK.

## Siguiente

Cerrar bloque RES si no hay más ítems P1; revisar plan maestro §15 Resumen.
