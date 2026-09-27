# EST-01 — Granjas completas

| Campo | Valor |
|---|---|
| ID | EST-01 |
| Estado | VERIFICADA |
| Fecha | 2026-09-26 |
| Rama | `feature/emp-01-alta-empresa` |

## Objetivo

Alta/edición de granjas con DICOSE texto, unicidad por empresa, estado activa y mensajes de error útiles en el formulario.

## Implementación

| Pieza | Detalle |
|---|---|
| Validación | `GranjaValidacion` (reglas, mensajes, normalización) |
| Actions | `CreateGranjaAction` / `UpdateGranjaAction` reutilizan validación |
| BD | índice único `(empresa_id, codigo)` |
| UI | Estructura → Granjas; errores mapeados a campos `granja*` |
| Permisos | Solo Administrativo gestiona; Dueño/Encargado sin alta granja |

## Prueba de cierre

```bash
php artisan test --compact tests/Unit/Support/GranjaValidacionTest.php tests/Feature/Admin/AdminEstructuraTest.php
php artisan test --compact
```

Resultado: **15/15** estructura + **632/632** suite (2026-09-26).

## Siguiente

**EST-02** (galpones completos).
