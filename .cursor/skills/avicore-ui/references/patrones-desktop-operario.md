# Patrones desktop — Operario

Shell: `components/layouts/operario-mobile.blade.php` (responsive) · Sidebar: `<x-operario.sidebar-nav>` · Nav móvil: `<x-operario.bottom-nav>`.

## Breakpoints

| Rango | Comportamiento |
|-------|----------------|
| **< 768px** | Shell `max-w-lg`, bottom nav, heroes con `avicore-home-nav` |
| **768–1023px (tablet)** | Shell `max-w-3xl`, bottom nav, KPIs/carga en más columnas; sin sidebar |
| **≥ 1024px (`lg`)** | Sidebar verde sticky + contenido ancho (`max-w-6xl`); bottom nav oculta |

## Layout escritorio

1. **Grid** — `.avicore-operario-shell` pasa a `grid-cols-[auto_minmax(0,1fr)]`; workspace a la derecha.
2. **Sidebar** — logo «Carga en campo», navegación vía `OperarioNav` + `x-ui.nav-link`, cuenta con `<x-ui.user-menu variant="sidebar">` (alias `x-operario.user-menu`; avatar blanco/iniciales verdes; panel teleport + clamp). Oculta en `< lg`.
3. **Bottom nav** — oculta con `lg:hidden`.
4. **Fondo** — `avicore-surface` en body; sin degradado superior de marca del shell móvil.
5. **Snackbar** — auto-cierre ~4,5s; en `lg+` abajo a la derecha (`right-6 bottom-6`); contrato en `SnackbarHostTest` (CSS).

## Header y heroes

- En hero pages: `avicore-home-nav` oculto en `lg+` (cuenta en sidebar).
- Chip galpón **interactivo** en Inicio, Cargar e Historial (`ManagesGalponSelector` + `galpon-chip-selector`); sin saltar a Inicio para elegir.

## Densidad y grillas

| Bloque | Móvil | Tablet (`md`–`lg`) | Escritorio (`lg+`) |
|--------|-------|--------------------|---------------------|
| KPI Inicio (`--duo`) | 1 col | 2 cols | 2 cols |
| Hoja Inicio (con galpón) | apilado | apilado | 2 cols en `xl` |
| Carga `--triple` | 2+1 | 3 cols | 3 cols |
| Carga `--quad` | 2×2 | 2×2 | 4 cols |
| Historial | label + hora | + meta tipo · galpón | + meta + hover |

## Motion y hover

- Móvil: sin `hover` como feedback principal.
- Escritorio: `hover` en sidebar, historial, tiles, select/chip; overlays centrados (`x-ui.sheet`, date-picker).
- Formularios de carga: botón «Guardar» muestra «Guardando…» con `wire:loading`.

## Referencias

- Móvil: `patrones-mobile-operario.md`
- Flujos: `pantallas-flujos.md` §5
- Admin (sidebar de referencia): `patrones-web-admin.md`
