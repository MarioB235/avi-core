# Patrones web — Admin (Dueño / Administrativo / Encargado)

Shell: `components/layouts/admin.blade.php` — **mismo chrome visual que operario** (clases `avicore-operario-*`), con **contenido y flujos solo de gestión**.

La carga en campo **no** forma parte de este panel: vive en `/operario` (módulo aparte). El Dueño puede tener permiso de cargar, pero el panel admin no lo promueve ni lo mezcla en la nav.

Referencia visual: `patrones-desktop-operario.md` + `patrones-mobile-operario.md`.  
Nav: `App\Support\AdminNav` · Composer: `AdminLayoutComposer`.

## Layout (paridad visual)

- **Sidebar** (`lg+`) — verde primario, logo + subtítulo empresa/`Administración AviCore`, secciones «Navegación» / «Cuenta», `<x-ui.user-menu variant="sidebar">`.
- **Bottom nav** (`< lg`) — pestañas `AdminNav` (Inicio · Usuarios).
- **Header** — en páginas hero (`admin.home`, `admin.usuarios.*`): `avicore-home-nav` (logo + cuenta); en el resto: título + badge de rol + menú cuenta.
- **Main** — sheet blanco (`avicore-operario-home-sheet`) bajo heroes; snackbar con dock sobre bottom nav (`context="operario"` = posición UI, no módulo Campo).

## Navegación (MVP)

| Tab | Ruta | Quién |
|-----|------|-------|
| Inicio | `/admin` | Todos los roles de panel |
| Usuarios | `/admin/usuarios` | Quienes `canViewUsers` |

**Fuera del panel:** `/operario` (Cargar / Historial) — no aparece como tab ni tile en admin.

Ítems futuros (Estructura, Reportes) van como tiles «Próximamente» en Inicio.

## Inicio (intereses Dueño)

Contenido **propio de gestión** (no clonar paneles/tiles de carga del operario):

- Hero compartido (saludo + subtítulo) + chip de empresa (`avicore-admin-context`, no chip de galpón).
- KPIs con `<x-ui.kpi-card>`: Usuarios activos · Granjas y galpones (placeholder).
- Accesos en lista `avicore-admin-home-action`: Usuarios · Estructura · Reportes (próximamente).
- Checklist «Estado inicial» en columna paralela en escritorio (`avicore-admin-home-panels`).

**No usar** en Inicio admin: `avicore-operario-kpi-panel`, `avicore-operario-carga-tile`, `avicore-operario-galpon-chip`.


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
- Pantallas: `pantallas-flujos.md` §3.1–3.2
