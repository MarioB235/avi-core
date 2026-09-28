# MOV-03 — Entrada y saldo inicial

**ID:** MOV-03  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/aud-historial-operario`

## Objetivo

Vincular el alta de lote con el ledger de movimientos sin duplicar `aves_actuales`; entradas externas idempotentes.

## Implementación

| Área | Cambio |
|------|--------|
| Alta lote | `RegistrarLoteAction` → `RegistrarSaldoInicialLoteAction` (movimiento `saldo_inicial_lote`, sin segundo incremento) |
| Entrada externa | `RegistrarEntradaAvesAction` incrementa saldo bajo lock + auditoría |
| Idempotencia | `idempotencia_clave` en `movimientos_aves`; clave fija `saldo-inicial-lote:{id}` |
| Enum | `MovimientoAvesOrigen` — `impactaAvesActuales()` |
| Policy | `MovimientoAvesPolicy` (encargado+) |

## Prueba de cierre

```bash
php artisan test tests/Feature/Movimientos/MovimientoAvesEntradaSaldoInicialTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- Tests MOV-03: **4/4** OK
- Suite completa: **867/867** OK · 3021 aserciones · Pint OK · `check:agent-docs` OK

## Contrato

- `movimientos-aves.md` § MOV-03 · `reglas.md` §11.2d · `esquema-bd.md`

## Siguiente ID

**MOV-04** — Traslado atómico entre galpones.
