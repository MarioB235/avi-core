# Patrones mobile — Operario

Shell: `components/layouts/operario-mobile.blade.php` · Header: `<x-operario.header>` · Menú cuenta: `<x-operario.user-menu>` · Nav: `<x-operario.bottom-nav>`.

## Principios

1. **Thumb zone** — acciones primarias abajo (dock) o en zona central-inferior del contenido.
2. **Sin hover** — feedback con `active:`, cambio de fondo en estado activo, y `focus-visible` para teclado/accesibilidad.
3. **Una acción primaria** por pantalla cuando sea posible.
4. **Densidad vertical** — listas con `min-h-[3.25rem]` o más; padding generoso en cards.
5. **Safe area** — header fijo arriba (`fixed top-0`) en heroes y rutas legacy; tokens `--operario-nav-*` en `.avicore-operario-shell--home`; dock con `safe-area-inset-bottom` (`resources/css/operario.css`).

## Navegación

- **Fuente única de rutas y títulos:** `App\Support\OperarioNav` — pestañas (`tabs()`), título de header (`headerTitle()`), activo (`tabIsActive()`). Consumido por `<x-operario.bottom-nav>` y `OperarioLayoutComposer`; no duplicar arrays de rutas en Blade.
- 3 pestañas en fila: Inicio (`home`) · Cargar (`plus`) · Historial (`calendar`) — **mismo diseño** en todos los ítems.
- Ítem **activo**: círculo verde sólido (`avicore-primary`), icono blanco, borde blanco fino; sobresale por encima del borde superior de la barra (efecto «notch» visual).
- Barra inferior **integrada**: esquinas superiores redondeadas, sombra suave hacia arriba, `safe-area-inset-bottom`.
- Ítems inactivos: círculo soft verde (`size-9`), label semibold en `avicore-primary` (clase `--active` en Blade; sin atributo `data-current`).
- `wire:navigate.hover` en links del dock; transición de página con `wire:transition="operario-page"` (View Transitions API) + morph suave del ítem activo (300ms).
- Cambio de galpón **solo** en Inicio (chip desplegable en hero).
- Sin galpón al intentar cargar: redirect a Inicio con selector abierto — flash `abrirSelectorGalpon` (`CargarHub`, `CargaHuevos`); enlace del chip vacío en hero Cargar/Historial puede usar `?abrir_galpon=1` (ambos los consume `Home`).

## Header contextual

- **Inicio / Cargar / Historial:** `avicore-home-nav` — barra **fija** en el layout (`operario-mobile`, fuera del scroll); `z-40` por encima de la hoja de contenido. `__shape` blanco + `__line-main` con gradiente fade en tokens de marca. El saludo y chip galpón scrollean debajo.
- **Cuenta del usuario:** `<x-operario.user-menu>` en el avatar del header (home nav y barra contextual). **Dropdown** anclado al trigger (`role="menu"`), no modal: Perfil (subvista con nombre, documento, correo, empresa, rol) y Cerrar sesión (`POST` + CSRF). Alpine: `open` + `view` (`menu` \| `profile`); cierre con Escape, click fuera y al volver de perfil. Rol visible con `UserRole::label()` (nav home y menú). Touch ≥44px en trigger e ítems; estilos en `operario.css` (`.avicore-operario-user-menu__*`).
- **Rutas legacy / futuras:** barra contextual con el mismo estilo elevado (`header` sin `isHomePage`).
- Galpón seleccionado: chip verde sólido (`avicore-primary`); sin galpón: chip ámbar con icono warehouse.
- Subtítulo hero «Estado de hoy del galpón.» en `text-avicore-primary/90`.
- Datos vía `OperarioLayoutComposer` — no duplicar lógica en cada Livewire.
- Feedback de confirmación: `<x-ui.snackbar-host context="operario" />` en layout; Livewire `dispatch('snackbar-show', message:, variant:)` o `session()->flash('status')` tras redirect.

## Inicio operario

- `<x-operario.home-hero>` — bloque único con fondo degradado suave, header, saludo horario compacto («¡Buenos días!» — sin repetir nombre; ya está en nav) + subtítulo «Estado de hoy del galpón.» y chip galpón desplegable (icono `warehouse`; vacío = ámbar + «Sin seleccionar»).
- `<x-operario.primary-action>` — CTA verde «Registrar producción» al inicio de la hoja; enlaza a `/operario/cargar`.
- Heroes Inicio/Cargar/Historial comparten **misma altura** (sin `min-height` fijo; contenido + chip galpón) y hoja `.avicore-operario-home-sheet` con `margin-top: -2rem` / `padding-top: 1.35rem`.
- `.avicore-operario-home-cargas` — altura flexible; vacío compacto con mensaje accionable + botón «Cargar ahora»; con datos usa `clamp(14rem, 34dvh, 22rem)` y scroll interno.
- `.avicore-operario-home-summary` — bloque KPI con eyebrow «Hoy»; grilla **2 columnas** (Maples hoy destacado + Cargas hoy); maples = suma huevos del día ÷ 30 (ver `reglas.md`). KPI Objetivo diario: `avicore-defer` en `home.blade.php` hasta existir meta en `reglas.md`.
- Dock: ítems inactivos con círculo soft verde (`size-9`) y label semibold primario; activo con círculo elevado.
- Estilos del módulo: `resources/css/operario.css` (no mezclar en `app.css`).

## Formularios de carga

- Hub **Cargar** (`/operario/cargar`): hero con fondo suave (igual que Inicio); grilla 2×2. **Huevos** → diálogo centrado (solo cantidad). Login recuperación: bottom sheet.
- Inputs numéricos grandes; botón guardar ancho completo en móvil.
- Validación inline; sin modales innecesarios.
- Tras guardar: feedback claro (toast o redirect a historial).

## Historial operario

- `<x-operario.historial-hero>` — hero con degradado suave (igual que Inicio/Cargar); chip galpón solo lectura (vacío → enlace `?abrir_galpon=1`).
- `.avicore-operario-home-sheet` — lista de registros del día (mismo panel que «Últimas cargas» en Inicio) + bloque «Tu cuenta» con cierre de sesión (complementario al menú del header).
- Tests HTTP: `OperarioBottomNavTest` (tab activa, icono `calendar` en Historial, empty state, registros del día); `OperarioNavTest` (pestaña activa y `headerTitle` por ruta, incl. `operario.historial`); `OperarioHomeTest` (rechazo `seleccionarGalpon`: empresa ajena, mantenimiento, inactivo); `OperarioUserMenuTest` (menú cuenta en home/cargar/historial, ARIA, perfil, logout).

## Motion (operario)

- `active:scale-95` en ítems del tab bar (con `prefers-reduced-motion`).
- Sin animaciones de entrada en listas de historial.

## Touch targets

- Mínimo **44×44px** en todos los controles clicables.
- Espacio entre ítems táctiles ≥ 8px.

## Referencias

- Design system: `refined-agro-principios.md`, `motion-y-feedback.md`, `elevacion-y-superficies.md`
- Flujos: `02-pantallas-y-flujos.md` (skill avicore-negocio)
