# CAP-02 — Huevos punta a punta

**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/emp-01-alta-empresa`

## Objetivo

Cantidades, teclado numérico, confirmación con desglose y acumulado del día; dos cargas nuevas suman; reintento no duplica.

## Implementación

| Pieza | Detalle |
|-------|---------|
| Migración | `idempotencia_clave` en `registros_operativos` (única por empresa) |
| `RegistrarCargaHuevosAction` | Devuelve registro existente si la clave ya fue usada |
| `ManagesHuevosForm` | UUID por apertura de diálogo; pasa clave al guardar |
| UI `carga-huevos-form` | Acumulado «Llevás hoy…», confirmación con maples, `inputmode="numeric"` |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Operario/OperarioCargaHuevosCap02Test.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

**Resultado:** 713/713 tests OK.

## Siguiente

**CAP-03** — muertes transaccionales.
