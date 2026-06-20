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
4. **Sin capas visuales extra** — no gradientes de fondo globales, blur, sombras fuertes ni paneles duplicados. **Excepciones:** tarjeta auth sobre foto; hero Inicio admin (`<x-admin.home-hero>`: `<img>` a ancho completo de la columna principal, KPIs en `max-w-7xl`; grid + `translateY(55%)` en ≥1024px).
5. **Tipografía 12/14/16/20/24/32** — rejilla 8pt; labels en uppercase solo en KPIs/tablas.

## Assets de marca

| Archivo | Uso |
|---------|-----|
| `logo-avicore.svg` | Isotipo con fondo transparente — **única copia:** `public/images/brand/logo-avicore.svg` (`x-ui.logo`) |
| `background-mobile.jpg` | Fondo ≤1023px — fuente en `resources/images/brand/`, copia en `public/images/brand/` |
| `background-desktop.jpg` | Fondo ≥1024px — idem |
| `admin-home-hero.jpg` | Hero Inicio admin (≥1024px) — fuente PNG/JPG en `resources/images/brand/`; degradado inferior documentado como excepción clean |
| `admin-home-hero-mobile.jpg` | Reservado — hero Inicio admin en móvil (pendiente asset) |

Tras cambiar fondos JPEG/PNG: `python scripts/optimize-brand-assets.py` (comprime y sincroniza `public/`). El logo SVG se edita o reemplaza solo en `public/images/brand/`.

**Iconos Lucide:** fuente preferida en `resources/images/icons/` (nombres kebab-case); `App\Support\IconSvg` carga el SVG del disco cuando existe y, si no, usa fallback inline en `components/ui/icons/inline.blade.php`. En pantalla: `x-ui.icon` con `stroke="currentColor"` (color vía Tailwind).

Capa scrim eliminada en auth; legibilidad con tarjeta blanca `.avicore-auth-card` (elevación `shadow-sm`/`shadow-md`, excepción documentada frente a cards KPI). Panel de marca escritorio: `.avicore-auth-brand` alinea logo (`x-ui.logo` size `hero`) y copy en columna (`auth-brand-panel`). Fondos referenciados desde Vite en `resources/css/app.css`.

**Recuperación MVP:** `config/avicore.php` (`.env` → `AVICORE_SUPPORT_*`) + `App\Services\SupportContactService` (valida WhatsApp/correo, construye `wa.me`/`mailto` con mensaje prefijado). Vista compuesta `x-auth.support-contact-dialog` sobre `x-ui.dialog`.

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
| `x-ui.input` | `aria-invalid`, estados error, `hint`, `toggle-password` (un solo toggle visible); icono leading en `avicore-primary`, toggle ojo en `avicore-muted` |
| `x-ui.card` | Borde simple, sin sombra; `padding`: `default`, `compact`, `none` |
| `x-ui.alert` | `info`, `success`, `warning`, `danger` |
| `x-ui.badge` | Estados semánticos; variante `sidebar` para badges sobre fondo verde |
| `x-ui.logo` | Marca — `public/images/brand/logo-avicore.svg` (verde `#1F5E3B`) + subtítulo opcional; `theme="on-primary"` en sidebar admin (texto blanco, icono sobre fondo blanco); `stacked` + `size="auth-mobile"` en login móvil |
| `x-ui.icon` | SVG inline por nombre (`menu`, `document`, `lock`, `eye`, `circle-x`, `mail`, `message-circle-check`, …) — nav, inputs, acciones; fuente Lucide en `resources/images/icons/` |
| `x-ui.kpi-card` | Label + valor + hint; prop `icon` opcional; para dashboard e Inicio admin |
| `x-ui.nav-link` | Sidebar admin — props `icon`, `active`, `disabled` |
| `x-ui.empty-state` | Empty state con icono, título y descripción |
| `x-ui.setup-checklist` | Lista de pasos de configuración inicial con badge de estado |
| `x-ui.user-avatar` | Iniciales circulares; prop `decorative` cuando el nombre visible está al lado (header/sidebar) — si no, `role="img"` + `aria-label` |
| `x-ui.dialog` | Diálogo modal Alpine — `title`, slot `trigger`; telón `.avicore-dialog__backdrop` (`bg-avicore-text/65`, fade sin scale), panel centrado, focus trap (Tab/Escape), restauración de foco al cerrar, bloquea scroll del body |
| `x-auth.support-contact-dialog` | Recuperación MVP — trigger «¿Olvidaste tu contraseña?», enlaces WhatsApp/correo vía `SupportContactService`; props `trigger`, `dialogTitle`, `intro`, `footer` |

## Layouts

| Layout | Archivo | Uso |
|--------|---------|-----|
| Público | `components/layouts/public.blade.php` | Login, cambio de contraseña — split marca + tarjeta (≥1024px); móvil: logo apilado + bottom sheet (`.avicore-auth-mobile-brand`, `.avicore-auth-card`); partial `auth-brand-panel` |
| Admin | `components/layouts/admin.blade.php` | Shell `.avicore-admin-*`: sidebar sticky verde (`bg-avicore-primary`, nav clara, labels de sección) + drawer Alpine (móvil), header y main con gutter común (`avicore-admin-gutter`); partials `admin-sidebar-inner`, `admin-nav`, `admin-header-toolbar`, `admin-menu-trigger` |
| Operario | `components/layouts/operario-mobile.blade.php` | Vista móvil — fondo marca responsive |

## Quality gates

1. Tokens `avicore-*`; sin inline styles.
2. Estados `hover`, `focus-visible`, `disabled` y `cursor: pointer` en controles clicables habilitados (regla base en `app.css`; `disabled:cursor-not-allowed` donde aplique).
3. Contraste WCAG AA.
4. Touch ≥ 44px en operario.
5. Sin `!important` en Tailwind.

## Enlaces

- Guía producto: [`../03-guia-visual-ui.md`](../03-guia-visual-ui.md)
- awesome-design-skills clean: `skills/clean/SKILL.md` en el registro
- Pantallas: [`../02-pantallas-y-flujos.md`](../02-pantallas-y-flujos.md)
