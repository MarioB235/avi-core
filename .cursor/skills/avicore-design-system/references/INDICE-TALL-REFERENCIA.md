# Índice — Referencias TALL (auditoría local)

Repos clonados temporalmente en `docs/` para auditoría. **No se versionan** en AviCore; este archivo conserva patrones extraídos y enlaces upstream.

## Repos auditados

| Repo local (temporal) | Upstream | Patrones extraídos para AviCore |
|----------------------|----------|--------------------------------|
| `awesome-tall-stack` | [blade-ui-kit/awesome-tall-stack](https://github.com/blade-ui-kit/awesome-tall-stack) | Catálogo TALL; Blade UI Kit; talltips; no instalar paquetes listados |
| `dashboard` | [tailwindcomponents/dashboard-template](https://github.com/tailwindcomponents/dashboard-template) | Sidebar + área principal; tablas/forms Blade simples; estructura dashboard |
| `soft-ui-dashboard-tall` | [creativetimofficial/soft-ui-dashboard-tall](https://github.com/creativetimofficial/soft-ui-dashboard-tall) | Cards con relieve; nav responsive; jerarquía KPI; **descartar** paleta fuchsia/morada y `scale-102` |
| `tallstarter` | [mortenebak/tallstarter](https://github.com/mortenebak/tallstarter) | Layout admin/sidebar; tablas Livewire; densidad Flux como referencia visual — **no** instalar Flux |
| `wireblade` | [lianmaymesi/wireblade](https://github.com/lianmaymesi/wireblade) | Drawer sidebar Alpine; `x-transition`; nav items; touch targets — **no** instalar paquete |

## Rutas de ejemplo consultadas (auditoría 2026-06)

| Patrón | Repo | Ruta de referencia (antes de borrar clone) |
|--------|------|---------------------------------------------|
| Drawer sidebar + header sticky | wireblade | `resources/views/components/layouts/app.blade.php` |
| Nav con Alpine toggle | wireblade | `resources/views/components/partials/navigation.blade.php` |
| Nav guest responsive | soft-ui-dashboard-tall | `resources/views/layouts/navbars/guest/nav.blade.php` |
| Layout admin sidebar | tallstarter | `resources/views/components/layouts/app/sidebar.blade.php` |
| Tabla componentizada | tallstarter | `resources/views/components/table.blade.php` |
| Dashboard source | dashboard | `source/` (Jigsaw; solo estructura) |

## Dónde vive el contrato AviCore (post-extracción)

| Tema | Archivo en este skill |
|------|----------------------|
| Principios Refined Agro | [`refined-agro-principios.md`](refined-agro-principios.md) |
| Motion y feedback | [`motion-y-feedback.md`](motion-y-feedback.md) |
| Elevación y superficies | [`elevacion-y-superficies.md`](elevacion-y-superficies.md) |
| Snippets copiables | [`ejemplos-snippet.md`](ejemplos-snippet.md) |
| Tokens y componentes | [`tokens-componentes.md`](tokens-componentes.md) |
| Mobile operario | `avicore-ui/references/patrones-mobile-operario.md` |
| Web admin | `avicore-ui/references/patrones-web-admin.md` |

## Regla de uso

Inspiración → adaptar a tokens `avicore-*` y componentes `x-ui.*`. Nunca copiar CSS/paleta de terceros ni añadir dependencias composer/npm de UI kits.
