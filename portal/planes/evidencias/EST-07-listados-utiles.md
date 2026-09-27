# EST-07 — Listados útiles con búsqueda y filtros

| Campo | Valor |
|---|---|
| ID | EST-07 |
| Estado | VERIFICADA |
| Fecha | 2026-09-26 |
| Rama | `feature/emp-01-alta-empresa` |

## Objetivo

Listados de Estructura admin con búsqueda, filtros por sección, paginación y scope por empresa; estados vacíos que explican indisponibilidad.

## Implementación

| Pieza | Detalle |
|---|---|
| `Admin/Estructura/Index` | `#[Url]` en búsqueda y filtros: granja, galpón, activa, estado operativo, estado/tipo lote |
| Queries | `granjasQuery`, `galponesQuery`, `lotesQuery` con `when()` por filtro; lotes por granja vía `whereHas` |
| Multiempresa | Filtro de granja ajena no filtra lotes de otra empresa (scope `empresa_id`) |
| Empty states | `emptyListadoMensaje()` + botón «Limpiar filtros» cuando hay filtros activos |
| Indisponibilidad | Badges/hints en galpones (granja inactiva, no disponible para carga) y lotes (galpón/granja) |
| Tests | 4 casos nuevos en `AdminEstructuraTest` (filtros + aislamiento empresa) |

## Parámetros URL (ejemplos)

- `?seccion=granjas&busqueda=…&granjaActiva=1`
- `?seccion=galpones&granja=…&galponEstado=mantenimiento`
- `?seccion=lotes&granja=…&galpon=…&loteEstado=activo&loteTipo=colorada`

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Admin/AdminEstructuraTest.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

Resultado: **682/682** suite · **24/24** `AdminEstructuraTest` (2026-09-26).

## Siguiente

**EST-08** (vacío y mantenimiento: sin lote, carga excepcional, vacío sanitario).
