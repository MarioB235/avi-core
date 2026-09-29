# MOV-11 — Concurrencia real (PostgreSQL)

**ID:** MOV-11  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness` (sin commit)

## Objetivo

Validar con **dos sesiones PostgreSQL** que traslados, cierres y muertes concurrentes no duplican efectos ni dejan saldos negativos.

## Implementación

| Área | Cambio |
|------|--------|
| Tests | `MovimientoAvesConcurrenciaTest` (4 escenarios) |
| Workers | `tests/Support/movimiento_concurrencia_worker.php` + `RunsMovimientoConcurrentWorkers` (procesos PHP paralelos) |
| Sesión B | Conexión `pgsql_concurrent` + `lock_timeout` para contención explícita |
| Transacción test | `connectionsToTransact = []` para que los workers vean datos commitados |

## Verificación

```bash
php artisan test tests/Feature/Movimientos/MovimientoAvesConcurrenciaTest.php
php artisan test tests/Feature/Movimientos/
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

| Comando | Resultado |
|---------|-----------|
| `MovimientoAvesConcurrenciaTest` | 4/4 OK (requiere `pgsql`) |
| `tests/Feature/Movimientos/` | 59/59 OK |
| Suite completa | **929/929** OK · 3235 aserciones |
| Pint / `check:agent-docs` | OK |

## Contrato

- `movimientos-aves.md` § MOV-11 · `reglas.md` §11.2l

## Siguiente

**MOV-12** — UI de supervisor (vista previa + motivo obligatorio).
