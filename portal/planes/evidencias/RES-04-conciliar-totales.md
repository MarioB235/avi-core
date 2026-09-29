# RES-04 — Conciliar totales Inicio / Resumen / Historial

**ID:** RES-04  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness`

## Objetivo

Mismo scope y día operativo: totales de captura coinciden en Inicio (pulso/teaser), Resumen (KPIs) e Historial (API de totales); anulados fuera; correcciones en valores vigentes.

## Cambios

| Pieza | Rol |
|-------|-----|
| `TotalesCapturaDiaService` | Agregación SQL canónica + `galponesEnScope` |
| `AdminResumenService::for` | KPIs del día desde el servicio canónico |
| `AdminHistorialOperativoService::totalesCapturaDiaActiva` | Misma fuente para supervisión |

## Verificación

```bash
php artisan test tests/Feature/Services/AdminResumenTotalesConciliacionTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Siguiente

RES-05 — mortalidad y ventana (movimientos / cierres).
