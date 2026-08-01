# PWA — AviCore (MVP)

Instalación en celular, manifest y aviso para instalar. **Sin offline completo** (sin cache de rutas Laravel ni datos operativos).

Stack: [`vite-plugin-pwa`](https://vite-pwa-org.netlify.app/) + Workbox (solo assets estáticos del build).

---

## Qué incluye el repo

| Pieza | Ubicación |
|-------|-----------|
| Plugin Vite + manifest | `vite.config.js` |
| Dependencias | `vite-plugin-pwa`, `workbox-window` (dev) |
| Registro service worker | `resources/js/pwa.js` |
| Meta manifest / Apple | `x-ui.pwa-meta` |
| Banner instalar | `x-ui.pwa-install-prompt` |
| Config | `config/avicore.php` → `pwa.enabled`, `pwa.install_prompt` |

Layouts con PWA: `public`, `operario-mobile`, `admin`, `layouts/app`.

---

## Variables de entorno

| Variable | Default | Efecto |
|----------|---------|--------|
| `AVICORE_PWA_ENABLED` | `true` | Manifest + service worker |
| `AVICORE_PWA_INSTALL_PROMPT` | `true` | Banner «Instalar» / guía iOS |

Desactivar banner (mantener PWA): `AVICORE_PWA_INSTALL_PROMPT=false`.

---

## Comportamiento del banner

- **Chrome / Edge (Android):** evento `beforeinstallprompt` → botón **Instalar**.
- **Safari (iOS):** texto «Compartir → Añadir a pantalla de inicio».
- No se muestra si la app ya está instalada (`display-mode: standalone`) o el usuario eligió **Ahora no** (`localStorage`: `avicore-pwa-install-dismissed`).
- Icono del banner: `pwa-192.png` (mismo que Apple touch / launcher).

---

## Manifest (resumen)

- `name` / `short_name`: AviCore
- `theme_color`: `#1f5e3b`
- `start_url`: `/login`
- Iconos dedicados en `public/images/brand/`:
  - `pwa-192.png` — launcher / Apple touch
  - `pwa-512.png` — splash / alta resolución
  - `pwa-512-maskable.png` — Android adaptive (logo al 80 % sobre fondo `#f5f7f4`)
- Regenerar tras cambiar logo: `python scripts/optimize-brand-assets.py`

Tras `pnpm run build`, el manifest queda en `public/build/manifest.webmanifest`.

---

## Service worker (alcance MVP)

- `registerType: autoUpdate` — actualiza SW al haber nueva versión del build.
- Precache: solo `js`, `css`, `woff2` del build.
- **Sin** `navigateFallback` — las rutas Livewire siguen requiriendo red.

No implementar cola offline de cargas operativas en este bloque.

---

## Verificación

1. `pnpm run build` sin error; existe `public/build/manifest.webmanifest`.
2. En móvil con HTTPS (local: túnel o Cloud): `/login` → banner o instalación nativa.
3. Instalada: abre en ventana propia, sin barra del navegador.
4. Flujo operario (`/operario`) y Livewire sin regresiones.

Laravel Cloud: añadir `AVICORE_PWA_*` en variables del entorno (ver [`deploy-laravel-cloud.md`](../../avicore-contexto/references/deploy-laravel-cloud.md)).

---

## Referencias

- [vite-plugin-pwa — guía](https://vite-pwa-org.netlify.app/guide/)
- [PWA minimal requirements](https://vite-pwa-org.netlify.app/guide/pwa-minimal-requirements.html)
