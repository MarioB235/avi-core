# EST-02 — Galpones completos

| Campo | Valor |
|---|---|
| ID | EST-02 |
| Estado | VERIFICADA |
| Fecha | 2026-09-26 |
| Rama | `feature/emp-01-alta-empresa` |

## Objetivo

Alta/edición de galpones con granja de la misma empresa, código único por granja, estados operativos y bloqueo de carga sin perder historial.

## Implementación

| Pieza | Detalle |
|---|---|
| Validación | `GalponValidacion` (reglas, mensajes, normalización, `assertGranjaActiva`) |
| Actions | `CreateGalponAction` / `UpdateGalponAction` reutilizan validación |
| BD | índice único `(granja_id, codigo)` |
| UI | Estructura → Galpones; errores mapeados a campos `galpon*`; badge de estado |
| Operario | Estados no activos excluyen carga; historial conserva registros previos |

## Prueba de cierre

```bash
php artisan migrate --force
php artisan test --compact tests/Unit/Support/GalponValidacionTest.php tests/Feature/Admin/AdminEstructuraTest.php tests/Feature/Operario/OperarioHistorialTest.php
php artisan test --compact
```

Resultado: **36/36** focalizados + **639/639** suite (2026-09-26).

## Siguiente

**EST-03** (jerarquía de estados granja → galpones).
