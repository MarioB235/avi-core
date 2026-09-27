# CAP-06 — Vacunación básica

**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/emp-01-alta-empresa`

## Objetivo

Lote/empresa/galpón vigente, tipo y detalle útil; anulación. Historia sanitaria sin inventar calendario o prescripción.

## Implementación

| Pieza | Detalle |
|-------|---------|
| Migración | `idempotencia_clave` en `vacunaciones` (única por empresa) |
| `RegistrarVacunacionAction` | Idempotencia + observación opcional (máx. 500) |
| `ManagesVacunacionForm` | UUID por apertura; errores conservan diálogo |
| UI `carga-vacunacion-form` | Confirmación lote+vacuna, contador del día, aviso sin calendario |
| `OperarioGalponResumenService` | `vacunaciones_hoy` (solo activas) |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Operario/OperarioCargaVacunacionCap06Test.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

**Resultado:** 746/746 tests OK.

## Siguiente

**CAP-07** — idempotencia transversal (o revisar si ya cubierta por CAP-02…06).
