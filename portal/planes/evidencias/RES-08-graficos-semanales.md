# RES-08 — Gráficos y tabla semanal

**ID:** RES-08 · **Estado:** VERIFICADA  
**Fecha:** 2026-09-29 · **Rama:** `fix/demo-login-seed-readiness`

## Objetivo

Semana operativa con aptos, descarte, muertes y kg; tabla accesible; día sin carga ≠ cero confirmado.

## Resultado observable

- Sección «Semana operativa» en `/admin/resumen` con tabla y 4 gráficos.
- Celdas «—» sin registro; `0 (confirmado)` cuando aplica.
- `x-ui.line-chart` no traza puntos con `value` null.

## Archivos clave

- `app/Services/ResumenGraficosSemanalesService.php`
- `app/Support/ResumenSemanaOperativa.php`
- `resources/views/livewire/admin/resumen/partials/semana-operativa.blade.php`
- `resources/views/components/ui/line-chart.blade.php`

## Verificación

```bash
php artisan test tests/Unit/Support/ResumenSemanaOperativaTest.php tests/Feature/Services/AdminResumenServiceTest.php tests/Feature/Ui/LineChartComponentTest.php tests/Feature/Admin/AdminResumenTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Siguiente ID

RES-09 — excepciones primero en Inicio.
