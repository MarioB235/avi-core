# RES-06 — Umbrales de referencia

**ID:** RES-06  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness`

## Objetivo

1,1 % como referencia orientativa (encuestas), período = ciclo del galpón; UI sin diagnóstico ni norma inventada.

## Cambios

| Pieza | Detalle |
|-------|---------|
| `ResumenMetricasCatalog` | `referenciaMortalidad()`, `superaReferenciaMortalidad()` |
| Resumen / Inicio | KPI «Galpones sobre referencia», badge «Sobre referencia», disclaimer |
| `AdminResumenService` | Alertas vía catálogo |

## Verificación

```bash
php artisan test tests/Unit/Support/ResumenMetricasCatalogTest.php tests/Feature/Admin/AdminResumenTest.php
php artisan test
```

## Siguiente

RES-07 — comparaciones honestas (hoy vs ayer, denominador cero).
