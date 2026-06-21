# Patrones mobile — Operario

Shell: `components/layouts/operario-mobile.blade.php` · Nav: `<x-operario.bottom-nav>`.

## Principios

1. **Thumb zone** — acciones primarias abajo (dock) o en zona central-inferior del contenido.
2. **Sin hover** — feedback con `active:`, cambio de fondo en estado activo, y `focus-visible` para teclado/accesibilidad.
3. **Una acción primaria** por pantalla cuando sea posible.
4. **Densidad vertical** — listas con `min-h-[3.25rem]` o más; padding generoso en cards.
5. **Safe area** — respetar `env(safe-area-inset-*)` en dock y header (clases en `app.css`).

## Navegación

- 4 pestañas fijas: Inicio, Cargar, Historial, Galpón.
- `wire:navigate` en links del dock y listas.
- Ítem activo: fondo primario en celda (`.avicore-operario-tab-bar__item--active`), no solo color de icono.
- Cambio de galpón **solo** en pestaña Galpón (no duplicar en header).

## Header contextual

- Título de sección + galpón seleccionado o hint «Elegí un galpón».
- Datos vía `OperarioLayoutComposer` — no duplicar lógica en cada Livewire.

## Formularios de carga

- Inputs numéricos grandes; botón guardar ancho completo en móvil.
- Validación inline; sin modales innecesarios.
- Tras guardar: feedback claro (toast o redirect a historial).

## Motion (operario)

- `active:scale-95` en ítems del tab bar (con `prefers-reduced-motion`).
- `backdrop-blur-md` solo en `.avicore-operario-tab-bar`.
- Sin animaciones de entrada en listas de historial.

## Touch targets

- Mínimo **44×44px** en todos los controles clicables.
- Espacio entre ítems táctiles ≥ 8px.

## Referencias

- Design system: `refined-agro-principios.md`, `motion-y-feedback.md`, `elevacion-y-superficies.md`
- Flujos: `02-pantallas-y-flujos.md` (skill avicore-negocio)
