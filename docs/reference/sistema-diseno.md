# Sistema de diseño AviCore

Referencia técnica para implementar UI con Tailwind 4 y componentes Blade en `resources/views/components/ui/`.

## Origen y elección de guía externa

Registro: [awesome-design-skills](https://github.com/bergside/awesome-design-skills) (Type UI).

| Skill evaluado | Uso en AviCore |
|----------------|----------------|
| **clean** | **Base principal** — whitespace, tipografía legible, paleta limitada, sin decoración |
| **enterprise** | **Solo patrones** — jerarquía, sidebar, datos operativos (sin copiar paleta teal/naranja) |
| minimal / sleek | Referencia de densidad; paleta siempre AviCore |
| glassmorphism / neon / gradient / bento / etc. | **Descartados** — contradicen identidad agro y skill clean |

**Paleta de marca:** siempre `docs/03-guia-visual-ui.md` (verde/agro). Los skills genéricos no reemplazan colores AviCore.

## Principios clean aplicados

1. **Mucho aire** — una idea por bloque; pocos elementos por pantalla.
2. **Color con criterio** — verde solo en marca, botón primario y acentos puntuales.
3. **Sin motion decorativo** — solo `transition-colors` en controles; respetar `prefers-reduced-motion`.
4. **Sin capas visuales extra** — no gradientes de fondo, blur, sombras fuertes ni paneles duplicados.
5. **Tipografía 12/14/16/20/24/32** — rejilla 8pt; labels en uppercase solo en KPIs/tablas.

## Assets de marca

| Archivo | Uso |
|---------|-----|
| `logo-avicore.svg` | Isotipo con fondo transparente — **única copia:** `public/images/brand/logo-avicore.svg` (`x-ui.logo`) |
| `background-mobile.jpg` | Fondo ≤1023px — fuente en `resources/images/brand/`, copia en `public/images/brand/` |
| `background-desktop.jpg` | Fondo ≥1024px — idem |

Tras cambiar fondos JPEG/PNG: `python scripts/optimize-brand-assets.py` (comprime y sincroniza `public/`). El logo SVG se edita o reemplaza solo en `public/images/brand/`.

Capa scrim eliminada en auth; legibilidad con tarjeta blanca `.avicore-auth-card`. Fondos referenciados desde Vite en `resources/css/app.css`.

**Input contraseña:** `x-ui.input` con `toggle-password`; clase `.avicore-password-input` oculta el reveal nativo del navegador (un solo icono ojo).

## Anti-patrones (no implementar)

- Panel lateral de marca + contenido duplicado en login/home.
- Grillas de tarjetas informativas cuando el módulo aún no existe.
- Banners con gradiente, badges decorativos o iconos en cada bloque.
- Animaciones de entrada, scale en botones, backdrop-blur.
- Más de tres colores de acento visibles en una misma pantalla.

## Tokens (fuente: `resources/css/app.css`)

| Token | Uso |
|-------|-----|
| `avicore-primary` / `secondary` / `soft` | Marca y fondos de acento |
| `avicore-surface` / `card` | Fondo app y tarjetas |
| `avicore-text` / `muted` | Texto principal y secundario |
| `avicore-border` / `border-strong` | Bordes de inputs y paneles |
| `avicore-success` / `warning` / `danger` / `info` | Estados semánticos |

## Componentes UI

| Componente | Variantes / notas |
|------------|-------------------|
| `x-ui.button` | `primary`, `secondary`, `danger`, `ghost` — min-h 44px, `focus-visible` |
| `x-ui.input` | `aria-invalid`, estados error, `hint`, `toggle-password` (un solo toggle visible) |
| `x-ui.card` | Borde simple, sin sombra; `padding`: `default`, `compact`, `none` |
| `x-ui.alert` | `info`, `success`, `warning`, `danger` |
| `x-ui.badge` | Estados semánticos |
| `x-ui.logo` | Marca — `public/images/brand/logo-avicore.svg` (verde `#1F5E3B`) + subtítulo opcional |
| `x-ui.icon` | SVG inline por nombre (`menu`, `document`, `lock`, `eye`, …) — nav, inputs, acciones |
| `x-ui.kpi-card` | Label + valor + hint; para dashboard |
| `x-ui.nav-link` | Sidebar admin |

## Layouts

| Layout | Archivo | Uso |
|--------|---------|-----|
| Público | `components/layouts/public.blade.php` | Login, cambio de contraseña — split marca + tarjeta (≥1024px); partial `auth-brand-panel` |
| Admin | `components/layouts/admin.blade.php` | Sidebar fija (desktop) + drawer Alpine (móvil); partials `admin-sidebar-inner`, `admin-nav` |
| Operario | `components/layouts/operario-mobile.blade.php` | Vista móvil — fondo marca responsive |

## Quality gates

1. Tokens `avicore-*`; sin inline styles.
2. Estados `hover`, `focus-visible`, `disabled` en controles.
3. Contraste WCAG AA.
4. Touch ≥ 44px en operario.
5. Sin `!important` en Tailwind.

## Enlaces

- Guía producto: [`../03-guia-visual-ui.md`](../03-guia-visual-ui.md)
- awesome-design-skills clean: `skills/clean/SKILL.md` en el registro
- Pantallas: [`../02-pantallas-y-flujos.md`](../02-pantallas-y-flujos.md)
