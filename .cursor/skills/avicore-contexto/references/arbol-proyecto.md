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

**Migraciones:** skeleton Laravel + `empresas` + `users` + estructura avícola (`granjas`, `galpones`, `lotes`, `registros_operativos`, `vacunaciones`) + `users.ultimo_galpon_id`.

**Auth (Bloque 2):** Livewire `Auth/Login`, `Auth/ChangePassword`; middleware en `bootstrap/app.php`; rutas `/dev/*` solo en entorno `local`. Contacto de recuperación MVP: `config/avicore.php` + `SupportContactService` + `x-auth.support-contact-dialog`. Login demo local: `DemoLoginService` + `x-ui.select` «Perfil demo» en `/login` (solo `APP_ENV=local` + `AVICORE_DEMO_LOGIN`).

**Operario (slice mínimo):** Livewire `Operario/Home`, `Operario/CargarHub`, `Operario/CargaHuevos`, `Operario/CargaMuertes`, `Operario/CargaVacunacion`, `Operario/CargaLote` (redirect-only), `Operario/Historial`; rutas `/operario`, `/operario/cargar`, `/operario/carga/huevos`, `/operario/carga/muertes`, `/operario/carga/vacunacion`, `/operario/carga/lote`, `/operario/historial`; shell con `x-operario.header`, `x-operario.user-menu`, `x-operario.home-hero`, `x-operario.primary-action`, `x-operario.cargar-hero`, `x-operario.historial-hero`, `x-operario.bottom-nav`, `x-ui.snackbar-host`, `x-ui.select`; carga huevos, muertes, vacunación y nuevo lote en hub vía `x-ui.dialog` (`partials/carga-huevos-form`, `partials/carga-muertes-form`, `partials/carga-vacunacion-form`, `partials/carga-lote-form`); selector galpón en `Home` + `partials/galpon-chip-selector`; `OperarioGalponService` (`galponDisponibleParaUsuario`, `galponActual`, `seleccionarGalpon`, `galponesDisponibles`, `historialCargasQuery`, `historialPaginado`), `OperarioGalponResumenService` (`resumen`, `edadSemanas`, `lotesActivos`, KPIs y acumulados por galpón), `RegistrarCargaHuevosAction`, `RegistrarCargaMuertesAction`, `RegistrarVacunacionAction`, `RegistrarLoteAction`, `Support\OperarioHistorialItem`, `VacunaTipo`, `GalponPolicy`, `LotePolicy`, `OperarioLayoutComposer`, `Support\OperarioNav` (pestañas, iconos y títulos de header). `EnsureOperarioAccess`: operario + dueño/administrativo/encargado.

**Tests auth (Bloque 2):** `tests/Feature/Auth/LoginFlowTest.php`, `DemoLoginTest.php`; `tests/Feature/Services/DemoLoginServiceTest.php`; `tests/Feature/Ui/LoginViewTest.php` (render login, demo `x-ui.select` + listbox); `tests/Feature/Ui/PublicLayoutTest.php` (shell login móvil + panel marca desktop, logo `entrance`); `tests/Feature/Ui/LogoComponentTest.php` (variantes logo, órbita `entrance`); `tests/Feature/Ui/SelectComponentTest.php` (contrato `x-ui.select`, posicionamiento flip).

**Tests operario:** `tests/Feature/Operario/OperarioCargaHuevosTest.php` (flujo E2E, multiempresa, galpón no disponible, redirect sin galpón y apertura automática del selector, Action rechaza mantenimiento), `tests/Feature/Operario/OperarioCargaMuertesTest.php` (flujo E2E muertes, descuento `aves_actuales`, rechazo si supera stock, Action multiempresa y mantenimiento, redirect `CargaMuertes` y `guardarMuertes` sin galpón disponible, query `form=muertes`), `tests/Feature/Operario/OperarioCargaVacunacionTest.php` (flujo E2E vacunación, validación lote/vacuna, Action multiempresa/galpón/lote, hub rechaza lote ajeno, redirect `CargaVacunacion` y `guardarVacunacion` sin galpón, query `form=vacunacion`), `tests/Feature/Operario/OperarioCargaLoteTest.php` (alta lote, codigo/secuencia, multi-tipo, gating operario, administrativo HTTP+registro, Action/policy, validación Livewire fecha/galpón, deep link `form=lote`), `tests/Feature/Operario/OperarioHomeTest.php` (`seleccionarGalpon` rechaza galpón ajeno, en mantenimiento o inactivo), `tests/Feature/Operario/OperarioHomeResumenTest.php` (KPIs galpón, lotes, acumulado, muertes, maples, edad vía service), `tests/Feature/Operario/OperarioHistorialTest.php` (tipos, vacunaciones mezcladas, filtro fecha validado, paginación, multiempresa), `tests/Feature/Services/OperarioGalponServiceTest.php` (`galponDisponibleParaUsuario`, `historialCargasQuery`, `historialPaginado` con vacunaciones, multiempresa, selección), `tests/Feature/Support/OperarioNavTest.php` (pestaña activa y `headerTitle` por ruta, incl. `operario.historial` e icono `calendar`), `tests/Feature/Ui/OperarioBottomNavTest.php` (dock, transiciones, heroes Inicio/Cargar/Historial, tab activa y `aria-current`, icono `calendar` en Historial, ilustración `operario-reloj` en historial HTTP, empty/populated historial HTTP, diálogos huevos/muertes/vacunación/lote vía deep link, chip galpón vacío/activo, KPI maples), `tests/Feature/Ui/IllustrationComponentTest.php` (`operario-ave`, `operario-huevo`, `operario-reloj`, `operario-vacuna`), `tests/Feature/Ui/SelectComponentTest.php` (`x-ui.select` listbox), `tests/Feature/Ui/OperarioUserMenuTest.php` (menú cuenta en home/cargar/historial, ARIA, perfil, logout), `tests/Feature/Ui/DialogComponentTest.php`, `tests/Feature/Ui/SheetComponentTest.php` (diálogo huevos en `CargarHub`), `tests/Feature/Ui/SnackbarHostTest.php` (host en layout, evento `snackbar-show`, flash `status`).

**Layout Livewire (oficial):** `resources/views/layouts/app.blade.php` — usado por componentes de página completa (`config/livewire.php` → `layouts::app`).

Reverb, Echo y PWA quedan para fases posteriores del plan.

---

## `app/` (estado actual)

```text
app/
├── Actions/
│   ├── Auth/                 # AttemptLoginAction, ChangePasswordAction
│   ├── Lote/                 # RegistrarLoteAction
│   └── Operacion/            # RegistrarCargaHuevosAction, RegistrarCargaMuertesAction, RegistrarVacunacionAction
├── Enums/                    # EmpresaEstado, UserRole, GalponEstado, LoteEstado, TipoHuevo, VacunaTipo, RegistroOperativo*
├── Http/
│   ├── Middleware/           # EnsurePasswordChanged, EnsureAdminPanelAccess, EnsureOperarioAccess, RedirectIfAuthenticated
│   └── View/
│       └── Composers/        # AdminHomeComposer, OperarioLayoutComposer
├── Livewire/
│   ├── Auth/                 # Login, ChangePassword
│   └── Operario/             # Home, CargarHub, CargaHuevos, CargaMuertes, CargaVacunacion, CargaLote, Historial
├── Models/
│   ├── Concerns/             # BelongsToEmpresa
│   ├── Empresa.php
│   ├── Granja.php
│   ├── Galpon.php
│   ├── Lote.php
│   ├── RegistroOperativo.php
│   ├── Vacunacion.php
│   └── User.php
├── Policies/
│   ├── GalponPolicy.php
│   └── LotePolicy.php
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
    ├── OperarioHistorialItem.php
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
│   │   └── ui/               # button, input, select, card, badge, alert, logo, icon, illustration, dialog, sheet, kpi-card, nav-link, empty-state, setup-checklist, user-avatar, snackbar-host
│   │       └── icons/        # inline.blade.php
│   ├── livewire/
│   │   ├── _redirect-placeholder.blade.php
│   │   ├── auth/             # login, change-password
│   │   └── operario/         # home (+ partials/galpon-chip-selector, carga-huevos-form, carga-muertes-form, carga-vacunacion-form), cargar-hub, historial
│   └── pages/
│       ├── admin/home.blade.php
│       └── dev/              # previews /dev/* (solo local)
├── images/
│   ├── brand/
│   ├── icons/                  # Lucide file-backed (p. ej. bird.svg)
│   └── illustrations/          # operario-ave, operario-huevo, operario-reloj, operario-vacuna
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
| Carga operario | `Livewire/Operario/` | `granjas`, `galpones`, `lotes`, `registros_operativos`, `vacunaciones` |

Módulos pendientes (Dashboard, Reportes, CRUD usuarios, etc.): ver `plan-desarrollo.md` § 13.

---

## Checklist al añadir código

- [ ] Clase en carpeta según convenciones de capa
- [ ] `arbol-proyecto.md` si nueva carpeta estándar
- [ ] Modelo alineado con `avicore-modelo-datos/references/esquema-bd.md`
- [ ] Policy y scope `empresa_id`
