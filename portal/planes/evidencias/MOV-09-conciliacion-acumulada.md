# MOV-09 — Conciliación acumulada

**ID:** MOV-09  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness` (sin commit)

## Objetivo

Exponer por galpón la ecuación acordada: inicial + entradas − salidas − muertes − descartes + ajustes = saldo esperado, comparado con `aves_actuales` (`diferencia`, `cuadra`).

## Implementación

- `MovimientoAvesConciliacionService::conciliacionAcumulada(Galpon, ?desde, ?hasta)`
- Excluye `saldo_inicial_lote` de entradas (ya en `inicial` por suma de `cantidad_inicial`)
- Muertes/descarte desde `registros_operativos` en la ventana
- `reversiones_registradas`: conteo informativo; el original `reversado` no entra en buckets

## Verificación

```bash
php artisan test tests/Feature/Movimientos/MovimientoAvesConciliacionAcumuladaTest.php
php artisan test tests/Feature/Movimientos/
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

| Comando | Resultado |
|---------|-----------|
| `MovimientoAvesConciliacionAcumuladaTest` | 4/4 OK |
| `tests/Feature/Movimientos/` | 52/52 OK |
| Suite completa | **922/922** OK · 3197 aserciones |
| Pint | OK |
| `check:agent-docs` | OK |

## Contrato

- `.cursor/skills/avicore-negocio/references/movimientos-aves.md` § MOV-09
- `.cursor/skills/avicore-negocio/references/reglas.md` §11.2j
- `portal/CHANGELOG.md`

## Siguiente

**MOV-10** — según plan maestro (bloque MOV).
