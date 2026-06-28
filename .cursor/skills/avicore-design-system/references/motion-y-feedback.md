# Motion y feedback — Refined Agro

Motion = **confirmación de interacción**, no espectáculo.

## Duraciones

| Uso | Duración | Easing |
|-----|----------|--------|
| Color / borde en controles | 150–200ms | `ease-out` |
| Sombra en cards/nav | 200ms | `ease-out` |
| Transform táctil (`active`) | 150ms | `ease-out` |
| Drawer / modal (Alpine) | 250–300ms | `ease-in-out` |

## Patrones por plataforma

### Móvil (operario)

```css
/* Envolver en prefers-reduced-motion — ver app.css operario tab-bar */
transition: color, background-color, transform, box-shadow;
active:scale-95; /* o scale-[0.98] */
```

- **No** usar `hover:` como único feedback.
- Preferir cambio de fondo en ítem activo de nav (ya en `.avicore-operario-tab-bar__item--active`).
- `wire:navigate` en links de nav — sin spinner decorativo salvo carga lenta real.
- Navegación operario: `wire:transition="operario-page"` + `operario-chrome` (View Transitions API, 220–280ms); fallback con clase `avicore-operario-shell--navigating`; respeta `prefers-reduced-motion` (Livewire + CSS).

### Desktop (admin)

```html
class="md:transition-colors md:duration-200 md:hover:bg-avicore-soft md:hover:text-avicore-primary"
```

- Hover solo con prefijo `md:` o `lg:` en filas de tabla, nav links, botones secundarios.
- Mantener `focus-visible` siempre (teclado).

## Alpine.js (patrón WireBlade adaptado)

Drawer sidebar admin en móvil:

```html
x-show="open"
x-transition:enter="transition ease-in-out duration-300"
x-transition:enter-start="-translate-x-full opacity-0"
x-transition:enter-end="translate-x-0 opacity-100"
```

- Un solo panel a la vez (drawer **o** modal).
- `@click.away` para cerrar drawer; restaurar foco al cerrar (`x-ui.dialog` con `applyOpenSideEffects` cuando cierra vía `wire:model`; `x-ui.sheet` con `closeSheet()`).

## prefers-reduced-motion

Siempre envolver transform/scale:

```css
@media (prefers-reduced-motion: no-preference) {
  .avicore-operario-tab-bar__item {
    @apply transition-[color,background-color,transform,box-shadow] duration-200 ease-out active:scale-95;
  }
}
```

Con `reduce`: solo cambios de color, sin scale.

## Logo auth (`entrance`)

- Prop `entrance` en `x-ui.logo` con `size="hero"` o `auth-mobile` + `showName`: isotipo orbita el wordmark y aterriza en posición final (CSS en `app.css`, clases `avicore-logo__orbit-*`).
- Duración 2200ms, `ease-in-out`; contrarrotación del mark para legibilidad.
- Solo en pantallas públicas (login / cambio de contraseña); no en header operario (`size="sm"` sin `entrance`).
- Envolver animaciones en `@media (prefers-reduced-motion: no-preference)` — sin órbita si el usuario pide menos movimiento.

## Prohibido

- `animate-bounce`, `animate-pulse` decorativo en contenido
- Staggered fade-in en listas de datos
- Parallax, scroll-driven animation
- Loading skeletons sin datos async reales
