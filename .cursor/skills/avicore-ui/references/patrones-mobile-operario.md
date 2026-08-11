# Patrones mobile — Operario

Shell: `components/layouts/operario-mobile.blade.php` · Header: `<x-operario.header>` · Menú cuenta: `<x-operario.user-menu>` · Nav: `<x-operario.bottom-nav>`.

## Principios

1. **Thumb zone** — acciones primarias abajo (dock) o en zona central-inferior del contenido.
2. **Sin hover** — feedback con `active:`, cambio de fondo en estado activo, y `focus-visible` para teclado/accesibilidad.
3. **Una acción primaria** por pantalla cuando sea posible.
4. **Densidad vertical** — listas con `min-h-[3.25rem]` o más; padding generoso en cards.
5. **Safe area** — header fijo arriba (`fixed top-0`) en heroes y rutas legacy; tokens `--operario-nav-*` en `.avicore-operario-shell--home`; dock con `safe-area-inset-bottom` (`resources/css/operario.css`).
6. **Lenguaje llano** — copy para operario en campo sin jerga técnica: preguntas directas («¿Cuántos huevos?»), evitar «producción acumulada», «inicio», «tipo de carga»; explicar maples como «cada 30 huevos»; fechas en formato `d/m/Y`.

## Navegación

- **Fuente única de rutas y títulos:** `App\Support\OperarioNav` — pestañas (`tabs()`), título de header (`headerTitle()`), activo (`tabIsActive()`). Consumido por `<x-operario.bottom-nav>` y `OperarioLayoutComposer`; no duplicar arrays de rutas en Blade.
- 3 pestañas en fila: Inicio (`home`) · Cargar (`plus`) · Historial (`calendar`) — **mismo diseño** en todos los ítems.
- Ítem **activo**: círculo **blanco** elevado, icono `avicore-primary`, borde verde fino; sobresale por encima del borde superior de la barra (efecto «notch» visual).
- Barra inferior **verde marca** (`avicore-primary`): esquinas superiores redondeadas, sombra suave verde hacia arriba, línea superior clara (gradiente blanco/`secondary`), `safe-area-inset-bottom`.
- Ítems inactivos: círculo `white/15`, icono blanco, label `white/80` semibold.
- `wire:navigate` en links del dock (prefetch en mousedown por defecto en Livewire 4; sin `.hover` para no disparar requests extra al pasar el dedo/ratón). Transición de página con `wire:transition="operario-page"` (View Transitions API, ~150–160 ms) + morph del ítem activo del dock (~150 ms, alineado a la página).
- Cambio de galpón en **Inicio, Cargar e Historial** (chip desplegable; trait `ManagesGalponSelector`). Panel `absolute` bajo el chip; con scroll interno de `home-sheet`, los contenedores padre pasan a `overflow: visible` mientras `avicore-operario-galpon-selector--open` (`:has` en shell). Estado UI con `@entangle` **sin** `.live` (evita morph de Livewire al abrir). Backdrop `fixed` cubre pantalla. Sin galpón al intentar cargar: se abre el selector en la misma pantalla (`selectorGalponAbierto`); deep links `/operario/carga/*` sin galpón redirigen a `/operario/cargar?abrir_galpon=1`. Flash `abrirSelectorGalpon` sigue soportado en `bootGalponSelector`.
- **Servicios scoped (request):** `AppServiceProvider` registra `OperarioGalponService` y `OperarioGalponResumenService` con `scoped()` — una instancia por request HTTP/Livewire.
- **`OperarioGalponService`:** `galponActual` memoiza pero **revalida** disponibilidad en cada llamada; `seleccionarGalpon` limpia el memo. Al **guardar** carga, `CargarHub::resolveGalponParaGuardar` usa `galponDisponibleParaUsuario` (no memo stale si el galpón pasó a mantenimiento).
- **`OperarioGalponResumenService`:** `resumen()` recalcula totales en cada llamada (KPIs frescos tras guardar); memo intra-request solo en `lotesActivos()` (Home + Cargar en el mismo request).

## Header contextual

- **Inicio / Cargar / Historial:** `avicore-home-nav` — barra **fija** en el layout (`operario-mobile`, fuera del scroll); `z-40` por encima de la hoja de contenido. `__shape` con tinte `--avicore-operario-hero-chrome` (misma familia que el degradado del body/hero, no blanco puro); `__line-main` con gradiente fade en tokens de marca (línea decorativa ogee). Shell y `main--home` **transparentes** para que el degradado del body unifique nav + hero. **Móvil:** scroll solo en `avicore-operario-home-sheet` (no sube bajo el nav; sin overlays ni `backdrop-filter` al scroll). Offset del hero: `--operario-nav-offset`.
- **Cuenta del usuario:** `<x-ui.user-menu>` (alias `<x-operario.user-menu>`) en el avatar del header (home nav `size="nav"` y barra contextual `size="sm"`). Avatar: en chrome verde (home/sidebar) disco blanco + iniciales `avicore-primary`; en header claro disco primario + iniciales blancas — sin `!important` ni borde grueso. **Dropdown** en portal (`x-teleport="body"`, `syncPanelPosition` con clamp vertical/horizontal y `max-height` viewport; `role="menu"`): resumen Perfil + enlaces **Editar datos** / **Cambiar contraseña** (`/operario/perfil` o `/perfil`) + Cerrar sesión. Alpine: `open` + `view` (`menu` \| `profile`); Escape / click fuera. Touch ≥44px (`--nav-account-avatar`: 2.75rem; 2.5rem en ≤380px). En escritorio la cuenta vive en sidebar (`patrones-desktop-operario.md`). El mismo componente se usa en el panel admin (`patrones-web-admin.md`).
- **Rutas legacy / futuras:** barra contextual con el mismo estilo elevado (`header` sin `isHomePage`).
- Galpón seleccionado: chip verde sólido (`avicore-primary`); sin galpón: chip ámbar con icono warehouse.
- Subtítulo hero Inicio: «Resumen de hoy del galpón que elegiste.» en `text-avicore-primary/90`.
- Datos vía `OperarioLayoutComposer` — no duplicar lógica en cada Livewire.
- Feedback de confirmación: `<x-ui.snackbar-host context="operario" />` en layout; auto-cierre ~3,5s con barra de progreso superior (vacía hacia la derecha) (pausa al hover/foco; sin barra si hay acción); móvil centrado sobre el dock; escritorio (`lg+`) abajo a la derecha. Livewire `dispatch('snackbar-show', message:, variant:)` o `session()->flash('status')` tras redirect.

## Perfil operario

- Ruta `/operario/perfil` (shell hero) y `/perfil` (admin). Livewire `Profile/Edit` compartido; `x-operario.perfil-hero` con copy según `seccion` (`datos` \| `password`); `wire:key` en el hero para remorph al cambiar sección.
- **Pestañas Mis datos / Contraseña:** `wire:click="seleccionarSeccion(…)"` + `#[Url(as: 'seccion')]` — **no** `wire:navigate` entre secciones (morph Livewire rompe el shell flex/overflow). Deep link: `?seccion=password`.
- Contenedor: `.avicore-operario-perfil` + `.avicore-operario-home-sheet` (mismo patrón scroll que Inicio/Cargar/Historial).
- `OperarioLayoutComposer::perfilHeaderTitle()` — título contextual en rutas hero según query `seccion`.
- Menú cuenta: enlaces a perfil con `wire:navigate` (cambio de ruta); pestañas internas sin navigate.
- Tests: `OperarioPerfilTest` (shell, pestañas, hero, policy usuario inactivo).

## Inicio operario

- `<x-operario.home-hero>` — bloque único con fondo degradado suave, header, saludo horario compacto («¡Buenos días!» — sin repetir nombre; ya está en nav) + subtítulo «Resumen de hoy del galpón que elegiste.» y chip galpón desplegable (icono `warehouse`; vacío = ámbar + «Sin seleccionar»).
- Heroes Inicio/Cargar/Historial comparten **misma altura** (sin `min-height` fijo; contenido + chip galpón) y hoja `.avicore-operario-home-sheet` con `margin-top: -2rem` / `padding-top: 1.35rem`. Viñeta edge fade solo bajo nav superior (`operario.css`, `shell--home` móvil); sin degradados ni pseudo-elementos sobre el dock — la hoja blanca llega hasta el navbar inferior.
- **Inicio sin CTA a Cargar** — la navegación a `/operario/cargar` es solo por la pestaña inferior del dock (no se usa `x-operario.primary-action` en la hoja).
- **Resumen galpón** — 2 paneles con banda superior verde (`primary` aves · `secondary` huevos), borde y degradado `avicore-soft`; métrica «En el galpón ahora» con fondo `soft`; «Murieron hoy» con fondo blanco, contador en `avicore-danger` y borde rojo suave (más marcado si `muertes_hoy > 0`); métricas de huevos con fondo blanco (`bg-white`) y borde `secondary/30`; nota en pill `soft`. Iconos KPI: ilustraciones en `avicore-operario-carga-tile__icon` (mismo contenedor que tiles Cargar: `size-11`, `rounded-xl`).
- **Lotes activos** — una línea por lote; cada ítem es card con borde `primary/25`, franja lateral verde y degradado `soft`.
- Sin galpón: empty «Seleccioná un galpón…» + botón elegir; KPIs y lotes ocultos.
- Historial solo por pestaña inferior del dock (sin enlace en hoja).
- KPI Objetivo diario: `avicore-defer` hasta existir meta en `reglas.md`.
- Dock: barra `primary` edge-to-edge (`__surface` + `safe-area-inset-bottom`); inactivos cápsula `rounded-2xl` `white/12` + label `white/85`; activo círculo blanco elevado con halo suave + icono verde.
- Estilos del módulo: `resources/css/operario.css` (no mezclar en `app.css`).
- Secciones Inicio/Cargar: entrada al scroll con `x-ui.reveal` (bloques, no filas); edge fade solo bajo nav superior; hoja hasta el dock.
- Tests: `OperarioHomeResumenTest`, `OperarioBottomNavTest`, `ScrollRevealTest`, `RevealComponentTest`, `OperarioGalponServiceTest` (scoped ambos services).

## Formularios de carga

- Hub **Cargar** (`/operario/cargar`): grilla **2 columnas** — Huevos · Muertes · Descarte · Vacunación · Alimento (+ tile ancho «Nuevo lote» si `canCreateLote()`). Diálogos `x-ui.dialog` solo montados si están abiertos. Snackbar: «Huevos guardados.» / «Muertes guardadas.» / «Descarte de aves guardado.» / «Vacunación guardada.» / «Lote(s) registrado(s)» con código(s).
- Inputs numéricos grandes; botón guardar ancho completo en móvil; texto «Guardando…» con `wire:loading` mientras persiste.
- Validación inline; sin modales innecesarios.
- Tras guardar: feedback claro (toast o redirect a historial).

## Historial operario

- `<x-operario.historial-hero>` — hero con degradado suave; chip galpón **interactivo** (mismo selector que Inicio); copy «Todo lo que cargaste, del más nuevo al más viejo.».
- `.avicore-operario-home-sheet` — **todos** los registros activos del operario (`registros_operativos` + `vacunaciones`), orden **cronológico descendente**. Paginación SQL (`UNION ALL` + `forPage` en `OperarioGalponService::historialPaginado`); hidrata solo la página actual. Ítems con resumen + (desde `md:`) meta `tipo · galpón`; mortalidad/vacunación con clases de color. Filtro fecha `x-ui.date-picker`; paginación 20.
- Tests: `OperarioHistorialTest`; `DatePickerComponentTest`; `OperarioCargaVacunacionTest`; `OperarioCargaLoteTest`; `OperarioGalponServiceTest` (`historialPaginado` union SQL); `OperarioBottomNavTest` (shell + sidebar + deep links); `SnackbarHostTest` (auto-cierre 3500, `syncProgressDuration`, pause/Escape, contrato desktop `right-6`/`bottom-6`); `OperarioUserMenuTest` (portal + clamp); `IllustrationComponentTest`; `OperarioNavTest`; `SelectComponentTest`; `ScrollRevealTest`; `RevealComponentTest`.

## Motion (operario)

- `active:scale-95` en ítems del tab bar (con `prefers-reduced-motion`).
- Scroll reveal opt-in en bloques de sección (`x-ui.reveal` en Inicio/Cargar); historial y filas de lista **sin** reveal.

## Touch targets

- Mínimo **44×44px** en todos los controles clicables.
- Espacio entre ítems táctiles ≥ 8px.

## Referencias

- Design system: `refined-agro-principios.md`, `motion-y-feedback.md`, `elevacion-y-superficies.md`
- Flujos: `02-pantallas-y-flujos.md` (skill avicore-negocio)
