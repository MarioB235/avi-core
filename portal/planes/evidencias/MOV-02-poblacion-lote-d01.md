# MOV-02 — Población por lote (D01)

**ID:** MOV-02  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/aud-historial-operario`

## Objetivo

Conciliar muertes del galpón antes de trasladar o cerrar parcialmente; nunca presentar estimación de saldo por lote como hecho cuando hay varios lotes activos.

## Implementación

| Área | Cambio |
|------|--------|
| Servicio | `MovimientoAvesConciliacionService::snapshot()` — hechos del galpón + filas por lote |
| Saldo lote | `MovimientoAvesLoteSaldo` — delta atribuible por lote/galpón |
| Validación | `assertConciliacionD01()` en traslado/cierre/faena |
| Metadata | `muertes_imputadas_lote`, `descarte_imputado_lote` cuando hay varios lotes |
| Un lote activo | `saldo_es_hecho: true` = `aves_actuales` del galpón |

## Prueba de cierre

```bash
php artisan test tests/Feature/Movimientos/MovimientoAvesConciliacionD01Test.php tests/Unit/Support/MovimientoAvesLoteSaldoTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- Tests MOV-02: **7/7** OK
- Suite completa: **863/863** OK · Pint OK · `check:agent-docs` OK

## Contrato

- `movimientos-aves.md` § MOV-02 · `reglas.md` §11.2c

## Siguiente ID

**MOV-03** — Entrada y saldo inicial — ver [MOV-03-entrada-saldo-inicial.md](MOV-03-entrada-saldo-inicial.md).
