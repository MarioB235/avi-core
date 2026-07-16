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
- Ítem **activo**: círculo verde sólido (`avicore-primary`), icono blanco, borde blanco fino; sobresale por encima del borde superior de la barra (efecto «notch» visual).
- Barra inferior **integrada**: esquinas superiores redondeadas, sombra suave hacia arriba, `safe-area-inset-bottom`.
- Ítems inactivos: círculo soft verde (`size-9`), label semibold en `avicore-primary` (clase `--active` en Blade; sin atributo `data-current`).
- `wire:navigate.hover` en links del dock; transición de página con `wire:transition="operario-page"` (View Transitions API) + morph suave del ítem activo (300ms).
- Cambio de galpón en **Inicio, Cargar e Historial** (chip desplegable; trait `ManagesGalponSelector`). Sin galpón al intentar cargar: se abre el selector en la misma pantalla (`selectorGalponAbierto`); deep links `/operario/carga/*` sin galpón redirigen a `/operario/cargar?abrir_galpon=1`. Flash `abrirSelectorGalpon` sigue soportado en `bootGalponSelector`.

## Header contextual

- **Inicio / Cargar / Historial:** `avicore-home-nav` — barra **fija** en el layout (`operario-mobile`, fuera del scroll); `z-40` por encima de la hoja de contenido. `__shape` blanco + `__line-main` con gradiente fade en tokens de marca. El saludo y chip galpón scrollean debajo.
- **Cuenta del usuario:** `<x-operario.user-menu>` en el avatar del header (home nav `size="nav"` y barra contextual `size="sm"`). Avatar: en chrome verde (home/sidebar) disco blanco + iniciales `avicore-primary`; en header claro disco primario + iniciales blancas — sin `!important` ni borde grueso. **Dropdown** en portal (`x-teleport="body"`, `syncPanelPosition` con clamp vertical/horizontal y `max-height` viewport; `role="menu"`): Perfil + Cerrar sesión. Alpine: `open` + `view` (`menu` \| `profile`); Escape / click fuera. Touch ≥44px (`--nav-account-avatar`: 2.75rem; 2.5rem en ≤380px). En escritorio la cuenta vive en sidebar (`patrones-desktop-operario.md`).
- **Rutas legacy / futuras:** barra contextual con el mismo estilo elevado (`header` sin `isHomePage`).
- Galpón seleccionado: chip verde sólido (`avicore-primary`); sin galpón: chip ámbar con icono warehouse.
- Subtítulo hero Inicio: «Resumen de hoy del galpón que elegiste.» en `text-avicore-primary/90`.
- Datos vía `OperarioLayoutComposer` — no duplicar lógica en cada Livewire.
- Feedback de confirmación: `<x-ui.snackbar-host context="operario" />` en layout; auto-cierre ~4,5s (pausa al hover; × o Escape); móvil centrado sobre el dock; escritorio (`lg+`) abajo a la derecha. Livewire `dispatch('snackbar-show', message:, variant:)` o `session()->flash('status')` tras redirect.

## Inicio operario

- `<x-operario.home-hero>` — bloque único con fondo degradado suave, header, saludo horario compacto («¡Buenos días!» — sin repetir nombre; ya está en nav) + subtítulo «Resumen de hoy del galpón que elegiste.» y chip galpón desplegable (icono `warehouse`; vacío = ámbar + «Sin seleccionar»).
- Heroes Inicio/Cargar/Historial comparten **misma altura** (sin `min-height` fijo; contenido + chip galpón) y hoja `.avicore-operario-home-sheet` con `margin-top: -2rem` / `padding-top: 1.35rem`.
- **Inicio sin CTA a Cargar** — la navegación a `/operario/cargar` es solo por la pestaña inferior del dock (no se usa `x-operario.primary-action` en la hoja).
- **Resumen galpón** — 2 paneles con banda superior verde (`primary` aves · `secondary` huevos), borde y degradado `avicore-soft`; métrica «En el galpón ahora» con fondo `soft`; «Murieron hoy» con fondo blanco, contador en `avicore-danger` y borde rojo suave (más marcado si `muertes_hoy > 0`); métricas de huevos con fondo blanco (`bg-white`) y borde `secondary/30`; nota en pill `soft`. Iconos KPI: ilustraciones en `avicore-operario-carga-tile__icon` (mismo contenedor que tiles Cargar: `size-11`, `rounded-xl`).
- **Lotes activos** — una línea por lote; cada ítem es card con borde `primary/25`, franja lateral verde y degradado `soft`.
- Sin galpón: empty «Seleccioná un galpón…» + botón elegir; KPIs y lotes ocultos.
- Historial solo por pestaña inferior del dock (sin enlace en hoja).
- KPI Objetivo diario: `avicore-defer` hasta existir meta en `reglas.md`.
- Dock: ítems inactivos con círculo soft verde (`size-9`) y label semibold primario; activo con círculo elevado.
- Estilos del módulo: `resources/css/operario.css` (no mezclar en `app.css`).
- Tests: `OperarioHomeResumenTest`, `OperarioBottomNavTest`.

## Formularios de carga

- Hub **Cargar** (`/operario/cargar`): hero «Registrar» + «¿Qué querés registrar?»; grilla **2×2** (`--quad`) si el rol puede crear lote (Huevos · Muertes · Vacunación · Nuevo lote); operario ve grilla **3 tiles** (`--triple`: Huevos · Muertes arriba; Vacunación ancho abajo). Tiles con ilustraciones `operario-huevo` / `operario-ave` / `operario-vacuna`; tile «Nuevo lote» reutiliza `operario-ave` (`avicore-defer: ilustración `operario-lote` cuando exista asset`). Tile activo (`--action`): borde sólido primario, badge en lenguaje llano («Cuántos juntaste hoy», «Cuántas aves murieron», «Registrá por lote», «Ingresá aves al galpón»). Diálogos `x-ui.dialog` con preguntas directas y botón «Guardar» (`!transition-none` en vacunación para evitar parpadeo). Vacunación: `x-ui.select` con `wire:model.defer` (lote + vacuna). Nuevo lote: select galpón, checkboxes Blanca/Colorada, cantidad por tipo, fecha nacimiento (solo dueño/administrativo/encargado). Snackbar: «Huevos guardados.» / «Muertes guardadas.» / «Vacunación guardada.» / «Lote(s) registrado(s)» con código(s).
- Inputs numéricos grandes; botón guardar ancho completo en móvil; texto «Guardando…» con `wire:loading` mientras persiste.
- Validación inline; sin modales innecesarios.
- Tras guardar: feedback claro (toast o redirect a historial).

## Historial operario

- `<x-operario.historial-hero>` — hero con degradado suave; chip galpón **interactivo** (mismo selector que Inicio); copy «Todo lo que cargaste, del más nuevo al más viejo.».
- `.avicore-operario-home-sheet` — **todos** los registros activos del operario (`registros_operativos` + `vacunaciones`), orden **cronológico descendente**. Ítems con resumen + (desde `md:`) meta `tipo · galpón`; mortalidad/vacunación con clases de color. Filtro fecha `x-ui.date-picker`; paginación 20.
- Tests: `OperarioHistorialTest`; `DatePickerComponentTest`; `OperarioCargaVacunacionTest`; `OperarioCargaLoteTest`; `OperarioGalponServiceTest` (`historialPaginado`; defer: paginación en memoria hasta ~500 ítems); `OperarioBottomNavTest` (shell + sidebar + deep links); `SnackbarHostTest` (auto-cierre 4500 + contrato desktop `right-6`/`bottom-6`); `OperarioUserMenuTest` (portal + clamp); `IllustrationComponentTest`; `OperarioNavTest`; `SelectComponentTest`.

## Motion (operario)

- `active:scale-95` en ítems del tab bar (con `prefers-reduced-motion`).
- Sin animaciones de entrada en listas de historial.

## Touch targets

- Mínimo **44×44px** en todos los controles clicables.
- Espacio entre ítems táctiles ≥ 8px.

## Referencias

- Design system: `refined-agro-principios.md`, `motion-y-feedback.md`, `elevacion-y-superficies.md`
- Flujos: `02-pantallas-y-flujos.md` (skill avicore-negocio)
