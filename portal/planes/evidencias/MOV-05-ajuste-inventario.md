# MOV-05 — Ajuste de inventario

**ID:** MOV-05  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness` (cambios locales sin commit)

## Objetivo

Alinear saldo vivo del galpón con conteo físico mediante movimiento `ajuste`, con motivo y rol encargado+; sin modificar registros de mortalidad.

## Implementación

| Área | Cambio |
|------|--------|
| Acción | `RegistrarAjusteInventarioAvesAction` — delta = conteo − `aves_actuales` bajo lock |
| Origen enum | `MovimientoAvesOrigen::AjusteInventario` |
| Auditoría | `ajuste_inventario` en categoría `Movimiento` |
| Tests | `MovimientoAvesAjusteInventarioTest` (6 casos, incl. muertes intactas) |

## Prueba de cierre

```bash
php artisan test tests/Feature/Movimientos/MovimientoAvesAjusteInventarioTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- Tests MOV-05: **6/6** OK
- Suite completa: **899/899** OK · 3124 aserciones · Pint OK · `check:agent-docs` OK

## Contrato

- `movimientos-aves.md` § MOV-05 · `reglas.md` §11.2f

## Siguiente ID

**MOV-06** — Cierre de lote.
