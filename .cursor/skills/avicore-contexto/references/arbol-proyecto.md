# Referencia — Estructura del proyecto

**Fuente maestra del árbol de carpetas y convenciones de código.** Solo carpetas y clases que existen en el repo; módulos futuros en `plan-desarrollo.md`.  
Principios y stack: `arquitectura.md`.

---

## Repositorio actual

```text
avi-core/
├── app/                      # Laravel — Actions, Services, Livewire, Policies
├── resources/views/          # layouts (público, admin, operario), components/ui
├── scripts/                  # dev.php, write-build-meta.cjs, serve-portal.cjs, check-agent-docs-sync, check-docs-impact, check-cloud-readiness, check-skill-references, …
├── tests/Feature/            # Auth/, Admin/, Operario/, Services/, Support/, Ui/
├── tests/Unit/Services/      # AppBuildServiceTest (metadata build)
├── portal/                   # Portal HTML documental (contenido, imprimibles, CHANGELOG, js/site.nav.js + site.theme.js + site.toc.js + site.js)
├── .cursor/                  # Reglas, skills, comando del arquitecto
├── AGENTS.md
├── pnpm-lock.yaml            # Lockfile front (pnpm)
└── …                         # Rutas, migraciones (estándar Laravel)
```

**Stack instalado (Bloque 1):** Laravel 13 · Livewire 4 · Tailwind 4 · PostgreSQL · Alpine (vía Livewire).

**Migraciones:** skeleton Laravel + `empresas` + `users` + estructura avícola (`granjas`, `galpones`, `lotes`, `registros_operativos`, `vacunaciones`) + `users.ultimo_galpon_id`.

**Auth (Bloque 2):** Livewire `Auth/Login`, `Auth/ChangePassword`; middleware en `bootstrap/app.php`; rutas `/dev/*` solo en entorno `local`. Contacto de recuperación MVP: `config/avicore.php` + `SupportContactService` + `x-auth.support-contact-dialog`. Login demo MVP: un usuario `000000000` (`AvicoreAuthSeeder` idempotente con `firstOrCreate`); el selector asigna rol al entrar (`DemoLoginService` + `executeDemo`).

**Operario (slice mínimo):** Livewire `Operario/Home`, `Operario/CargarHub`, `Operario/CargaHuevos`, `Operario/CargaMuertes`, `Operario/CargaVacunacion`, `Operario/CargaLote` (redirect-only), `Operario/Historial`; Concerns `ManagesGalponSelector`, `ManagesHuevosForm`, `ManagesMuertesForm`, `ManagesVacunacionForm`, `ManagesLoteForm`; rutas `/operario`, `/operario/cargar`, `/operario/carga/huevos`, `/operario/carga/muertes`, `/operario/carga/vacunacion`, `/operario/carga/lote`, `/operario/historial`; shell responsive con `x-operario.sidebar-nav` (escritorio), `x-operario.header`, `x-operario.user-menu`, `x-operario.home-hero`, `x-operario.primary-action`, `x-operario.cargar-hero`, `x-operario.historial-hero`, `x-operario.bottom-nav` (móvil), `x-ui.snackbar-host`, `x-ui.reveal` (secciones Inicio/Cargar), `x-ui.select`, `x-ui.date-picker` (filtro fecha Historial); `resources/js/scroll-reveal.js` + `operario-navigate.js` (scrim nav al scroll); carga huevos, muertes, vacunación y nuevo lote en hub vía `x-ui.dialog` (`partials/carga-huevos-form`, `partials/carga-muertes-form`, `partials/carga-vacunacion-form`, `partials/carga-lote-form`); selector galpón en Inicio/Cargar/Historial (`ManagesGalponSelector` + `partials/galpon-chip-selector`); `OperarioGalponService` (scoped; `galponActual` con revalidación, `galponDisponibleParaUsuario`, `seleccionarGalpon`, `galponesDisponibles`, `historialCargasQuery`, `historialPaginado` — SQL `UNION ALL` + paginación; `avicore-defer`: unificar count+página si crece), `OperarioGalponResumenService` (scoped; `resumen` sin memo de totales, `lotesActivos` con memo intra-request, `edadSemanas`, KPIs y acumulados por galpón con agregados SQL), `RegistrarCargaHuevosAction`, `RegistrarCargaMuertesAction`, `RegistrarVacunacionAction`, `RegistrarLoteAction`, `Support\OperarioHistorialItem`, `VacunaTipo`, `GalponPolicy`, `LotePolicy`, `OperarioLayoutComposer`, `Support\OperarioNav` (pestañas, iconos y títulos de header). `CargarHub::resolveGalponParaGuardar` revalida galpón con `galponDisponibleParaUsuario`. `EnsureOperarioAccess`: operario + dueño/administrativo/encargado.

**Tests auth (Bloque 2):** `tests/Feature/Auth/LoginFlowTest.php`, `DemoLoginTest.php` (roles demo, empresa inactiva, rate-limit `demoRole`, operario→`/operario`); `tests/Feature/Services/DemoLoginServiceTest.php` (flag, rol único, Admin AviCore sin empresa, empresa DEMO ausente); `tests/Feature/Ui/LoginViewTest.php` (render login, select perfil demo + campos vacíos/disabled); `tests/Feature/Ui/InputComponentTest.php` (toggle password disabled); `tests/Feature/Ui/PublicLayoutTest.php` (shell login móvil + panel marca desktop, logo `entrance`); `tests/Feature/Ui/LogoComponentTest.php` (variantes logo, órbita `entrance`); `tests/Feature/Ui/SelectComponentTest.php` (contrato `x-ui.select`, posicionamiento flip); `tests/Feature/Ui/PwaInstallPromptTest.php` (manifest + banner en login/operario/admin, split `enabled`/`install_prompt` en guest y autenticado, `shouldShowBanner`, `apple-touch-icon` + `pwa-180`).

**Tests admin:** `tests/Feature/Admin/AdminUsuariosTest.php` (CRUD multiempresa, permisos por rol — dueño/administrativo/encargado/operario/Admin AviCore, reset clave, toggle activo, guards rol admin y auto-desactivación); `tests/Feature/Services/AdminHomeServiceTest.php` (KPI usuarios activos, `setupItems`); `tests/Feature/Ui/AdminHomeViewTest.php` (Inicio gestión sin Campo, KPIs, checklist); `tests/Feature/Ui/AdminShellTest.php` (shell operario en admin, tabs Inicio·Usuarios, heroes); `tests/Feature/Ui/AdminUserMenuTest.php` (`x-ui.user-menu` en Inicio y Usuarios: portal, sidebar, home-nav, Versión con `avicore-build.json`).

**Tests operario:** `tests/Feature/Operario/OperarioCargaHuevosTest.php` (flujo E2E, multiempresa, galpón no disponible, redirect sin galpón y apertura automática del selector, Action rechaza mantenimiento), `tests/Feature/Operario/OperarioCargaMuertesTest.php` (flujo E2E muertes, descuento `aves_actuales`, rechazo si supera stock, Action multiempresa y mantenimiento, redirect `CargaMuertes` y `guardarMuertes` sin galpón disponible, query `form=muertes`), `tests/Feature/Operario/OperarioCargaVacunacionTest.php` (flujo E2E vacunación, validación lote/vacuna, Action multiempresa/galpón/lote, hub rechaza lote ajeno, redirect `CargaVacunacion` y `guardarVacunacion` sin galpón, query `form=vacunacion`), `tests/Feature/Operario/OperarioCargaLoteTest.php` (alta lote, codigo/secuencia, multi-tipo, gating operario, administrativo HTTP+registro, Action/policy, validación Livewire fecha/galpón, deep link `form=lote`), `tests/Feature/Operario/OperarioHomeTest.php` (`seleccionarGalpon` rechaza galpón ajeno, en mantenimiento o inactivo), `tests/Feature/Operario/OperarioHomeResumenTest.php` (KPIs galpón, lotes, acumulado, muertes, maples, edad vía service; `resumen` fresco tras nuevo registro; memo `lotesActivos`), `tests/Feature/Operario/OperarioHistorialTest.php` (tipos, vacunaciones mezcladas, filtro fecha validado con mensaje visible y sin acumulación, paginación, multiempresa, date-picker), `tests/Feature/Services/OperarioGalponServiceTest.php` (`galponDisponibleParaUsuario`, `historialCargasQuery`, `historialPaginado` con vacunaciones, multiempresa, selección, scoped ambos services operario), `tests/Feature/Support/OperarioNavTest.php` (pestaña activa y `headerTitle` por ruta, incl. `operario.historial` e icono `calendar`), `tests/Feature/Ui/OperarioBottomNavTest.php` (dock, transiciones ~150 ms, heroes Inicio/Cargar/Historial, tab activa y `aria-current`, icono `calendar` en Historial, date-picker en historial HTTP, ilustración `operario-reloj`, empty/populated historial HTTP, diálogos huevos/muertes/vacunación/lote vía deep link, hub sin deep link sin `avicore-dialog`, chip galpón vacío/activo, KPI maples), `tests/Feature/Ui/IllustrationComponentTest.php` (`operario-ave`, `operario-huevo`, `operario-reloj`, `operario-vacuna`), `tests/Feature/Ui/SelectComponentTest.php` (`x-ui.select` listbox), `tests/Feature/Ui/DatePickerComponentTest.php` (contrato `x-ui.date-picker`), `tests/Feature/Ui/OperarioUserMenuTest.php` (menú cuenta portal/clamp en home/cargar/historial, ARIA, perfil, logout, Versión), `tests/Feature/Ui/DialogComponentTest.php`, `tests/Feature/Ui/SheetComponentTest.php` (diálogo huevos en `CargarHub`), `tests/Feature/Ui/SnackbarHostTest.php` (host en layout, evento `snackbar-show`, flash `status`, `syncProgressDuration`, pause/Escape/`runAction`, contrato desktop `right-6`/`bottom-6`, contrato `pwa.js`: `immediate: false`, `onNeedRefresh`, `pwa-update`, `__avicorePwaUpdate`), `tests/Feature/Ui/RevealComponentTest.php` (`x-ui.reveal`, `data-reveal-delay`, sin inline), `tests/Feature/Ui/ScrollRevealTest.php` (wiring `scroll-reveal.js`, markers HTTP Inicio/Cargar, edge fade shell).

**Tests unitarios:** `tests/Unit/Services/AppBuildServiceTest.php` (`metadata` null/inválido, `labelForProfile` con y sin commit, hint local).

**PWA (MVP instalable, sin offline completo):** `vite-plugin-pwa` + `resources/js/pwa.js` (registro SW, snackbar actualización) + `resources/js/pwa-install.js` (`__avicorePwaInstall`); manifest en `vite.config.js` → `public/build/manifest.webmanifest` tras `pnpm run build`; `scripts/write-build-meta.cjs` → `public/build/avicore-build.json`; `App/Services/AppBuildService` (Versión en Perfil); `config/avicore.php` → `pwa.enabled`, `pwa.install_prompt` (`AVICORE_PWA_*`); `x-ui.pwa-meta`, `x-ui.pwa-install-prompt` en layouts público, operario, admin y `layouts/app`; iconos `public/images/brand/pwa-*.png` y screenshots `pwa-screenshot-*.jpg` vía `scripts/optimize-brand-assets.py`; `scripts/check-cloud-readiness.cjs` valida assets y dimensiones vs manifest. Contrato: `avicore-pwa/references/pwa.md`.

Reverb y Echo quedan para fases posteriores del plan.

---

## `app/` (estado actual)

```text
app/
├── Actions/
│   ├── Auth/                 # AttemptLoginAction, ChangePasswordAction
│   ├── Lote/                 # RegistrarLoteAction
│   ├── Operacion/            # RegistrarCargaHuevosAction, RegistrarCargaMuertesAction, RegistrarVacunacionAction
│   └── User/                 # CreateUserAction, UpdateUserAction, ResetUserPasswordAction
├── Enums/                    # EmpresaEstado, UserRole, GalponEstado, LoteEstado, TipoHuevo, VacunaTipo, RegistroOperativo*
├── Http/
│   ├── Middleware/           # EnsurePasswordChanged, EnsureAdminPanelAccess, EnsureOperarioAccess, RedirectIfAuthenticated
│   └── View/
│       └── Composers/        # AdminHomeComposer, AdminLayoutComposer, OperarioLayoutComposer
├── Livewire/
│   ├── Admin/
│   │   └── Usuarios/         # Index (CRUD listado/alta/edición/reset)
│   ├── Auth/                 # Login, ChangePassword
│   └── Operario/             # Home, CargarHub (+ Concerns/: ManagesGalponSelector, ManagesHuevosForm, …), CargaHuevos, CargaMuertes, CargaVacunacion, CargaLote, Historial
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
│   ├── LotePolicy.php
│   └── UserPolicy.php
├── Providers/
│   └── AppServiceProvider.php
├── Services/
│   ├── AppBuildService.php   # metadata build (Versión en menú cuenta)
│   ├── DemoLoginService.php
│   ├── AdminHomeService.php
│   ├── EmpresaContextService.php
│   ├── OperarioGalponService.php
│   ├── OperarioGalponResumenService.php
│   ├── SupportContactService.php
│   └── TemporaryPasswordGenerator.php
└── Support/
    ├── IconSvg.php
    ├── IllustrationSvg.php      # Ilustraciones KPI operario (SVG en resources/images/illustrations/)
    ├── AdminNav.php             # Pestañas y títulos del shell admin (paridad operario)
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
│   │   ├── admin/            # sidebar-nav, bottom-nav, header, home-hero, page-hero
│   │   ├── auth/             # support-contact-dialog
│   │   ├── operario/         # bottom-nav, sidebar-nav, header, user-menu, home-hero, primary-action, cargar-hero, historial-hero
│   │   ├── layouts/          # público, admin (shell tipo operario), operario-mobile
│   │   │   └── partials/     # auth-brand-panel
│   │   └── ui/               # button, input, select, date-picker, card, badge, alert, logo, icon, illustration, dialog, sheet, kpi-card, nav-link, empty-state, setup-checklist, user-avatar, snackbar-host, reveal, user-menu, pwa-meta, pwa-install-prompt
│   │       └── icons/        # inline.blade.php
│   ├── livewire/
│   │   ├── _redirect-placeholder.blade.php
│   │   ├── auth/             # login, change-password
│   │   ├── admin/            # usuarios/index
│   │   └── operario/         # home (+ partials/galpon-chip-selector, carga-huevos-form, carga-muertes-form, carga-vacunacion-form, carga-lote-form), cargar-hub, historial
│   └── pages/
│       ├── admin/home.blade.php
│       └── dev/              # previews /dev/* (solo local)
├── images/
│   ├── brand/
│   ├── icons/                  # Lucide file-backed (p. ej. bird.svg)
│   └── illustrations/          # operario-ave, operario-huevo, operario-reloj, operario-vacuna
├── css/                      # Tailwind 4 + tema AviCore (`app.css`, `operario.css`)
└── js/                       # `app.js`, `scroll-reveal.js`, `pwa-install.js` (`__avicorePwaInstall`), `pwa.js` (SW + snackbar update), `operario-navigate.js`
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
| Usuarios admin | `Livewire/Admin/Usuarios/` + `Actions/User/` + `UserPolicy` | `users`, `empresas` |
| Carga operario | `Livewire/Operario/` | `granjas`, `galpones`, `lotes`, `registros_operativos`, `vacunaciones` |

Módulos pendientes (Dashboard, Reportes, CRUD estructura, etc.): ver `plan-desarrollo.md` § 13.

---

## Checklist al añadir código

- [ ] Clase en carpeta según convenciones de capa
- [ ] `arbol-proyecto.md` si nueva carpeta estándar
- [ ] Modelo alineado con `avicore-modelo-datos/references/esquema-bd.md`
- [ ] Policy y scope `empresa_id`
