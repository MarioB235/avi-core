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
- **Cuenta del usuario:** `<x-operario.user-menu>` en el avatar del header (home nav y barra contextual). Avatar: círculo `bg-avicore-primary`, iniciales blancas, `border-2 border-white` y sombra suave (misma familia visual que pestaña activa del dock). **Dropdown** anclado al trigger (`role="menu"`), no modal: Perfil (subvista con nombre, documento, correo, empresa, rol) y Cerrar sesión (`POST` + CSRF). Alpine: `open` + `view` (`menu` \| `profile`); cierre con Escape, click fuera y al volver de perfil. Rol visible con `UserRole::label()` (nav home y menú). Touch ≥44px en trigger (`--nav-account-avatar`: 2.75rem; 2.5rem en ≤380px); estilos en `operario.css` (`.avicore-operario-user-menu__*`).
- **Rutas legacy / futuras:** barra contextual con el mismo estilo elevado (`header` sin `isHomePage`).
- Galpón seleccionado: chip verde sólido (`avicore-primary`); sin galpón: chip ámbar con icono warehouse.
- Subtítulo hero «Estado de hoy del galpón.» en `text-avicore-primary/90`.
- Datos vía `OperarioLayoutComposer` — no duplicar lógica en cada Livewire.
- Feedback de confirmación: `<x-ui.snackbar-host context="operario" />` en layout; Livewire `dispatch('snackbar-show', message:, variant:)` o `session()->flash('status')` tras redirect.

## Inicio operario

- `<x-operario.home-hero>` — bloque único con fondo degradado suave, header, saludo horario compacto («¡Buenos días!» — sin repetir nombre; ya está en nav) + subtítulo «Estado de hoy del galpón.» y chip galpón desplegable (icono `warehouse`; vacío = ámbar + «Sin seleccionar»).
- Heroes Inicio/Cargar/Historial comparten **misma altura** (sin `min-height` fijo; contenido + chip galpón) y hoja `.avicore-operario-home-sheet` con `margin-top: -2rem` / `padding-top: 1.35rem`.
- **Inicio sin CTA a Cargar** — la navegación a `/operario/cargar` es solo por la pestaña inferior del dock (no se usa `x-operario.primary-action` en la hoja).
- **Resumen galpón** (`.avicore-operario-home-summary`) — KPIs del **galpón seleccionado** (no del operario): grilla **2×2** con aves actuales, huevos hoy (+ hint maples ÷30), muertes hoy (`avicore-danger` si >0), producción acumulada desde `min(fecha_ingreso)` de lotes activos/en producción; nota opcional de muertes acumuladas. Sin cabecera duplicada del galpón (nombre solo en chip del hero). Datos vía `OperarioGalponResumenService`.
- **Lotes activos** (`.avicore-operario-home-lotes`) — lista compacta por lote (código, edad en semanas vía `OperarioGalponResumenService::edadSemanas()`, cantidad inicial); aviso informativo si hay más de un lote activo (regla §3 galpones).
- Sin galpón: empty «Seleccioná un galpón…» + botón elegir; KPIs y lotes ocultos.
- Historial solo por pestaña inferior del dock (sin enlace en hoja).
- KPI Objetivo diario: `avicore-defer` hasta existir meta en `reglas.md`.
- Dock: ítems inactivos con círculo soft verde (`size-9`) y label semibold primario; activo con círculo elevado.
- Estilos del módulo: `resources/css/operario.css` (no mezclar en `app.css`).
- Tests: `OperarioHomeResumenTest`, `OperarioBottomNavTest`.

## Formularios de carga

- Hub **Cargar** (`/operario/cargar`): hero con fondo suave (igual que Inicio); hoja sin card contenedora — cabecera de sección + grilla 2×2. Tile activo (`--action`): borde sólido primario, sombra suave, badge informativo y feedback táctil (`active:scale-[0.97]`). Tiles «Próximamente» (`--soon`): borde punteado atenuado, sin interacción. **Huevos** → diálogo centrado. Login recuperación: bottom sheet.
- Inputs numéricos grandes; botón guardar ancho completo en móvil.
- Validación inline; sin modales innecesarios.
- Tras guardar: feedback claro (toast o redirect a historial).

## Historial operario

- `<x-operario.historial-hero>` — hero con degradado suave (igual que Inicio/Cargar); chip galpón solo lectura (vacío → enlace `?abrir_galpon=1`); copy «Huevos, muertes y más».
- `.avicore-operario-home-sheet` — **todos** los registros activos del operario (huevos, muertes, alimento, combinado), orden **cronológico descendente** (más reciente arriba). Lista simple `.avicore-operario-historial-list`: una línea con cantidad (`cantidadResumen()`, sin repetir tipo ni galpón), observación opcional debajo, fecha/hora a la derecha; ítems de mortalidad (tipo `muertes` o combinado con muertes) en `text-avicore-danger` (`.avicore-operario-historial-list__item--muertes`). Filtro opcional por fecha (`?fecha=`; validación Livewire: `date`, `before_or_equal:today`; error visible con `role="alert"`); paginación Livewire (20). Cuenta/logout solo en menú avatar del header.
- Tests: `OperarioHistorialTest` (tipos, filtro, fechas inválidas/futuras, paginación, multiempresa); `OperarioBottomNavTest`; `OperarioNavTest`; `OperarioUserMenuTest`.

## Motion (operario)

- `active:scale-95` en ítems del tab bar (con `prefers-reduced-motion`).
- Sin animaciones de entrada en listas de historial.

## Touch targets

- Mínimo **44×44px** en todos los controles clicables.
- Espacio entre ítems táctiles ≥ 8px.

## Referencias

- Design system: `refined-agro-principios.md`, `motion-y-feedback.md`, `elevacion-y-superficies.md`
- Flujos: `02-pantallas-y-flujos.md` (skill avicore-negocio)
