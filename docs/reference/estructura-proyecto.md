# Referencia — Estructura del proyecto

**Fuente maestra del árbol de carpetas y convenciones de código.**  
Principios y stack: `docs/07-arquitectura-tecnica.md`.

---

## Repositorio actual (documentación + Cursor)

```text
avi-core/
├── AGENTS.md                 # Entrada Cursor/CLI (enlaza a docs/00-contexto.md)
├── docs/
│   ├── README.md             # Índice y gobernanza
│   ├── 00-contexto.md        # Contexto del proyecto
│   ├── CHANGELOG.md          # Cambios de contrato documental
│   ├── 01-producto-avicore.md … 12-plan-de-desarrollo.md
│   ├── reference/
│   │   ├── estructura-base-datos.md
│   │   └── estructura-proyecto.md   # (este archivo)
│   └── cursor/               # Tooling agente (no negocio)
├── .cursor/
│   ├── rules/                # Reglas .mdc
│   ├── skills/               # Skills por escenario
│   └── commands/             # Slash commands
├── app/                      # (futuro) Laravel
├── database/
├── resources/
├── routes/
└── tests/
```

---

## Estructura Laravel objetivo (`app/`)

```text
app/
├── Actions/
│   ├── Operacion/
│   ├── Lotes/
│   ├── Galpones/
│   └── Auditoria/
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
│   ├── layouts/          # público, admin, operario-móvil
│   ├── livewire/
│   └── components/
├── css/
└── js/                   # Echo, PWA
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
| Login / contraseña | Auth (layout público) | users |
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
