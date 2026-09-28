# Movimientos de aves (MOV)

## MOV-01 — Modelo mínimo

| Aspecto | Decisión MVP |
|---------|----------------|
| Tabla | `movimientos_aves` — ledger inmutable (D07) |
| Tipos | `entrada`, `traslado`, `ajuste`, `cierre_lote`, `faena`, `reversion` |
| Origen/destino | `galpon_origen_id` / `galpon_destino_id` según tipo |
| Lote | `lote_id` opcional — identificación D01; sin reparto silencioso de muertes |
| Momento efectivo | `fecha_efectiva` |
| Actor/motivo | `registrado_por`, `motivo` obligatorio |
| Reversión | `reversa_de_id` / `reversado_por_id`; original pasa a `reversado` |
| Saldo | `MovimientoAvesEfecto` reconstruye delta por galpón; mortalidad sigue en `registros_operativos` |

## Efecto por tipo (reconstrucción)

| Tipo | Efecto |
|------|--------|
| entrada | +cantidad en destino |
| traslado | −cantidad origen, +cantidad destino |
| ajuste | `ajuste_delta` firmado en galpón origen |
| cierre_lote / faena | −cantidad en origen |
| reversion | sin delta — trazabilidad; el original `reversado` deja de contar (MOV-08 aplicará mutación) |

## MOV-02 — Población por lote (D01)

| Situación | Regla |
|-----------|--------|
| Un lote activo en galpón | `saldo_atribuible` = `aves_actuales` del galpón (`saldo_es_hecho: true`) |
| Varios lotes activos | `saldo_atribuible` = **null** — no estimar; imputar muertes/descarte en `metadata` |
| Traslado / cierre / faena | `MovimientoAvesValidacion::assertConciliacionD01()` exige `lote_id` y, si hay varios lotes, `muertes_imputadas_lote` |
| Saldo declarado | `cantidad_inicial` + movimientos del lote − imputaciones; nunca reparto automático de muertes del galpón |

Servicio: `MovimientoAvesConciliacionService::snapshot()` · saldo por lote: `MovimientoAvesLoteSaldo`.

## MOV-03 — Entrada y saldo inicial

| Origen | Acción | `aves_actuales` | Idempotencia |
|--------|--------|-----------------|--------------|
| Alta de lote | `RegistrarSaldoInicialLoteAction` (desde `RegistrarLoteAction`) | No — ya incrementó el alta de lote | `saldo-inicial-lote:{lote_id}` |
| Entrada externa | `RegistrarEntradaAvesAction` | Sí, bajo lock | `idempotencia_clave` UUID del cliente |

`metadata.origen`: `saldo_inicial_lote` | `entrada_externa` · `impacta_aves_actuales` documenta si el movimiento mutó saldo vivo.

**Permisos:** `MovimientoAvesPolicy::create` (encargado+; operario 403; soporte bloqueado). **Auditoría:** entrada externa registra categoría `Movimiento` vía `RegistrarAuditoriaAction`.

## Pendiente (MOV-04+)

- Traslado atómico con mutación de saldo (MOV-04)
- UI supervisor (MOV-12)
- Conciliación acumulada entre movimientos (MOV-09)
- Auditoría en traslado/ajuste/cierre (MOV-04+)

## Verificación

```bash
php artisan test tests/Feature/Movimientos/ tests/Unit/Policies/MovimientoAvesPolicyTest.php
```
