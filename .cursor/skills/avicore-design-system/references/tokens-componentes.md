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

**Paleta de marca:** siempre `guia-visual.md` (verde/agro). Los skills genéricos no reemplazan colores AviCore.

## Principios Refined Agro (clean + polish controlado)

Ver [`refined-agro-principios.md`](refined-agro-principios.md), [`motion-y-feedback.md`](motion-y-feedback.md), [`elevacion-y-superficies.md`](elevacion-y-superficies.md).

1. **Mucho aire** — una idea por bloque; pocos elementos por pantalla.
2. **Color con criterio** — verde solo en marca, botón primario y acentos puntuales.
3. **Motion con propósito** — transiciones 150–300ms en color, sombra y transform sutil; `prefers-reduced-motion` obligatorio en `scale`/`transform`.
4. **Elevación sutil** — `shadow-sm`/`shadow-md`; `backdrop-blur` solo en chrome fijo (nav operario, modales). **Excepciones:** tarjeta auth sobre foto; hero Inicio admin; gradiente local operario (`.avicore-operario-shell::before`).
5. **Tipografía 12/14/16/20/24/32** — rejilla 8pt; labels en uppercase solo en KPIs/tablas.
6. **Mobile vs admin** — operario sin `hover` como feedback; admin con `hover:` desde `md:`.

## Assets de marca

| Archivo | Uso |
|---------|-----|
| `logo-avicore.png` | Isotipo PNG — fuente `resources/images/brand/`, copia en `public/images/brand/` (`x-ui.logo`) |
| `login-background.jpg` | Fondo login ≤1023px — fuente PNG en `resources/images/brand/login-background.png` |
| `background-desktop.jpg` | Fondo ≥1024px — idem |
| `admin-home-hero.jpg` | Hero Inicio admin (≥1024px) — fuente PNG/JPG en `resources/images/brand/`; degradado inferior documentado como excepción clean |
| `admin-home-hero-mobile.jpg` | Reservado — hero Inicio admin en móvil (pendiente asset) |
| `operario-home-hero.jpg` | *(legacy)* Fuente PNG en `resources/images/brand/` — ya no se renderiza; hero Inicio usa degradado CSS en `operario.css` |
| `operario-cargar-hero.jpg` | *(legacy)* Fuente PNG en `resources/images/brand/` — ya no se renderiza; hero Cargar usa degradado CSS en `operario.css` |

Tras cambiar fondos JPEG/PNG: `python scripts/optimize-brand-assets.py` (comprime fondos y sincroniza logo + `public/`).

**Iconos Lucide:** fuente preferida en `resources/images/icons/` (nombres kebab-case); `App\Support\IconSvg` carga el SVG del disco cuando existe y, si no, usa fallback inline en `components/ui/icons/inline.blade.php`. En pantalla: `x-ui.icon` con `stroke="currentColor"` (color vía Tailwind).

Capa scrim eliminada en auth; legibilidad con tarjeta blanca `.avicore-auth-card` (elevación `shadow-sm`/`shadow-md`, excepción documentada frente a cards KPI). Panel de marca escritorio: `.avicore-auth-brand` alinea logo (`x-ui.logo` size `hero`) y copy en columna (`auth-brand-panel`). Fondos referenciados desde Vite en `resources/css/app.css`.

**Recuperación MVP:** `config/avicore.php` (`.env` → `AVICORE_SUPPORT_*`) + `App\Services\SupportContactService` (valida WhatsApp/correo, construye `wa.me`/`mailto` con mensaje prefijado). Vista compuesta `x-auth.support-contact-dialog` sobre **`x-ui.sheet`** (bottom sheet en auth; el nombre del componente es histórico).

**Input contraseña:** `x-ui.input` con `toggle-password`; clase `.avicore-password-input` oculta el reveal nativo del navegador (un solo icono ojo).

## Lista blanca motion / superficie (Refined Agro)

| Efecto | Dónde | Notas |
|--------|-------|-------|
| `transition-colors` 150–200ms | Controles, nav | Siempre permitido |
| `transition-shadow` 200ms | Cards admin | Con `md:hover:shadow-md` |
| `active:scale-[0.98]` / `active:scale-95` | Botones/listas móvil | Dentro de `@media (prefers-reduced-motion: no-preference)` |
| `md:hover:*` | Admin sidebar, tablas, cards | No en operario como único feedback |
| `backdrop-blur-md` | `.avicore-operario-tab-bar`, modales | No en fondo global ni cards KPI |
| Órbita logo auth (`entrance`) | `x-ui.logo` en login (`hero` / `auth-mobile`) | 2200ms ease-in-out; `prefers-reduced-motion` desactiva animación (`app.css`) |
| Alpine `x-transition` 150–300ms | Drawer admin, modales, bottom sheet operario, menú cuenta operario | Un panel a la vez |

## Anti-patrones (no implementar)

- Panel lateral de marca + contenido duplicado en login/home.
- Grillas de tarjetas informativas cuando el módulo aún no existe.
- Banners con gradiente ajeno a marca, badges decorativos o iconos en cada bloque.
- Animaciones de entrada en cada ítem de lista; `hover:scale-102`; glassmorphism global.
- Paleta morada/fuchsia Soft UI; instalar UI kits externos (WireBlade, Flux, Soft UI).
- Más de tres colores de acento visibles en una misma pantalla.

## Tokens (fuente: `resources/css/app.css`)

| Token | Uso |
|-------|-----|
| `avicore-primary` / `secondary` / `soft` | Marca y fondos de acento |
| `avicore-surface` / `card` | Fondo app y tarjetas |
| `avicore-text` / `muted` | Texto principal y secundario |
| `avicore-border` / `border-strong` | Bordes de inputs y paneles |
| `avicore-success` / `warning` / `danger` / `info` | Estados semánticos |
| `--avicore-operario-brand-surface` | Degradado heroes operario (body `.avicore-operario-body`, `__media`, shell) — `@theme` en `app.css` |

## Componentes UI

| Componente | Variantes / notas |
|------------|-------------------|
| `x-ui.button` | `primary`, `secondary`, `danger`, `ghost` — min-h 44px, `focus-visible` |
| `x-ui.input` | `aria-invalid`, estados error, `hint`, `toggle-password` (un solo toggle visible); icono leading en `avicore-primary`, toggle ojo en `avicore-muted` |
| `x-ui.card` | Borde simple, sin sombra; `padding`: `default`, `compact`, `none` |
| `x-ui.alert` | `info`, `success`, `warning`, `danger` |
| `x-ui.snackbar-host` | Toast fijo — fondo sólido suave por variante (`success` = `avicore-soft`, texto oscuro); `context` (`operario` \| `default`), auto-cierre ~4,5s, botón cerrar, evento `snackbar-show`; flash `status` + `status_variant` |
| `x-ui.badge` | Estados semánticos; variante `sidebar` para badges sobre fondo verde |
| `x-ui.logo` | Marca — `public/images/brand/logo-avicore.png` + subtítulo opcional; `entrance` (órbita isotipo en `hero` / `auth-mobile` con `showName`) en auth; `theme="on-primary"` en sidebar admin (texto blanco, icono sobre fondo blanco); `stacked` + `size="auth-mobile"` en login móvil |
| `x-ui.icon` | SVG inline por nombre (`menu`, `document`, `lock`, `eye`, `circle-x`, `mail`, `message-circle-check`, …) — nav, inputs, acciones; fuente Lucide en `resources/images/icons/` |
| `x-ui.kpi-card` | Label + valor + hint; prop `icon` opcional; para dashboard e Inicio admin |
| `x-ui.nav-link` | Sidebar admin — props `icon`, `active`, `disabled` |
| `x-ui.empty-state` | Empty state con icono, título y descripción |
| `x-ui.setup-checklist` | Lista de pasos de configuración inicial con badge de estado |
| `x-ui.user-avatar` | Iniciales circulares; prop `decorative` cuando el nombre visible está al lado (header/sidebar) — si no, `role="img"` + `aria-label` |
| `x-ui.dialog` | Diálogo modal Alpine — `title`, slot `trigger` **o** `wire:model` (Livewire); panel centrado; focus trap; `applyOpenSideEffects` sincroniza scroll/foco al cerrar vía entangle |
| `x-ui.sheet` | Bottom sheet Alpine — slot `trigger` **o** `wire:model`; panel anclado abajo (slide-up), handle, safe-area; auth recuperación contraseña |
| `x-auth.support-contact-dialog` | Recuperación MVP — trigger «¿Olvidaste tu contraseña?», bottom sheet (`x-ui.sheet`); enlaces WhatsApp/correo vía `SupportContactService`; props `trigger`, `dialogTitle`, `intro`, `footer` |
| `x-operario.primary-action` | Inicio — CTA «Registrar producción» (verde sólido, enlace a hub Cargar) |
| `x-operario.home-hero` | Inicio — fondo degradado suave + saludo horario compacto + chip galpón (`home-hero.blade.php`); nav fijo va en layout |
| `x-operario.cargar-hero` | Hub Cargar — mismo fondo degradado suave que Inicio; header estándar; chip galpón solo lectura |
| `x-operario.historial-hero` | Historial — mismo hero/header que Inicio; chip galpón solo lectura |
| `x-operario.header` | Barra operario — variante hero (grilla logo/usuario + divisor ogee inferior; gradiente SVG con tokens `--color-avicore-*`) o contextual (título + chip en tarjeta); integra `<x-operario.user-menu>` |
| `x-operario.user-menu` | Menú cuenta operario — dropdown desde avatar (`x-ui.user-avatar`); Perfil (subvista) + Cerrar sesión; ARIA `menu` / `menuitem`; props `size`, `avatarClass` |
| `x-operario.bottom-nav` | Barra inferior integrada — 3 pestañas (Inicio `home`, Cargar `plus`, Historial `calendar`); ítem activo con círculo verde sobresaliente; datos desde `OperarioNav` |

## Layouts

| Layout | Archivo | Uso |
|--------|---------|-----|
| Público | `components/layouts/public.blade.php` | Login, cambio de contraseña — split marca + tarjeta (≥1024px); móvil: logo apilado + bottom sheet (`.avicore-auth-mobile-brand`, `.avicore-auth-card`); partial `auth-brand-panel` |
| Admin | `components/layouts/admin.blade.php` | Shell `.avicore-admin-*`: sidebar sticky verde (`bg-avicore-primary`, nav clara, labels de sección) + drawer Alpine (móvil), header y main con gutter común (`avicore-admin-gutter`); partials `admin-sidebar-inner`, `admin-nav`, `admin-header-toolbar`, `admin-menu-trigger` |
| Operario | `components/layouts/operario-mobile.blade.php` | Shell `.avicore-operario-shell` — header `<x-operario.header>` fijo en layout (hero `isHomePage` en Inicio/Cargar/Historial; contextual en rutas legacy) + barra inferior `<x-operario.bottom-nav>`; datos de galpón vía `OperarioLayoutComposer`; pestañas/títulos vía `OperarioNav` |

## Quality gates

1. Tokens `avicore-*`; sin inline styles.
2. Estados `hover`, `focus-visible`, `disabled` y `cursor: pointer` en controles clicables habilitados (regla base en `app.css`; `disabled:cursor-not-allowed` donde aplique).
3. Contraste WCAG AA.
4. Touch ≥ 44px en operario.
5. Sin `!important` en Tailwind.

## Enlaces

- Guía producto: [`guia-visual.md`](guia-visual.md)
- Patrones TALL (índice): [`INDICE-TALL-REFERENCIA.md`](INDICE-TALL-REFERENCIA.md)
- Snippets: [`ejemplos-snippet.md`](ejemplos-snippet.md)
- Mobile / admin: `avicore-ui/references/patrones-mobile-operario.md`, `patrones-web-admin.md`
- Pantallas: `avicore-ui/references/pantallas-flujos.md`
