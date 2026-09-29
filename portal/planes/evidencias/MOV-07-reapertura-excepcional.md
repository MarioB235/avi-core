# MOV-07 — Reapertura excepcional

**ID:** MOV-07  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness` (cambios locales sin commit)

## Objetivo

Reabrir lote cerrado con permiso superior, motivo auditado y restauración de aves sin segundo saldo inicial; rechazar conflicto con ciclo activo.

## Implementación

| Área | Cambio |
|------|--------|
| Acción | `ReabrirLoteExcepcionalAction` |
| Ledger | Entrada `reapertura_lote` + idempotencia `reapertura-lote:{id}` |
| Permisos | Dueño/Administrativo; encargado 403 |
| Tests | `MovimientoAvesReaperturaLoteTest` (5 casos) |

## Prueba de cierre

```bash
php artisan test tests/Feature/Movimientos/MovimientoAvesReaperturaLoteTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- Tests MOV-07: **5/5** OK
- Suite completa: **911/911** OK · 3154 aserciones · Pint OK

## Contrato

- `movimientos-aves.md` § MOV-07 · `reglas.md` §11.2h · `permisos.md`

## Siguiente ID

**MOV-08** — Reversión de movimientos.
