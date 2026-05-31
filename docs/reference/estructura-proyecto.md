# Referencia — Estructura del proyecto

**Fuente maestra del árbol de carpetas y convenciones de código.**  
Principios y stack: `docs/07-arquitectura-tecnica.md`.

---

## Repositorio actual

```text
avi-core/
├── app/                      # Laravel — Actions, Services, Livewire, Policies, Events
├── resources/views/          # layouts (público, admin, operario), components/ui
├── docs/                     # Documentación de producto + referencias
├── .cursor/                  # Reglas, skills, comando del arquitecto
├── AGENTS.md
└── …                         # Rutas, migraciones, tests (estándar Laravel)
```

**Stack instalado (Bloque 1):** Laravel 13 · Livewire 4 · Tailwind 4 · PostgreSQL · Alpine (vía Livewire).

**Migraciones:** skeleton Laravel + `empresas` + `users` (esquema AviCore, índice único parcial documento admin). Resto de tablas operativas: pendientes (módulos 3+).

**Auth (Bloque 2):** Livewire `Auth/Login`, `Auth/ChangePassword`; middleware en `bootstrap/app.php`; rutas `/dev/*` solo en entorno `local`.

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
│   └── Middleware/           # EnsurePasswordChanged, EnsureAdminPanelAccess, EnsureOperarioAccess, RedirectIfAuthenticated
├── Services/
│   ├── DashboardService.php
│   ├── AuditoriaService.php
│   ├── ReporteService.php
│   └── EmpresaContextService.php
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
│   │   ├── layouts/          # público, admin, operario-móvil (Blade)
│   │   └── ui/               # botón, input, card, badge, alert, logo
│   └── pages/                # home, previews /dev/*
├── css/                      # Tailwind 4 + tema AviCore (`app.css`)
└── js/                       # Vite entry
```

---

## Convenciones por capa

| Capa | Ubicación | Responsabilidad |
|------|-----------|-----------------|
| Reglas de negocio | `Actions/`, `Services/` | Validaciones complejas, cálculos, anulaciones |
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
