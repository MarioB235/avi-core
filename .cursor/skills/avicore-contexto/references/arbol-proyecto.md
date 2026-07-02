# Referencia — Estructura del proyecto

**Fuente maestra del árbol de carpetas y convenciones de código.** Solo carpetas y clases que existen en el repo; módulos futuros en `plan-desarrollo.md`.  
Principios y stack: `arquitectura.md`.

---

## Repositorio actual

```text
avi-core/
├── app/                      # Laravel — Actions, Services, Livewire, Policies
├── resources/views/          # layouts (público, admin, operario), components/ui
├── scripts/                  # dev.php (composer dev), optimize-brand-assets.py, check-agent-docs-sync.cjs
├── tests/Feature/            # Auth/, Operario/, Services/, Support/, Ui/
├── docs/                     # Documentación de producto + referencias
├── .cursor/                  # Reglas, skills, comando del arquitecto
├── AGENTS.md
├── pnpm-lock.yaml            # Lockfile front (pnpm)
└── …                         # Rutas, migraciones (estándar Laravel)
```

**Stack instalado (Bloque 1):** Laravel 13 · Livewire 4 · Tailwind 4 · PostgreSQL · Alpine (vía Livewire).

**Migraciones:** skeleton Laravel + `empresas` + `users` + estructura avícola (`granjas`, `galpones`, `lotes`, `registros_operativos`) + `users.ultimo_galpon_id`.

**Auth (Bloque 2):** Livewire `Auth/Login`, `Auth/ChangePassword`; middleware en `bootstrap/app.php`; rutas `/dev/*` solo en entorno `local`. Contacto de recuperación MVP: `config/avicore.php` + `SupportContactService` + `x-auth.support-contact-dialog`. Login demo local: `DemoLoginService` + selector de perfil en `/login` (solo `APP_ENV=local` + `AVICORE_DEMO_LOGIN`).

**Operario (slice mínimo):** Livewire `Operario/Home`, `Operario/CargarHub`, `Operario/CargaHuevos`, `Operario/CargaMuertes` (redirect-only), `Operario/Historial`; rutas `/operario`, `/operario/cargar`, `/operario/carga/huevos`, `/operario/carga/muertes`, `/operario/historial`; shell con `x-operario.header`, `x-operario.user-menu`, `x-operario.home-hero`, `x-operario.primary-action`, `x-operario.cargar-hero`, `x-operario.historial-hero`, `x-operario.bottom-nav`, `x-ui.snackbar-host`; carga huevos y muertes en hub vía `x-ui.dialog` (`partials/carga-huevos-form`, `partials/carga-muertes-form`); selector galpón en `Home` + `partials/galpon-chip-selector`; `OperarioGalponService` (`galponDisponibleParaUsuario`, `galponActual`, `seleccionarGalpon`, `galponesDisponibles`, `historialCargasQuery`), `OperarioGalponResumenService` (`resumen`, `edadSemanas`, KPIs y acumulados por galpón), `RegistrarCargaHuevosAction`, `RegistrarCargaMuertesAction`, `GalponPolicy`, `OperarioLayoutComposer`, `Support\OperarioNav` (pestañas, iconos y títulos de header).

**Tests auth (Bloque 2):** `tests/Feature/Auth/LoginFlowTest.php`, `DemoLoginTest.php`; `tests/Feature/Services/DemoLoginServiceTest.php`; `tests/Feature/Ui/LoginViewTest.php` (render login y selector demo); `tests/Feature/Ui/PublicLayoutTest.php` (shell login móvil + panel marca desktop, logo `entrance`); `tests/Feature/Ui/LogoComponentTest.php` (variantes logo, órbita `entrance`).

**Tests operario:** `tests/Feature/Operario/OperarioCargaHuevosTest.php` (flujo E2E, multiempresa, galpón no disponible, redirect sin galpón y apertura automática del selector, Action rechaza mantenimiento), `tests/Feature/Operario/OperarioCargaMuertesTest.php` (flujo E2E muertes, descuento `aves_actuales`, rechazo si supera stock, Action multiempresa y mantenimiento, redirect `CargaMuertes` y `guardarMuertes` sin galpón disponible, query `form=muertes`), `tests/Feature/Operario/OperarioHomeTest.php` (`seleccionarGalpon` rechaza galpón ajeno, en mantenimiento o inactivo), `tests/Feature/Operario/OperarioHomeResumenTest.php` (KPIs galpón, lotes, acumulado, muertes, maples, edad vía service), `tests/Feature/Operario/OperarioHistorialTest.php` (tipos, filtro fecha validado, paginación, multiempresa), `tests/Feature/Services/OperarioGalponServiceTest.php` (`galponDisponibleParaUsuario`, `historialCargasQuery`, multiempresa, selección), `tests/Feature/Support/OperarioNavTest.php` (pestaña activa y `headerTitle` por ruta, incl. `operario.historial` e icono `calendar`), `tests/Feature/Ui/OperarioBottomNavTest.php` (dock, transiciones, heroes Inicio/Cargar/Historial, tab activa y `aria-current`, icono `calendar` en Historial, ilustración `operario-reloj` en historial HTTP, empty/populated historial HTTP, diálogos huevos/muertes vía deep link, chip galpón vacío/activo, KPI maples), `tests/Feature/Ui/IllustrationComponentTest.php` (`operario-ave`, `operario-huevo`, `operario-reloj`), `tests/Feature/Ui/OperarioUserMenuTest.php` (menú cuenta en home/cargar/historial, ARIA, perfil, logout), `tests/Feature/Ui/DialogComponentTest.php`, `tests/Feature/Ui/SheetComponentTest.php` (diálogo huevos en `CargarHub`), `tests/Feature/Ui/SnackbarHostTest.php` (host en layout, evento `snackbar-show`, flash `status`).

**Layout Livewire (oficial):** `resources/views/layouts/app.blade.php` — usado por componentes de página completa (`config/livewire.php` → `layouts::app`).

Reverb, Echo y PWA quedan para fases posteriores del plan.

---

## `app/` (estado actual)

```text
app/
├── Actions/
│   ├── Auth/                 # AttemptLoginAction, ChangePasswordAction
│   └── Operacion/            # RegistrarCargaHuevosAction, RegistrarCargaMuertesAction
├── Enums/                    # EmpresaEstado, UserRole, GalponEstado, LoteEstado, TipoHuevo, RegistroOperativo*
├── Http/
│   ├── Middleware/           # EnsurePasswordChanged, EnsureAdminPanelAccess, EnsureOperarioAccess, RedirectIfAuthenticated
│   └── View/
│       └── Composers/        # AdminHomeComposer, OperarioLayoutComposer
├── Livewire/
│   ├── Auth/                 # Login, ChangePassword
│   └── Operario/             # Home, CargarHub, CargaHuevos, CargaMuertes, Historial
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
│   ├── OperarioGalponResumenService.php
│   └── SupportContactService.php
└── Support/
    ├── IconSvg.php
    ├── IllustrationSvg.php      # Ilustraciones KPI operario (SVG en resources/images/illustrations/)
    └── OperarioNav.php          # Pestañas y títulos del shell operario
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
│   │   ├── operario/         # bottom-nav, header, user-menu, home-hero, primary-action, cargar-hero, historial-hero
│   │   ├── layouts/          # público, admin, operario-mobile
│   │   │   └── partials/     # admin-nav, admin-sidebar-inner, admin-header-toolbar, admin-menu-trigger, auth-brand-panel
│   │   └── ui/               # button, input, card, badge, alert, logo, icon, illustration, dialog, sheet, kpi-card, nav-link, empty-state, setup-checklist, user-avatar, snackbar-host
│   │       └── icons/        # inline.blade.php
│   ├── livewire/
│   │   ├── _redirect-placeholder.blade.php
│   │   ├── auth/             # login, change-password
│   │   └── operario/         # home (+ partials/galpon-chip-selector, carga-huevos-form, carga-muertes-form), cargar-hub, historial
│   └── pages/
│       ├── admin/home.blade.php
│       └── dev/              # previews /dev/* (solo local)
├── images/
│   ├── brand/
│   ├── icons/                  # Lucide file-backed (p. ej. bird.svg)
│   └── illustrations/          # operario-ave, operario-huevo, operario-reloj
├── css/                      # Tailwind 4 + tema AviCore (`app.css`, `operario.css`)
└── js/                       # `app.js`, `operario-navigate.js` (clase navigating en shell)
```

---

## Convenciones por capa

| Capa | Ubicación | Responsabilidad |
|------|-----------|-----------------|
| Reglas de negocio | `Actions/`, `Services/` | Validaciones complejas, cálculos, anulaciones |
| Datos a vistas Blade estáticas | `Http/View/Composers/` | Inyección sin lógica en Blade (`Route::view`, p. ej. Inicio admin) |
| HTTP / UI dinámica | `Livewire/` | Estado de formularios, listados |
| Autorización | `Policies/` | Rol + `empresa_id` |
| Tiempo real | `Events/` + canales privados | Ver `eventos.md` (cuando exista) |
| Persistencia | `Models/`, `database/migrations/` | Espejo de `avicore-modelo-datos/references/esquema-bd.md` |

---

## Módulos implementados (mapa)

| Módulo | Livewire / vista | Tablas principales |
|--------|------------------|-------------------|
| Login / contraseña | `Livewire/Auth/` | `empresas`, `users` |
| Inicio admin | `pages/admin/home` + `AdminHomeService` | `users` |
| Carga operario | `Livewire/Operario/` | `granjas`, `galpones`, `lotes`, `registros_operativos` |

Módulos pendientes (Dashboard, Reportes, CRUD usuarios, etc.): ver `plan-desarrollo.md` § 13.

---

## Checklist al añadir código

- [ ] Clase en carpeta según convenciones de capa
- [ ] `arbol-proyecto.md` si nueva carpeta estándar
- [ ] Modelo alineado con `avicore-modelo-datos/references/esquema-bd.md`
- [ ] Policy y scope `empresa_id`
