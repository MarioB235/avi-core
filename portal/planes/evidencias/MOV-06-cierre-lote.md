# MOV-06 — Cierre de lote

**ID:** MOV-06  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness` (cambios locales sin commit)

## Objetivo

Registrar salida de remanente con movimiento `cierre_lote`, motivo/destino D07 y cierre de ciclo del lote sin aves fantasma ni carga incompatible.

## Implementación

| Área | Cambio |
|------|--------|
| Acción | `RegistrarCierreLoteAction` + integración `TransicionarLoteEstadoAction` |
| Origen enum | `MovimientoAvesOrigen::CierreLoteOperativo` |
| Idempotencia | `IdempotenciaMovimiento::buscarExistente` antes de validar lote activo |
| Tests | `MovimientoAvesCierreLoteTest` (7 casos) |

## Prueba de cierre

```bash
php artisan test tests/Feature/Movimientos/MovimientoAvesCierreLoteTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- Tests MOV-06: **7/7** OK
- Suite completa: **906/906** OK · 3142 aserciones · Pint OK

## Contrato

- `movimientos-aves.md` § MOV-06 · `reglas.md` §11.2g

## Siguiente ID

**MOV-07** — Reapertura excepcional.
