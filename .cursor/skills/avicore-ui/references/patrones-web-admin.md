# Patrones web — Admin

Shell: `components/layouts/admin.blade.php` · Sidebar verde · Drawer `< lg`.

## Layout

- **Sidebar sticky** (`bg-avicore-primary`) — logo, nav por secciones, usuario abajo.
- **Header** — título de página, acciones contextuales, trigger menú en `< lg`.
- **Main** — gutter común (`avicore-admin-gutter`); contenido en `max-w-7xl` cuando aplique.

## Navegación

- `<x-ui.nav-link>` con `icon`, `active`, `disabled`.
- Hover en links: `md:hover:bg-white/10` (sidebar) — solo desde `md:`.
- Drawer móvil: patrón Alpine inspirado en WireBlade (`x-transition`, `-translate-x-full`).

## Tablas y datos

- Zebra sutil: `md:hover:bg-avicore-soft/60` en filas.
- Encabezados: uppercase pequeño, `text-avicore-muted`.
- Empty state con `<x-ui.empty-state>` — no tabla vacía sin mensaje.

## KPI grid

- `<x-ui.kpi-card>` en grid responsive (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`).
- Hover sutil en card: `md:hover:shadow-md` (200ms).

## Densidad

- Más información por fila que en operario; mantener legibilidad (14–16px cuerpo).
- Formularios admin: labels visibles; no depender solo de placeholder.

## Motion (admin)

- Transiciones en color/sombra desde `md:`.
- Modales: `x-ui.dialog` — fade backdrop, sin scale exagerado en panel.
- Sin stagger en listas de registros.

## Desktop vs tablet

- `< lg`: drawer sidebar.
- `≥ lg`: sidebar visible; contenido fluido.

## Referencias

- `ejemplos-snippet.md` (design-system)
- Hero Inicio: `<x-admin.home-hero>` — excepción de imagen + KPI overlay documentada en `tokens-componentes.md`
