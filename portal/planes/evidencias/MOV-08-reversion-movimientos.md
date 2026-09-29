# MOV-08 — Reversión de movimientos

**ID:** MOV-08  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness` (cambios locales sin commit)

## Objetivo

Revertir movimientos activos con enlace `reversion` ↔ `reversado`, validando posteriores y saldo vivo sin duplicar ni dejar negativos.

## Implementación

| Área | Cambio |
|------|--------|
| Acción | `RevertirMovimientoAvesAction` |
| Idempotencia | `reversion-movimiento:{movimiento_id}` |
| Validación | Posterior operativo en galpón; excluye `saldo_inicial_lote` |
| Tests | `MovimientoAvesReversionTest` (7 casos) |

## Prueba de cierre

```bash
php artisan test tests/Feature/Movimientos/MovimientoAvesReversionTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- Tests MOV-08: **7/7** OK
- Suite completa: **918/918** OK · 3171 aserciones · Pint OK

## Contrato

- `movimientos-aves.md` § MOV-08 · `reglas.md` §11.2i

## Siguiente ID

**MOV-09** — Conciliación acumulada.
