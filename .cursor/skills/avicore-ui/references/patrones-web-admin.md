# Patrones web — Admin (persona de referencia: **Dueño**)

> **MVP (2026-08-22):** Dueño con 4 tabs (Inicio, Resumen, Equipo, Comercial preview). **Estructura** → Administrativo/Encargado. **Usuarios** CRUD → Administrativo. Ver `permisos.md` §10–§11.

Shell: `components/layouts/admin.blade.php` — **mismo chrome visual que operario** (clases `avicore-operario-*`), con **contenido y flujos solo de gestión**.

La carga en campo **no** forma parte de este panel: vive en `/operario` (módulo aparte). El Dueño puede tener permiso de cargar, pero el panel admin no lo promueve ni lo mezcla en la nav.

Referencia visual: `patrones-desktop-operario.md` + `patrones-mobile-operario.md`.  
Nav: `App\Support\AdminNav` · Composer: `AdminLayoutComposer`.

## Layout (paridad visual)

- **Sidebar** (`lg+`) — verde primario, logo + subtítulo empresa/`Administración AviCore`, secciones «Navegación» / «Cuenta», `<x-ui.user-menu variant="sidebar">`.
- **Bottom nav** (`< lg`) — `<x-ui.tab-bar>` compartido vía `x-admin.bottom-nav` / `x-operario.bottom-nav`; columnas dinámicas (`--avicore-tab-cols`); mismos íconos y elevación que operario.
- **Header** — páginas hero (`{rol}.home`, `{rol}.resumen.*`, `{rol}.estructura.*`, `{rol}.usuarios.*`, `profile.edit`): `avicore-home-nav` (logo + cuenta), mismo patrón que operario.
- **Main** — sheet blanco (`avicore-operario-home-sheet`) bajo heroes; snackbar con dock sobre bottom nav (`context="operario"` = posición UI, no módulo Campo).

## Navegación (MVP)

| Tab | Ruta (ejemplo) | Quién |
|-----|----------------|-------|
| Inicio | `/dueno`, `/administrativo`, … | Todos los roles de panel |
| Resumen | `/{rol}/resumen` | `canViewResumen` (Dueño, Administrativo, Encargado) |
| Equipo | `/{rol}/equipo` | `canViewEquipo` (Dueño — solo lectura) |
| Comercial | `/{rol}/comercial` | `canViewComercial` (Dueño — preview post-MVP) |
| Estructura | `/{rol}/estructura` | `canViewEstructura` (Administrativo, Encargado) |
| Usuarios | `/{rol}/usuarios` | `canViewUsers` (Admin AviCore, Administrativo, Encargado) |

**Dueño:** Inicio solo con KPIs del día; Equipo y Comercial en tabs del nav.

**Fuera del panel:** `/operario` (Cargar / Historial) — no aparece como tab ni tile en admin.

Ítems futuros (Reportes) se agregarán como tab o tile cuando exista el módulo.

## Inicio (intereses Dueño)

Contenido **propio de gestión** (no clonar paneles/tiles de carga del operario):

- Hero compartido (saludo + subtítulo `Resumen de {empresa · rol}.`; sin chip duplicado de empresa ni galpón).
- KPIs operativos: `x-ui.stat-panel` en grilla `avicore-operario-kpi-grid--stat` (2×2 móvil, 4 columnas `lg+`; títulos y valores con tipografía fluida).
- **Sin checklist onboarding** en Dueño (módulos en bottom nav).

**Reutilizar del operario:** grids/paneles KPI, section-head, filter-chip, `x-ui.reveal`. **No usar** chip de galpón ni tiles de carga en campo ni duplicar tabs del dock.


## Densidad y tablas (CRUD)

- Listados (p. ej. Usuarios) viven **dentro** del sheet; más densidad que en carga de campo, misma tipografía/tokens.
- Empty state con `<x-ui.empty-state>`; filas con `md:hover` sutil.
- Formularios: labels visibles; diálogos `x-ui.dialog` / sheets según patrón existente.

## Motion

- `wire:transition` en chrome/página.
- Hover de nav/filas solo desde `md:`.
- Sin stagger en tablas.

## Desktop vs móvil

- `< lg`: bottom nav + home-nav en heroes.
- `≥ lg`: sidebar; bottom nav oculta; home-nav del header se oculta (cuenta en sidebar).

## Referencias

- Componentes: `x-admin.sidebar-nav`, `bottom-nav`, `header`, `home-hero`, `page-hero`
- Tokens: `tokens-componentes.md` · menú: `x-ui.user-menu`
- Pantallas: `pantallas-flujos.md` §3.1–3.4
