# CAP-01 — Selector robusto (operario)

**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/emp-01-alta-empresa`

## Objetivo

Recordar galpón disponible, invalidar ajeno/inactivo y mostrar contexto siempre; no guardar en galpón anterior tras cambiar selección.

## Implementación

| Pieza | Detalle |
|-------|---------|
| `OperarioGalponService` | `galponActual` revalida disponibilidad; `galponDisponibleParaUsuario` scope empresa |
| `ManagesGalponSelector` | `syncGalponSelector` + `hydrateGalponSelector` en Home/Cargar/Historial |
| `CargarHub::resolveGalponParaGuardar` | Relee `ultimo_galpon_id` al guardar (no memo stale) |
| UI | Chip en Inicio/Cargar/Historial; granja visible bajo el chip; alerta sin galpón en Cargar |
| Deep links | `/operario/carga/*` sin galpón → `/operario/cargar?abrir_galpon=1` |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Operario/OperarioGalponSelectorTest.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

**Resultado:** 708/708 tests OK.

## Siguiente

**CAP-02** — huevos punta a punta.
