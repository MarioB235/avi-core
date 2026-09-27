# CAP-03 — Muertes transaccionales

**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/emp-01-alta-empresa`

## Objetivo

Lock de saldo y error conservando entrada; solicitudes simultáneas no dejan saldo negativo.

## Implementación

| Pieza | Detalle |
|-------|---------|
| `RegistrarCargaMuertesAction` | `lockForUpdate` + `idempotencia_clave` (mismo patrón que huevos) |
| `ManagesMuertesForm` | UUID por apertura; `ValidationException` conserva diálogo y valor |
| UI `carga-muertes-form` | Saldo vivo, muertes hoy, confirmación con saldo restante, `wire:model.live` |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Operario/OperarioCargaMuertesCap03Test.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

**Resultado:** 725/725 tests OK (suite al cierre CAP-04).

## Siguiente

**CAP-04** — descarte de aves.
