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
| reversion | sin delta en ledger; el original `reversado` deja de contar; `RevertirMovimientoAvesAction` invierte `aves_actuales` cuando `impacta_aves_actuales` |

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

## MOV-04 — Traslado

| Aspecto | Decisión |
|---------|----------|
| Acción | `RegistrarTrasladoAvesAction` |
| Validación | Misma empresa; origen ≠ destino; lote activo en origen; destino disponible para carga; conciliación D01 vía `assertEstructuraMinima` |
| Saldo | `decrement` origen + `increment` destino bajo `GalponValidacion::bloquearParOrdenado` (ids ascendentes) |
| Idempotencia | `idempotencia_clave` opcional (mismo patrón que entrada) |
| Metadata | `origen`: `traslado_operativo`; `impacta_aves_actuales`: true; imputaciones D01 si aplica; `cambio_ubicacion_expediente` si agota remanente del lote en origen (MOV-10) |

**Auditoría:** acción `traslado` en categoría `Movimiento`. **Tests:** `MovimientoAvesTrasladoTest`.

## MOV-05 — Ajuste de inventario

| Aspecto | Decisión |
|---------|----------|
| Acción | `RegistrarAjusteInventarioAvesAction` |
| Entrada | `conteo_fisico` vs `aves_actuales` del galpón bajo lock; `ajuste_delta = conteo − sistema` |
| Regla | No modifica `registros_operativos` (muertes/descarte); solo ledger + saldo vivo |
| Rol | Encargado o superior (`MovimientoAvesPolicy::create`) |
| Idempotencia | `idempotencia_clave` opcional |
| Metadata | `ajuste_inventario`, `conteo_fisico`, `saldo_sistema_antes`, `ajuste_delta` |

**Auditoría:** acción `ajuste_inventario`. **Tests:** `MovimientoAvesAjusteInventarioTest`.

## MOV-06 — Cierre de lote

| Aspecto | Decisión |
|---------|----------|
| Acción | `RegistrarCierreLoteAction` |
| Salida | Movimiento `cierre_lote` −cantidad en galpón; `destino_salida` y motivo (D07) en metadata |
| Ciclo | `cerrarCicloLote=true` exige remanente completo (D01) y transición a `LoteEstado::Cerrado` vía `TransicionarLoteEstadoAction` |
| Parcial | `cerrarCicloLote=false` solo registra salida sin cerrar el lote |
| Idempotencia | Reintento con clave devuelve movimiento previo (`IdempotenciaMovimiento::buscarExistente`) |

**Auditoría:** `cierre_lote` o `salida_cierre_parcial`. **Tests:** `MovimientoAvesCierreLoteTest`.

## MOV-07 — Reapertura excepcional

| Aspecto | Decisión |
|---------|----------|
| Acción | `ReabrirLoteExcepcionalAction` (Lote) |
| Permiso | Dueño/Administrativo (`canReabrirLote` + `LotePolicy::transition`) |
| Restauración | Entrada con `reapertura_lote` según último `cierre_lote` de ciclo; idempotencia `reapertura-lote:{id}` — **no** segundo `saldo_inicial_lote` |
| Conflictos | Rechaza si hay otro lote activo en el galpón o aves vivas con ciclo nuevo |

**Auditoría:** `reapertura_excepcional` (categoría Lote). **Tests:** `MovimientoAvesReaperturaLoteTest`.

## MOV-08 — Reversión

| Aspecto | Decisión |
|---------|----------|
| Acción | `RevertirMovimientoAvesAction` |
| Enlace | Movimiento `reversion` + original `reversado` / `reversado_por_id` |
| Reglas | Sin posteriores operativos en galpón (excluye `saldo_inicial_lote`); sin saldo negativo; no revertir `reversion` ni saldo inicial |
| Idempotencia | `reversion-movimiento:{id}` |

**Auditoría:** acción `reversion`. **Tests:** `MovimientoAvesReversionTest`.

## MOV-09 — Conciliación acumulada

| Aspecto | Decisión |
|---------|----------|
| Servicio | `MovimientoAvesConciliacionService::conciliacionAcumulada()` |
| Fórmula | `inicial + entradas − salidas − muertes − descartes + ajustes = saldo_esperado` vs `aves_actuales` |
| Inicial | Suma `cantidad_inicial` de lotes activos en el galpón (no duplica `saldo_inicial_lote` en entradas) |
| Período | Por defecto desde `fecha_ingreso` del lote activo más antiguo hasta hoy; filtros opcionales `desde`/`hasta` |
| Reversiones | El original `reversado` deja de contar en buckets; `reversiones_registradas` es informativo |
| Salida | `diferencia`, `cuadra` para supervisor/reportes (MOV-12/REP-05) |

**Tests:** `MovimientoAvesConciliacionAcumuladaTest`.

## MOV-10 — Ubicación histórica

| Aspecto | Decisión |
|---------|----------|
| Servicio | `LoteUbicacionHistoricaService` |
| Consulta | `galponEnMomento()`, `segmentosUbicacion()` desde saldo inicial en ledger + traslados con `metadata.cambio_ubicacion_expediente` |
| Hechos operativos | `galponDelHechoOperativo()` / agregados por `registro.galpon_id` — no se reasignan al destino tras un traslado |
| Traslado total | Si la cantidad agota el remanente del lote en origen, `RegistrarTrasladoAvesAction` actualiza `lote.galpon_id` y marca metadata |

**Tests:** `MovimientoAvesUbicacionHistoricaTest`.

## MOV-11 — Concurrencia (PostgreSQL)

| Aspecto | Decisión |
|---------|----------|
| Locks | `GalponValidacion::bloquearParOrdenado` + `lockForUpdate` en Actions existentes |
| Verificación | `MovimientoAvesConcurrenciaTest` — workers en procesos paralelos (dos sesiones PG) + sesión alterna con `lock_timeout` |
| Escenarios | Traslados que compiten por saldo, doble cierre de ciclo, muertes vs traslado, contención de locks |

**Requisito CI/local:** `DB_CONNECTION=pgsql` · sin transacción envolvente en esos tests (`connectionsToTransact = []`).

## MOV-12 — UI supervisor

| Aspecto | Decisión |
|---------|----------|
| Ruta | `/{rol}/movimientos` — Livewire `Admin\Movimientos\Index` |
| Gate | `admin.viewMovimientos` (`AdminModulePolicy::viewMovimientos`) — dueño, administrativo, encargado con `canManageLotes`; operario sin acceso; soporte bloqueado si `blocksProductionMutations` |
| Tipos | Traslado, entrada externa, ajuste, cierre de lote, salida a faena (reapertura/reversión fuera de esta pantalla MVP) |
| Vista previa | `MovimientoAvesVistaPreviaService` — saldos origen/destino, delta y avisos antes de confirmar |
| Motivo | Obligatorio (mín. 10 caracteres) para abrir diálogo de confirmación y ejecutar |
| Ejecución | Actions existentes (`RegistrarTrasladoAvesAction`, etc.) con `authorize` de `MovimientoAvesPolicy` |
| Render | Vista previa solo se calcula en `render` cuando hay datos en el formulario (formulario vacío → placeholder) |

**Tests:** `AdminMovimientosSupervisorTest` (traslado, entrada externa, ajuste, cierre parcial, faena parcial, 403 operario), `MovimientoAvesVistaPreviaServiceTest`.

## MOV-13 — Faena (salida operativa)

| Aspecto | Decisión |
|---------|----------|
| Acción | `RegistrarFaenaAction` |
| Ledger | Tipo `faena` −cantidad en galpón origen; mismas reglas D01 que cierre/traslado |
| Trazabilidad | `metadata.destino_faena` obligatorio; `referencia_remito` / `referencia_documento` opcionales; `trazabilidad_interna: true` |
| SMA / D05 | Documentos oficiales y envío SMA quedan fuera del MVP — sin integración automática |
| Ciclo | `cerrarCicloLote` (default true) exige remanente completo y cierra el lote; parcial deja lote activo |
| Reapertura | `ReabrirLoteExcepcionalAction` considera último cierre de ciclo vía `cierre_lote` **o** `faena` |
| UI | Tipo «Salida a faena» en `/{rol}/movimientos` con vista previa (incluye aviso sin envío SMA) |

**Auditoría:** `faena_cierre_ciclo` / `faena_parcial`. **Tests:** `MovimientoAvesFaenaTest`.

## Verificación

```bash
php artisan test tests/Feature/Movimientos/ tests/Feature/Admin/AdminMovimientosSupervisorTest.php tests/Unit/Services/MovimientoAvesVistaPreviaServiceTest.php tests/Unit/Policies/MovimientoAvesPolicyTest.php
```
