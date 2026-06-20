# Referencia — Estructura del proyecto

**Fuente maestra del árbol de carpetas y convenciones de código.**  
Principios y stack: `docs/07-arquitectura-tecnica.md`.

---

## Repositorio actual

```text
avi-core/
├── app/                      # Laravel — Actions, Services, Livewire, Policies, Events
├── resources/views/          # layouts (público, admin, operario), components/ui
├── scripts/                  # dev.php (composer dev), optimize-brand-assets.py, check-agent-docs-sync.cjs
├── tests/Feature/            # Auth/, Services/, Ui/ (componentes x-ui), …
├── docs/                     # Documentación de producto + referencias
├── .cursor/                  # Reglas, skills, comando del arquitecto
├── AGENTS.md
└── …                         # Rutas, migraciones (estándar Laravel)
```

**Stack instalado (Bloque 1):** Laravel 13 · Livewire 4 · Tailwind 4 · PostgreSQL · Alpine (vía Livewire).

**Migraciones:** skeleton Laravel + `empresas` + `users` (esquema AviCore, índice único parcial documento admin). Resto de tablas operativas: pendientes (módulos 3+).

**Auth (Bloque 2):** Livewire `Auth/Login`, `Auth/ChangePassword`; middleware en `bootstrap/app.php`; rutas `/dev/*` solo en entorno `local`. Contacto de recuperación MVP: `config/avicore.php` + `SupportContactService` + `x-auth.support-contact-dialog`. Login demo local: `DemoLoginService` + selector de perfil en `/login` (solo `APP_ENV=local` + `AVICORE_DEMO_LOGIN`).

**Tests auth (Bloque 2):** `tests/Feature/Auth/LoginFlowTest.php`, `DemoLoginTest.php`; `tests/Feature/Services/DemoLoginServiceTest.php`; `tests/Feature/Ui/LoginViewTest.php` (render login y selector demo).

**Layout Livewire (oficial):** `resources/views/layouts/app.blade.php` — usado por componentes de página completa (`config/livewire.php` → `layouts::app`).

Reverb, Echo y PWA quedan para fases posteriores del plan.

---

## Estructura Laravel objetivo (`app/`)

```text
app/
├── Actions/
│   ├── Auth/                 # AttemptLoginAction, ChangePasswordAction
│   ├── Operacion/
│   ├── Lotes/
│   ├── Galpones/
│   └── Auditoria/
├── Http/
│   ├── Middleware/           # EnsurePasswordChanged, EnsureAdminPanelAccess, EnsureOperarioAccess, RedirectIfAuthenticated
│   └── View/
│       └── Composers/        # Inyección de datos a vistas Blade (p. ej. AdminHomeComposer → pages.admin.home)
├── Services/
│   ├── DashboardService.php
│   ├── AuditoriaService.php
│   ├── ReporteService.php
│   ├── EmpresaContextService.php
│   ├── AdminHomeService.php        # Datos Inicio admin (contexto, KPI usuarios, checklist MVP)
│   ├── DemoLoginService.php        # Login demo local (credencial única + rol → usuario seed)
│   └── SupportContactService.php   # URLs de soporte auth (config/avicore.php)
├── Support/
│   └── IconSvg.php                 # Carga SVG Lucide desde disco o fallback inline
├── Models/
│   ├── Empresa.php
│   ├── Granja.php
│   ├── Galpon.php
│   ├── Lote.php
│   ├── RegistroOperativo.php
│   └── Auditoria.php
├── Livewire/
│   ├── Auth/
│   ├── Dashboard/
│   ├── Operario/
│   ├── Galpones/
│   ├── Lotes/
│   ├── Reportes/
│   └── Usuarios/
├── Events/
│   ├── RegistroOperativoCreado.php
│   ├── RegistroAnulado.php
│   └── AlertaGenerada.php
└── Policies/
```

---

## `resources/` (objetivo)

```text
resources/
├── views/
│   ├── layouts/
│   │   └── app.blade.php     # layout Livewire (páginas completas)
│   ├── components/
│   │   ├── admin/             # home-hero (Inicio admin)
│   │   ├── auth/             # support-contact-dialog (recuperación MVP)
│   │   ├── layouts/          # público, admin, operario-móvil (Blade)
│   │   │   └── partials/     # admin-nav, admin-sidebar-inner, admin-header-toolbar, admin-menu-trigger, auth-brand-panel
│   │   └── ui/               # button, input, card, badge, alert, logo, icon, dialog, kpi-card, nav-link, empty-state, setup-checklist, user-avatar
│   │       └── icons/        # inline.blade.php — fallback SVG cuando no hay archivo Lucide
│   └── pages/
│       ├── admin/home.blade.php
│       ├── operario/home.blade.php
│       └── dev/              # previews /dev/* (solo local)
├── images/
│   ├── brand/             # fuente fondos JPEG (copia optimizada en public/)
│   └── icons/             # fuente SVG Lucide (referencia; render en x-ui.icon)
├── css/                      # Tailwind 4 + tema AviCore (`app.css`)
└── js/                       # Vite entry
```

---

## Convenciones por capa

| Capa | Ubicación | Responsabilidad |
|------|-----------|-----------------|
| Reglas de negocio | `Actions/`, `Services/` | Validaciones complejas, cálculos, anulaciones |
| Datos a vistas Blade estáticas | `Http/View/Composers/` | Inyección sin lógica en Blade (`Route::view`, p. ej. Inicio admin) |
| HTTP / UI dinámica | `Livewire/` | Estado de formularios, listados |
| Autorización | `Policies/` | Rol + `empresa_id` |
| Tiempo real | `Events/` + canales privados | Ver `docs/08-tiempo-real-eventos.md` |
| Persistencia | `Models/`, `database/migrations/` | Espejo de `reference/estructura-base-datos.md` |

---

## Módulos vs carpetas Livewire (mapa)

| Módulo MVP | Livewire (sugerido) | Tablas principales |
|------------|---------------------|-------------------|
| Login / contraseña | `Livewire/Auth/` (layout público) | `empresas`, `users` |
| Usuarios | `Usuarios/` | users |
| Empresas | Admin | empresas |
| Granjas | `Galpones/` o dedicado | granjas |
| Galpones | `Galpones/` | galpones |
| Lotes | `Lotes/` | lotes |
| Carga operario | `Operario/` | registros_operativos |
| Dashboard | `Dashboard/` | varias |
| Reportes | `Reportes/` | lectura agregada |

---

## Checklist al añadir código

- [ ] Clase en carpeta según tabla anterior
- [ ] `reference/estructura-proyecto.md` si nueva carpeta estándar
- [ ] Modelo alineado con `reference/estructura-base-datos.md`
- [ ] Policy y scope `empresa_id`
