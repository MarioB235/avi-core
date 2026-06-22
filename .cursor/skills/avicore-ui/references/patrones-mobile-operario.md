# Patrones mobile — Operario

Shell: `components/layouts/operario-mobile.blade.php` · Header: `<x-operario.header>` · Nav: `<x-operario.bottom-nav>`.

## Principios

1. **Thumb zone** — acciones primarias abajo (dock) o en zona central-inferior del contenido.
2. **Sin hover** — feedback con `active:`, cambio de fondo en estado activo, y `focus-visible` para teclado/accesibilidad.
3. **Una acción primaria** por pantalla cuando sea posible.
4. **Densidad vertical** — listas con `min-h-[3.25rem]` o más; padding generoso en cards.
5. **Safe area** — header Inicio: `max(2.75rem, safe-area + 1.25rem)`; dock con `safe-area-inset-bottom` (`resources/css/operario.css`).

## Navegación

- **Fuente única de rutas y títulos:** `App\Support\OperarioNav` — pestañas (`tabs()`), título de header (`headerTitle()`), activo (`tabIsActive()`). Consumido por `<x-operario.bottom-nav>` y `OperarioLayoutComposer`; no duplicar arrays de rutas en Blade.
- 4 pestañas en fila: Inicio · Galpón · Cargar · Historial — **mismo diseño** en todos los ítems.
- Ítem **activo**: círculo verde sólido (`avicore-primary`), icono blanco, borde blanco fino; sobresale por encima del borde superior de la barra (efecto «notch» visual).
- Barra inferior **integrada**: esquinas superiores redondeadas, sombra suave hacia arriba, `safe-area-inset-bottom`.
- Ítems inactivos: icono y label en `avicore-muted`, sin círculo ni elevación.
- `wire:navigate` en todos los links; feedback táctil `active:scale-95` (con `prefers-reduced-motion`).
- Cambio de galpón **solo** en pestaña Galpón.

## Header contextual

- **Inicio:** `<x-operario.home-hero>` integra foto, header (safe-area), saludo y chip galpón enlazado a `/operario/galpon`.
- **Inicio (header):** logo + nombre/rol + avatar; **sin** chevron decorativo (no hay menú de usuario en MVP).
- **Otras rutas:** barra con badge «Operario», título de sección, chip galpón y avatar.
- Galpón seleccionado: chip verde sólido (`avicore-primary`); sin galpón: chip ámbar con icono warehouse.
- En pestaña Galpón: subtítulo plano «Seleccioná dónde vas a cargar hoy».
- Datos vía `OperarioLayoutComposer` — no duplicar lógica en cada Livewire.

## Inicio operario

- `<x-operario.home-hero>` — bloque único con foto, header, saludo («Acá tenés el resumen de tu granja.») y chip galpón (icono `warehouse`; vacío = ámbar + «Sin seleccionar»).
- `.avicore-operario-home-cargas` — panel «Últimas cargas de hoy» con `min-height: 50dvh`; lista con scroll interno; vacío centrado.
- `.avicore-operario-home-summary` — bloque KPI debajo del panel de cargas (maples = suma huevos del día ÷ 30; ver `reglas.md`).
- Estilos del módulo: `resources/css/operario.css` (no mezclar en `app.css`).

## Formularios de carga

- Inputs numéricos grandes; botón guardar ancho completo en móvil.
- Validación inline; sin modales innecesarios.
- Tras guardar: feedback claro (toast o redirect a historial).

## Motion (operario)

- `active:scale-95` en ítems del tab bar (con `prefers-reduced-motion`).
- Sin animaciones de entrada en listas de historial.

## Touch targets

- Mínimo **44×44px** en todos los controles clicables.
- Espacio entre ítems táctiles ≥ 8px.

## Referencias

- Design system: `refined-agro-principios.md`, `motion-y-feedback.md`, `elevacion-y-superficies.md`
- Flujos: `02-pantallas-y-flujos.md` (skill avicore-negocio)
