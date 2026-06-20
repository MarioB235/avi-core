# Referencia — Estructura del proyecto

**Fuente maestra del árbol de carpetas y convenciones de código.** Solo carpetas y clases que existen en el repo; módulos futuros en `docs/12-plan-de-desarrollo.md`.  
Principios y stack: `docs/07-arquitectura-tecnica.md`.

---

## Repositorio actual

```text
avi-core/
├── app/                      # Laravel — Actions, Services, Livewire, Policies
├── resources/views/          # layouts (público, admin, operario), components/ui
├── scripts/                  # dev.php (composer dev), optimize-brand-assets.py, check-agent-docs-sync.cjs
├── tests/Feature/            # Auth/, Operario/, Services/, Ui/
├── docs/                     # Documentación de producto + referencias
├── .cursor/                  # Reglas, skills, comando del arquitecto
├── AGENTS.md
└── …                         # Rutas, migraciones (estándar Laravel)
```

**Stack instalado (Bloque 1):** Laravel 13 · Livewire 4 · Tailwind 4 · PostgreSQL · Alpine (vía Livewire).

**Migraciones:** skeleton Laravel + `empresas` + `users` + estructura avícola (`granjas`, `galpones`, `lotes`, `registros_operativos`) + `users.ultimo_galpon_id`.

**Auth (Bloque 2):** Livewire `Auth/Login`, `Auth/ChangePassword`; middleware en `bootstrap/app.php`; rutas `/dev/*` solo en entorno `local`. Contacto de recuperación MVP: `config/avicore.php` + `SupportContactService` + `x-auth.support-contact-dialog`. Login demo local: `DemoLoginService` + selector de perfil en `/login` (solo `APP_ENV=local` + `AVICORE_DEMO_LOGIN`).

**Operario (slice mínimo):** Livewire `Operario/Home`, `Operario/SelectorGalpon`, `Operario/CargarHub`, `Operario/CargaHuevos`, `Operario/Historial`; rutas `/operario`, `/operario/galpon`, `/operario/cargar`, `/operario/carga/huevos`, `/operario/historial`; shell con `x-operario.bottom-nav`; `OperarioGalponService`, `RegistrarCargaHuevosAction`, `GalponPolicy`, `OperarioLayoutComposer`.

**Tests auth (Bloque 2):** `tests/Feature/Auth/LoginFlowTest.php`, `DemoLoginTest.php`; `tests/Feature/Services/DemoLoginServiceTest.php`; `tests/Feature/Ui/LoginViewTest.php` (render login y selector demo).

**Tests operario:** `tests/Feature/Operario/OperarioCargaHuevosTest.php` (flujo E2E, multiempresa, galpón no disponible, redirect sin galpón, Action rechaza mantenimiento), `tests/Feature/Ui/OperarioBottomNavTest.php` (dock, pestaña activa en home y en `/operario/carga/huevos`, hint de header sin galpón).

**Layout Livewire (oficial):** `resources/views/layouts/app.blade.php` — usado por componentes de página completa (`config/livewire.php` → `layouts::app`).

Reverb, Echo y PWA quedan para fases posteriores del plan.

---

## `app/` (estado actual)

```text
app/
├── Actions/
│   ├── Auth/                 # AttemptLoginAction, ChangePasswordAction
│   └── Operacion/            # RegistrarCargaHuevosAction
├── Enums/                    # EmpresaEstado, UserRole, GalponEstado, LoteEstado, TipoHuevo, RegistroOperativo*
├── Http/
│   ├── Middleware/           # EnsurePasswordChanged, EnsureAdminPanelAccess, EnsureOperarioAccess, RedirectIfAuthenticated
│   └── View/
│       └── Composers/        # AdminHomeComposer, OperarioLayoutComposer
├── Livewire/
│   ├── Auth/                 # Login, ChangePassword
│   └── Operario/             # Home, SelectorGalpon, CargarHub, CargaHuevos, Historial
├── Models/
│   ├── Concerns/             # BelongsToEmpresa
│   ├── Empresa.php
│   ├── Granja.php
│   ├── Galpon.php
│   ├── Lote.php
│   ├── RegistroOperativo.php
│   └── User.php
├── Policies/
│   └── GalponPolicy.php
├── Providers/
│   └── AppServiceProvider.php
├── Services/
│   ├── AdminHomeService.php
│   ├── DemoLoginService.php
│   ├── EmpresaContextService.php
│   ├── OperarioGalponService.php
│   └── SupportContactService.php
└── Support/
    └── IconSvg.php
```

---

## `resources/` (estado actual)

```text
resources/
├── views/
│   ├── layouts/
│   │   └── app.blade.php
│   ├── components/
│   │   ├── admin/            # home-hero
│   │   ├── auth/             # support-contact-dialog
│   │   ├── operario/         # bottom-nav
│   │   ├── layouts/          # público, admin, operario-mobile
│   │   │   └── partials/     # admin-nav, admin-sidebar-inner, admin-header-toolbar, admin-menu-trigger, auth-brand-panel
│   │   └── ui/               # button, input, card, badge, alert, logo, icon, dialog, kpi-card, nav-link, empty-state, setup-checklist, user-avatar
│   │       └── icons/        # inline.blade.php
│   ├── livewire/
│   │   ├── auth/             # login, change-password
│   │   └── operario/         # home, selector-galpon, cargar-hub, carga-huevos, historial
│   └── pages/
│       ├── admin/home.blade.php
│       └── dev/              # previews /dev/* (solo local)
├── images/
│   ├── brand/
│   └── icons/
├── css/                      # Tailwind 4 + tema AviCore (`app.css`)
└── js/
```

---

## Convenciones por capa

| Capa | Ubicación | Responsabilidad |
|------|-----------|-----------------|
| Reglas de negocio | `Actions/`, `Services/` | Validaciones complejas, cálculos, anulaciones |
| Datos a vistas Blade estáticas | `Http/View/Composers/` | Inyección sin lógica en Blade (`Route::view`, p. ej. Inicio admin) |
| HTTP / UI dinámica | `Livewire/` | Estado de formularios, listados |
| Autorización | `Policies/` | Rol + `empresa_id` |
| Tiempo real | `Events/` + canales privados | Ver `docs/08-tiempo-real-eventos.md` (cuando exista) |
| Persistencia | `Models/`, `database/migrations/` | Espejo de `reference/estructura-base-datos.md` |

---

## Módulos implementados (mapa)

| Módulo | Livewire / vista | Tablas principales |
|--------|------------------|-------------------|
| Login / contraseña | `Livewire/Auth/` | `empresas`, `users` |
| Inicio admin | `pages/admin/home` + `AdminHomeService` | `users` |
| Carga operario | `Livewire/Operario/` | `granjas`, `galpones`, `lotes`, `registros_operativos` |

Módulos pendientes (Dashboard, Reportes, CRUD usuarios, etc.): ver `docs/12-plan-de-desarrollo.md` § 13.

---

## Checklist al añadir código

- [ ] Clase en carpeta según convenciones de capa
- [ ] `reference/estructura-proyecto.md` si nueva carpeta estándar
- [ ] Modelo alineado con `reference/estructura-base-datos.md`
- [ ] Policy y scope `empresa_id`
