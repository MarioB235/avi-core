# EST-06 — Separar edición y transición de lote

| Campo | Valor |
|---|---|
| ID | EST-06 |
| Estado | VERIFICADA |
| Fecha | 2026-09-26 |
| Rama | `feature/emp-01-alta-empresa` |

## Objetivo

`UpdateLoteAction` no cambia el estado del lote; las transiciones pasan por un circuito controlado con motivo y historial auditable.

## Implementación

| Pieza | Detalle |
|---|---|
| `UpdateLoteAction` | Solo SMA, raza y observación; rechaza `estado` |
| `TransicionarLoteEstadoAction` | Motivo obligatorio, matriz `LoteEstado::transicionesPermitidas()` |
| BD | Columna `lotes.estado_historial` (JSON) |
| Permisos | `LotePolicy::transition`; reapertura solo `canReabrirLote()` |
| UI | Editar lote: estado lectura + botón «Cambiar estado» (diálogo aparte) |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Actions/TransicionarLoteEstadoActionTest.php tests/Feature/Admin/AdminEstructuraTest.php tests/Unit/Policies/LotePolicyTest.php
php artisan test --compact
```

Resultado: **678/678** suite (2026-09-26).

## Siguiente

**EST-07** (listados útiles con búsqueda y filtros).
