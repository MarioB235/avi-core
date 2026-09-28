# MOV-01 — Modelo mínimo de movimientos

**ID:** MOV-01  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/aud-historial-operario`

## Objetivo

Tabla `movimientos_aves` con origen/destino, lote opcional, cantidad/tipo, momento efectivo, actor, motivo y enlaces de reversión; permitir reconstruir saldo por galpón.

## Implementación

| Área | Cambio |
|------|--------|
| BD | Migración `movimientos_aves` con FK RESTRICT e índices por empresa/galpón/lote/fecha |
| Enums | `MovimientoAvesTipo`, `MovimientoAvesEstado` |
| Modelo | `MovimientoAves` + relaciones en `Galpon`/`Lote` + `PreventsHardDelete` |
| Saldo | `MovimientoAvesEfecto::saldoNetoPorGalpon()` |
| Validación | `MovimientoAvesValidacion::assertEstructuraMinima()` |
| Factory | `MovimientoAvesFactory` con estados entrada/traslado/ajuste |

## Escenario de cierre

Entrada 5000 → traslado 800 → ajuste −15 → faena 120:

- Galpón A: **4065** neto por movimientos
- Galpón B: **800** neto

## Prueba de cierre

```bash
php artisan test tests/Feature/Movimientos/MovimientoAvesModeloTest.php tests/Unit/Support/MovimientoAvesEfectoTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- Tests MOV: **5/5** OK
- Suite completa: **856/856** OK · 2989 aserciones · Pint OK · `check:agent-docs` OK

## Contrato

- `esquema-bd.md` · `movimientos-aves.md` · `reglas.md` §11 · `criterios-modelo.md`

## Siguiente ID

**MOV-02** — Población por lote con D01 — ver [MOV-02-poblacion-lote-d01.md](MOV-02-poblacion-lote-d01.md).
