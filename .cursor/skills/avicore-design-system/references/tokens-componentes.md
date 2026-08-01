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
| `pwa-192.png`, `pwa-512.png`, `pwa-512-maskable.png` | Iconos PWA — generados por `scripts/optimize-brand-assets.py`; manifest en `vite.config.js` |
| `login-background.jpg` | Fondo login ≤1023px — fuente PNG en `resources/images/brand/login-background.png` |
| `background-desktop.jpg` | Fondo ≥1024px — idem |
| `admin-home-hero.jpg` | Legacy — Inicio admin ya no usa masthead con foto; hero alineado a operario (degradado CSS). Asset puede quedar en brand hasta limpieza |
| `admin-home-hero-mobile.jpg` | Legacy / reservado — no usado en shell actual |
| `operario-home-hero.jpg` | **Eliminado** — hero Inicio usa degradado CSS en `operario.css` (fuente canónica: Vite/`resources`; no duplicar en `public/images/brand/` salvo assets servidos sin build) |
| `operario-cargar-hero.jpg` | **Eliminado** — hero Cargar usa degradado CSS en `operario.css` |

Tras cambiar fondos JPEG/PNG o el logo: `python scripts/optimize-brand-assets.py` (comprime fondos, sincroniza logo, genera iconos PWA en `public/`).

**Iconos Lucide:** fuente preferida en `resources/images/icons/` (nombres kebab-case); `App\Support\IconSvg` carga el SVG del disco cuando existe y, si no, usa fallback inline en `components/ui/icons/inline.blade.php`. En pantalla: `x-ui.icon` con `stroke="currentColor"` (color vía Tailwind).

**Ilustraciones KPI / marca:** SVG a color en `resources/images/illustrations/` (`operario-ave`, `operario-huevo`, `operario-reloj`, `operario-vacuna`); `App\Support\IllustrationSvg` + `x-ui.illustration` (viewBox responsivo, sin envoltorio Lucide). Contenedor unificado en KPIs Inicio, tiles Cargar e Historial: `.avicore-operario-carga-tile__icon` (`size-11`, `rounded-xl`).

Capa scrim eliminada en auth; legibilidad con tarjeta blanca `.avicore-auth-card` (elevación `shadow-sm`/`shadow-md`, excepción documentada frente a cards KPI). Panel de marca escritorio: `.avicore-auth-brand` alinea logo (`x-ui.logo` size `hero`) y copy en columna (`auth-brand-panel`). Fondos referenciados desde Vite en `resources/css/app.css`.

**Recuperación MVP:** `config/avicore.php` (`.env` → `AVICORE_SUPPORT_*`) + `App\Services\SupportContactService` (valida WhatsApp/correo, construye `wa.me`/`mailto` con mensaje prefijado). Vista compuesta `x-auth.support-contact-dialog` sobre **`x-ui.sheet`** (bottom sheet en móvil; diálogo centrado en escritorio ≥1024px; el nombre del componente es histórico).

**Input contraseña:** `x-ui.input` con `toggle-password`; clase `.avicore-password-input` oculta el reveal nativo del navegador (un solo icono ojo). Con `disabled` en el input, el toggle también queda deshabilitado.

## Lista blanca motion / superficie (Refined Agro)

| Efecto | Dónde | Notas |
|--------|-------|-------|
| `transition-colors` 150–200ms | Controles, nav | Siempre permitido |
| `transition-shadow` 200ms | Cards admin | Con `md:hover:shadow-md` |
| `active:scale-[0.98]` / `active:scale-95` | Botones/listas móvil | Dentro de `@media (prefers-reduced-motion: no-preference)` |
| `md:hover:*` | Admin sidebar, tablas, cards | No en operario como único feedback |
| `backdrop-blur-md` | `.avicore-operario-tab-bar`, modales | No en fondo global ni cards KPI |
| Órbita logo auth (`entrance`) | `x-ui.logo` en login (`hero` / `auth-mobile`) | 2200ms ease-in-out; `prefers-reduced-motion` desactiva animación (`app.css`) |
| Alpine `x-transition` 150–300ms | Drawer admin, modales, bottom sheet operario, menú cuenta operario, `x-ui.date-picker` | Un panel a la vez |

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
| `x-ui.input` | `aria-invalid`, estados error, `hint`, `toggle-password` (un solo toggle visible; botón ojo **disabled** si el input está `disabled`); icono leading en `avicore-primary`, toggle ojo en `avicore-muted` |
| `x-ui.select` | Lista desplegable custom (Alpine + listbox): trigger `.avicore-select-trigger`, panel `.avicore-select-panel--below` \| `--above` (flip según espacio en viewport vía `syncPanelPosition()`), lista `.avicore-select-list` con `maxHeight` dinámico; opción `.avicore-select-option` con `md:hover:bg-avicore-soft` (escritorio); activa `.avicore-select-option--active` (`bg-avicore-primary/8` + texto `avicore-text`, ring suave, `md:hover:bg-avicore-primary/12`); `wire:model` vía `@entangle` — **defer por defecto** (`.live` solo con `wire:model.live`); en formularios operario preferir `wire:model.defer`; `placeholder` + `options`; recálculo en `resize` mientras está abierto |
| `x-ui.date-picker` | Calendario custom (Alpine): trigger `.avicore-date-picker-trigger`, panel teletransportado `.avicore-date-picker-panel` — **móvil** bottom sheet (slide-up + handle); **escritorio ≥1024px** diálogo centrado (`rounded-2xl`, sin handle, fade); grilla mes Lu–Do; props `min` / `max` / `today` / `error`; CTA «Hoy»; `dayAriaLabel`; error unificado (prop o bag `$errors`); `wire:model` vía `@entangle`; sin `input type="date"` nativo — Historial operario (`fechaError` → `:error`) |
| `x-ui.card` | Borde simple, sin sombra; `padding`: `default`, `compact`, `none` |
| `x-ui.alert` | `info`, `success`, `warning`, `danger` |
| `x-ui.snackbar-host` | Toast fijo — tarjeta `avicore-card` con franja lateral por variante, icono en chip soft; auto-cierre ~4,5s (pausa al hover/foco; cierre manual × o Escape); `context` (`operario` \| `default`); móvil centrado sobre dock / `bottom-6`; escritorio (`lg+`) anclado **abajo a la derecha**; evento `snackbar-show`; flash `status` + `status_variant` |
| `x-ui.badge` | Estados semánticos; variante `sidebar` para badges sobre fondo verde |
| `x-ui.logo` | Marca — `public/images/brand/logo-avicore.png` + subtítulo opcional; `entrance` (órbita isotipo en `hero` / `auth-mobile` con `showName`) en auth; `theme="on-primary"` en sidebar admin (texto blanco, icono sobre fondo blanco); `stacked` + `size="auth-mobile"` en login móvil |
| `x-ui.icon` | SVG inline por nombre (`menu`, `document`, `lock`, `eye`, `circle-x`, `mail`, `message-circle-check`, …) — nav, inputs, acciones; fuente Lucide en `resources/images/icons/` |
| `x-ui.illustration` | Ilustración SVG a color por nombre (`operario-ave`, `operario-huevo`, `operario-reloj`, `operario-vacuna`, …) — KPIs, tiles Cargar, Historial; fuente en `resources/images/illustrations/` |
| `x-ui.kpi-card` | Label + valor + hint; prop `icon` opcional; para dashboard e Inicio admin |
| `x-ui.nav-link` | Sidebar (admin u operario escritorio) — props `icon`, `active`, `disabled` |
| `x-ui.empty-state` | Empty state con icono, título y descripción |
| `x-ui.setup-checklist` | Lista de pasos de configuración inicial con badge de estado |
| `x-ui.user-avatar` | Iniciales circulares; sizes `sm` (2.25rem), `nav` (2.75rem, home-nav operario), `md` (2.5rem); prop `decorative` cuando el nombre visible está al lado (header/sidebar) — si no, `role="img"` + `aria-label` |
| `x-ui.user-menu` | Menú cuenta compartido (admin + operario) — panel teleport + clamp viewport; variante `sidebar`; Perfil + Cerrar sesión; props `size`, `avatarClass`, `variant` |
| `x-ui.pwa-meta` | Meta PWA — manifest (`build/manifest.webmanifest`), `apple-touch-icon` (`pwa-192.png`), flags iOS; solo si `config('avicore.pwa.enabled')` |
| `x-ui.pwa-install-prompt` | Banner inferior «Instalá AviCore» — Alpine (`beforeinstallprompt` / guía iOS), icono `pwa-192.png`, dismiss en `localStorage`; solo si `config('avicore.pwa.install_prompt')`; estilos `.avicore-pwa-install*` en `app.css` |
| `x-ui.dialog` | Diálogo modal Alpine — `title`, slot `trigger` **o** `wire:model` (Livewire); panel centrado; focus trap; `applyOpenSideEffects` sincroniza scroll/foco al cerrar vía entangle |
| `x-ui.sheet` | Overlay Alpine — slot `trigger` **o** `wire:model`; **móvil** bottom sheet (slide-up, handle, safe-area); **escritorio ≥1024px** panel centrado tipo diálogo (`max-w-md`, sin handle); auth recuperación contraseña |
| `x-auth.support-contact-dialog` | Recuperación MVP — trigger «¿Olvidaste tu contraseña?», overlay (`x-ui.sheet` responsive); enlaces WhatsApp/correo vía `SupportContactService`; props `trigger`, `dialogTitle`, `intro`, `footer` |
| `x-operario.primary-action` | Inicio — CTA «Registrar producción» (verde sólido, enlace a hub Cargar) |
| `x-operario.home-hero` | Inicio — fondo degradado suave + saludo horario compacto + chip galpón (`home-hero.blade.php`); nav fijo va en layout |
| `x-operario.cargar-hero` | Hub Cargar — mismo fondo degradado suave que Inicio; chip galpón **interactivo** (slot `galponSelector` + `ManagesGalponSelector`) |
| `x-operario.historial-hero` | Historial — mismo hero que Inicio; chip galpón **interactivo** (mismo selector) |
| `x-operario.header` | Barra operario — variante hero o contextual; integra `<x-operario.user-menu>` (alias de `x-ui.user-menu`); en `lg+` el home-nav se oculta |
| `x-operario.user-menu` | Alias de `x-ui.user-menu` para vistas operario |
| `x-operario.sidebar-nav` | Nav escritorio (`lg+`) — logo «Carga en campo», `OperarioNav` + `x-ui.nav-link`, cuenta con `user-menu variant="sidebar"`; oculta en `< lg` |
| `x-operario.bottom-nav` | Barra inferior integrada — 3 pestañas (Inicio `home`, Cargar `plus`, Historial `calendar`); `lg:hidden`; ítem activo con círculo verde sobresaliente; datos desde `OperarioNav` |
| `x-admin.sidebar-nav` | Nav escritorio panel — mismas clases que operario; tabs `AdminNav`; subtítulo empresa |
| `x-admin.bottom-nav` | Bottom nav panel (`lg:hidden`) — Inicio · Usuarios (solo gestión) |
| `x-admin.header` | Home-nav en heroes o título+badge; menú `x-ui.user-menu` |
| `x-admin.home-hero` | Saludo horario + subtítulo + slot chip empresa (Inicio) |
| `x-admin.page-hero` | Hero de módulo (p. ej. Usuarios) alineado al home-hero |

## Layouts

| Layout | Archivo | Uso |
|--------|---------|-----|
| Público | `components/layouts/public.blade.php` | Login, cambio de contraseña — split marca + tarjeta (≥1024px); móvil: logo apilado + bottom sheet (`.avicore-auth-mobile-brand`, `.avicore-auth-card`); partial `auth-brand-panel` |
| Admin | `components/layouts/admin.blade.php` | Mismo shell que operario (`.avicore-operario-*`): sidebar `lg+`, bottom nav móvil, home-nav en heroes; chrome `x-admin.sidebar-nav` / `bottom-nav` / `header` / `home-hero` / `page-hero`; tabs `AdminNav`. Estilos legacy `.avicore-admin-sidebar*` / toolbar retirados de `app.css` (post-auditoría 2026-07-16). |
| Operario | `components/layouts/operario-mobile.blade.php` | Shell responsive `.avicore-operario-shell` — **móvil:** header + `<x-operario.bottom-nav>`; **escritorio ≥1024px:** `<x-operario.sidebar-nav>` + contenido ancho; snackbar `context="operario"`; datos vía `OperarioLayoutComposer` / `OperarioNav` |

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
