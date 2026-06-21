# Refined Agro — Principios de diseño AviCore

Evolución del contrato **clean + enterprise** con polish controlado. Reemplaza la prohibición absoluta de motion/blur del contrato anterior.

## Identidad

- **Verde / agro moderno** — profesional, confiable, campo + empresa.
- Paleta canónica: `guia-visual.md` y tokens `avicore-*` en `resources/css/app.css`.
- Inspiración TALL (Soft UI, WireBlade, dashboard): **patrones de layout y feedback**, no colores ni dependencias.

## Tres capas visuales

1. **Estructura** — jerarquía clara (título → subtítulo → contenido → acción). Una idea por bloque.
2. **Superficie** — cards y chrome con elevación sutil (ver `elevacion-y-superficies.md`).
3. **Motion** — feedback de interacción, no decoración (ver `motion-y-feedback.md`).

## Mobile vs desktop

| Contexto | Modo | Regla clave |
|----------|------|-------------|
| Operario en campo | Móvil primero | Sin `hover`; touch ≥44px; bottom nav; `active:` para feedback |
| Panel admin | Desktop primero | `hover:` desde `md:`; sidebar + tablas; más densidad horizontal |

Detalle: skill `avicore-ui` → `patrones-mobile-operario.md` / `patrones-web-admin.md`.

## Lista blanca (permitido)

- Transiciones 150–300ms en color, sombra, transform sutil
- `hover:` en admin ≥ `md:`
- `active:scale-[0.98]` o `active:scale-95` en controles táctiles
- `backdrop-blur-md` solo en chrome fijo (nav operario, modales)
- Sombras `shadow-sm` / `shadow-md` con opacidad baja
- Gradiente **local** de lectura en operario (`.avicore-operario-shell::before`) — ya implementado

## Lista negra (prohibido)

- Paleta morada/fuchsia Soft UI o gradientes de marca ajenos
- `hover:scale-102` u homotecia permanente en desktop
- Animaciones de entrada en cada ítem de lista (`fade-in` cascada)
- Glassmorphism en fondo global de la app
- Instalar WireBlade, Flux, Soft UI, Filament como dependencia
- Más de tres colores de acento visibles por pantalla

## Implementación

- Componentes: `resources/views/components/ui/*`
- Utilidades: `resources/css/app.css` (clases `.avicore-*`)
- Sin estilos inline; reutilizar `x-ui.*` antes de duplicar clases
