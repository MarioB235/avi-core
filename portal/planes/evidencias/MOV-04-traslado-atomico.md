# MOV-04 — Traslado atómico

**ID:** MOV-04  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness` (cambios locales sin commit)

## Objetivo

Trasladar aves entre galpones de la misma empresa con validación de saldo/lote (D01), destino operativo y conservación del total; fallo sin mutar saldos.

## Implementación

| Área | Cambio |
|------|--------|
| Acción | `RegistrarTrasladoAvesAction` — decrement/increment bajo transacción |
| Locks | `GalponValidacion::bloquearParOrdenado` (orden estable por id) |
| Origen enum | `MovimientoAvesOrigen::TrasladoOperativo` |
| Auditoría | `traslado` en categoría `Movimiento` |
| Tests | `MovimientoAvesTrasladoTest` (6 casos) |

## Prueba de cierre

```bash
php artisan test tests/Feature/Movimientos/MovimientoAvesTrasladoTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- Tests MOV-04: **6/6** OK
- Suite completa: **893/893** OK · 3106 aserciones · Pint OK · `check:agent-docs` OK

## Contrato

- `movimientos-aves.md` § MOV-04 · `reglas.md` §11.2e · `arbol-proyecto.md`

## Siguiente ID

**MOV-05** — Ajuste de inventario.
