# PWA — AviCore (MVP)

Instalación en celular, manifest y aviso para instalar. **Sin offline completo** (sin cache de rutas Laravel ni datos operativos).

Stack: [`vite-plugin-pwa`](https://vite-pwa-org.netlify.app/) + Workbox (solo assets estáticos del build).

---

## Qué incluye el repo

| Pieza | Ubicación |
|-------|-----------|
| Plugin Vite + manifest | `vite.config.js` |
| Dependencias | `vite-plugin-pwa`, `workbox-window` (dev) |
| Captura `beforeinstallprompt` + API instalación | `resources/js/pwa-install.js` → `window.__avicorePwaInstall` |
| Registro service worker + aviso actualización | `resources/js/pwa.js` |
| Meta manifest / Apple | `x-ui.pwa-meta` |
| Banner instalar | `x-ui.pwa-install-prompt` (Alpine orquesta; lógica en `pwa-install.js`) |
| Menú cuenta «Instalar app» | `x-ui.user-menu` |
| Build metadata (Versión en Perfil) | `scripts/write-build-meta.cjs` → `public/build/avicore-build.json`; `App/Services/AppBuildService.php` |
| Config | `config/avicore.php` → `pwa.enabled`, `pwa.install_prompt` |

Layouts con PWA: `public`, `operario-mobile`, `admin`, `layouts/app`.

---

## Variables de entorno

| Variable | Default | Efecto |
|----------|---------|--------|
| `AVICORE_PWA_ENABLED` | `true` | Manifest + service worker |
| `AVICORE_PWA_INSTALL_PROMPT` | `true` | Banner «Instalar» / guía iOS (solo autenticado) |

Desactivar banner (mantener PWA): `AVICORE_PWA_INSTALL_PROMPT=false`.

---

## Comportamiento del banner

Sigue [MDN — Trigger install prompt](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/How_to/Trigger_install_prompt): captura global de `beforeinstallprompt` en `pwa-install.js` (antes de Alpine), `preventDefault()` + `prompt()` en gesto del usuario.

- **Chrome / Edge (Android):** evento capturado en `pwa-install.js` → botón **Instalar app** (solo si el navegador lo permite).
- **Safari (iOS):** sin `beforeinstallprompt` — guía «Compartir → Añadir a pantalla de inicio» tras 3 s.
- **Menú cuenta:** ítem **Instalar app** en móvil (todos los roles); Android abre diálogo nativo; iOS muestra snackbar con guía.
- **Solo autenticado** — tras iniciar sesión (operario, admin); no en `/login`.
- **Solo móvil** (`max-width: 768px` o user-agent móvil).
- **Retraso 3 s** antes de mostrar (no interrumpe el primer pantallazo).
- **Una vez por sesión de navegador:** «Ahora no» oculta hasta cerrar pestaña o volver a `/login` (limpia `sessionStorage`: `avicore-pwa-install-session-dismissed`).
- No se muestra si ya está instalada (`display-mode: standalone|fullscreen`, `navigator.standalone`).
- Al instalar: evento `appinstalled` oculta el banner.
- Android sin `beforeinstallprompt`: sin banner vacío; menú cuenta o menú ⋮ del navegador.

### API `window.__avicorePwaInstall` (`pwa-install.js`)

Fuente única de detección móvil/iOS/instalado y dismiss por sesión. El banner (`x-ui.pwa-install-prompt`) solo orquesta visibilidad y delay; no duplicar lógica en Alpine.

| Método / prop | Uso |
|---------------|-----|
| `hasPrompt()` | Hay evento `beforeinstallprompt` capturado |
| `shouldShowBanner()` | Móvil + no instalada + no dismiss en sesión |
| `shouldShowMenuItem()` | Ítem «Instalar app» en menú cuenta |
| `prompt()` / `offerInstall()` | Diálogo nativo o snackbar guía iOS |
| `getInstalledAt()` | ISO timestamp de `appinstalled` en `sessionStorage` (métrica local) |
| `dismissThisSession()` / `clearSessionDismiss()` | Dismiss «Ahora no» en `sessionStorage` |
| `clearLegacyDismissKeys()` | Limpia claves `localStorage` legacy al iniciar banner |

Eventos globales: `avicore:pwa-install-ready`, `avicore:pwa-installed`.

---

## Actualización tras deploy

- `registerType: autoUpdate` + `onNeedRefresh` en `pwa.js`.
- Snackbar persistente: «Hay una nueva versión» + botón **Actualizar** (recarga con nuevo SW).
- **Perfil (menú cuenta):** campo **Versión** con fecha/hora del último `pnpm run build` + commit corto (`public/build/avicore-build.json`, generado por `scripts/write-build-meta.cjs`).

---

## Manifest (resumen)

- `id`: `/` · `start_url`: `/` (redirige a operario/admin si hay sesión; login si no).
- `name` / `short_name`: AviCore
- `theme_color`: `#1f5e3b` · `orientation`: `portrait-primary` · `prefer_related_applications`: `false`
- `display_override`: `standalone`, `browser` · `handle_links`: `preferred` · `launch_handler.client_mode`: `navigate-existing`
- `categories`: business, productivity
- **Shortcuts:** Operario (`/operario`), Administración (`/admin`)
- **Screenshots:** `pwa-screenshot-narrow.jpg` (1080×1920, móvil) y `pwa-screenshot-wide.jpg` (1920×1080, escritorio) — generados con logo sobre gradiente de marca; mejoran el diálogo nativo de instalación Android (Chrome). **No** usar fotos decorativas de login: las dimensiones declaradas deben coincidir con el archivo real.
- Iconos dedicados en `public/images/brand/`:
  - `pwa-180.png` — Apple touch (`apple-touch-icon` 180×180)
  - `pwa-192.png` — launcher Android
  - `pwa-512.png` — splash / alta resolución
  - `pwa-512-maskable.png` — Android adaptive (logo al 80 % sobre fondo `#f5f7f4`)
- Regenerar tras cambiar logo: `python scripts/optimize-brand-assets.py` (iconos PWA + screenshots del manifest)

Meta iOS: `apple-mobile-web-app-status-bar-style=black-translucent`.

Tras `pnpm run build`, el manifest queda en `public/build/manifest.webmanifest`.

---

## Service worker (alcance MVP)

- `registerType: autoUpdate` — actualiza SW al haber nueva versión del build.
- Precache: solo `js`, `css`, `woff2` del build.
- Runtime cache: `/images/brand/*` con `StaleWhileRevalidate` (30 días, máx. 32 entradas).
- **Sin** `navigateFallback` — las rutas Livewire siguen requiriendo red.

No implementar cola offline de cargas operativas en este bloque.

---

## Verificación

1. `pnpm run build` sin error; existe `public/build/manifest.webmanifest`.
2. `pnpm run check:cloud-readiness` — valida `AVICORE_PWA_*`, iconos PWA y dimensiones de screenshots/iconos vs manifest.
3. En móvil con HTTPS (local: túnel o Cloud): login → home operario/admin → banner tras 3 s.
4. Menú cuenta → **Instalar app** visible en móvil si no está instalada.
5. Instalada: abre en ventana propia, sin barra del navegador; ícono abre `/` → home según rol.
6. Flujo operario (`/operario`) y Livewire sin regresiones.
7. Lighthouse PWA en staging (Chrome DevTools → Lighthouse → móvil) tras deploy.

**Pendiente MVP+:** screenshots reales de UI operario/admin; splash iOS (`apple-touch-startup-image`).

Laravel Cloud: añadir `AVICORE_PWA_*` en variables del entorno (ver [`deploy-laravel-cloud.md`](../../avicore-contexto/references/deploy-laravel-cloud.md)).

---

## Referencias

- [vite-plugin-pwa — guía](https://vite-pwa-org.netlify.app/guide/)
- [PWA minimal requirements](https://vite-pwa-org.netlify.app/guide/pwa-minimal-requirements.html)
