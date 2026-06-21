# 12 — Plan de desarrollo

## 0. Estado de avance (2026-06-20)

| Bloque | Estado | Notas |
|--------|--------|-------|
| **1 — Base** | **Hecho** | Laravel 13, Livewire 4, Tailwind 4, layouts, UI base, PostgreSQL + `migrate` OK |
| **2 — Seguridad** | **En curso** | Login + cambio obligatorio implementados; roles/CRUD usuarios pendiente |
| **4 — Estructura avícola** | **Parcial** | Migraciones + seeder demo mínimo (1 granja, 2 galpones, 1 lote); sin CRUD admin |
| **5 — Operación móvil** | **Parcial** | Home operario, selector galpón, carga huevos; muertes/alimento/combinada pendientes |
| 3, 6–7 | Pendiente | Según orden de la sección 2 |

Detalle técnico del Bloque 1: [`arbol-proyecto.md`](arbol-proyecto.md) · entorno local: [`arranque-local.md`](arranque-local.md).

---

## 1. Estrategia

Desarrollar módulo por módulo.

No construir todas las pantallas antes del backend.

### Fórmula

```text
Interfaz del módulo → backend → validaciones → permisos → auditoría → prueba → siguiente módulo
```

---

## 2. Orden recomendado

| Fase | Módulo |
|---|---|
| 1 | Base del proyecto |
| 2 | Identidad visual |
| 3 | Login |
| 4 | Cambio obligatorio de contraseña |
| 5 | Usuarios y roles |
| 6 | Multiempresa |
| 7 | Empresas |
| 8 | Granjas |
| 9 | Galpones |
| 10 | Lotes |
| 11 | Vista móvil operario |
| 12 | Carga de huevos |
| 13 | Carga de muertes |
| 14 | Carga de alimento |
| 15 | Carga combinada |
| 16 | Anulación y auditoría |
| 17 | Dashboard |
| 18 | Tiempo real |
| 19 | Reportes |
| 20 | Datos demo |
| 21 | PWA |

---

## 3. Primer módulo

Comenzar con:

```text
Login + cambio obligatorio de contraseña
```

Motivo:

- Es pequeño.
- Es necesario.
- Define estilo visual.
- Permite crear layout público.
- Permite validar seguridad.

---

## 4. Primer recorrido funcional mínimo

```text
Admin AviCore crea empresa demo
        ↓
Admin empresa crea usuario operario
        ↓
Operario entra con documento y contraseña temporal
        ↓
Operario cambia contraseña
        ↓
Operario ingresa a vista móvil
        ↓
Operario selecciona galpón
        ↓
Operario carga huevos
        ↓
Dashboard refleja la carga
```

---

## 5. Bloque 1 — Base ✅

- [x] Crear proyecto Laravel en el repo.
- [x] Configurar PostgreSQL (base `avicore`, `.env`).
- [x] Configurar Tailwind 4 + Vite.
- [x] Configurar Livewire 4 (+ Alpine vía Livewire).
- [x] Crear layouts base (público, admin, operario + `layouts/app` para Livewire).
- [x] Crear componentes UI básicos (botón, input, card, badge, alert).
- [x] Migraciones iniciales Laravel (`php artisan migrate`).

---

## 6. Bloque 2 — Seguridad

- [x] Login (documento + contraseña, empresa activa, usuario activo).
- [x] Contraseña temporal + cambio obligatorio.
- [x] Redirección por rol (admin vs operario).
- [x] Usuario activo/inactivo y empresa suspendida bloquean acceso.
- [x] Tabla `empresas` + `users` alineados al esquema AviCore.
- [x] Seeder demo (`AvicoreAuthSeeder`).
- [ ] CRUD usuarios y asignación de roles (módulo 5 del plan).

---

## 7. Bloque 3 — Multiempresa

- Empresas.
- Empresa activa.
- Usuarios por empresa.
- Configuración por empresa.
- Panel Admin AviCore.

---

## 8. Bloque 4 — Estructura avícola

- [x] Migraciones `granjas`, `galpones`, `lotes`, `registros_operativos`.
- [x] Seeder demo mínimo (`AvicoreEstructuraAvicolaSeeder`: 1 granja, 2 galpones, 1 lote).
- [ ] CRUD admin granjas/galpones/lotes.
- [ ] Estados y tipo de huevo en UI admin.

---

## 9. Bloque 5 — Operación móvil

- [x] Layout móvil (home Livewire en `/operario`).
- [x] Selector galpón (`/operario/galpon`) + último galpón en `users.ultimo_galpon_id`.
- [x] Carga huevos (`/operario/carga/huevos`).
- [ ] Carga muertes.
- [ ] Carga alimento.
- [ ] Últimas cargas (listado del día implementado; ampliar tipos al sumar cargas).

---

## 10. Bloque 6 — Dashboard y tiempo real

- Tarjetas.
- Gráficos.
- Alertas.
- Eventos.
- Reverb.
- Echo.

---

## 11. Bloque 7 — Reportes y demo

- Reportes PDF.
- Reportes Excel.
- Datos demo.
- PWA instalable.

---

## 12. Criterio de avance

No avanzar al módulo siguiente si el módulo actual no:

- Guarda datos.
- Valida.
- Respeta permisos.
- Respeta empresa.
- Funciona en PC.
- Funciona en móvil.

---

## 13. Tablas y carpetas futuras (sin migración aún)

Solo nombres de bloque; el DDL vive en `avicore-modelo-datos/references/esquema-bd.md` **cuando** exista la migración.

| Bloque / fase | Tabla o carpeta | Notas |
|---------------|-----------------|-------|
| Movimientos de aves | `movimientos_aves` | Traslados, ajustes, cierres |
| Anulación y auditoría (fase 16) | `auditorias` | Acciones críticas |
| Dashboard (fase 17) | `alertas` | Supervisión |
| Multiempresa / config (fase 6–7) | `configuraciones_empresa` | Maple, logos, módulos |
| Dashboard | `Livewire/Dashboard/`, `DashboardService` | Tarjetas, KPIs |
| Reportes (fase 19) | `Livewire/Reportes/`, `ReporteService` | PDF/Excel |
| Usuarios admin (fase 5) | `Livewire/Usuarios/` | CRUD usuarios |
| CRUD avícola admin | `Livewire/Galpones/`, `Livewire/Lotes/` | Granjas, galpones, lotes |
| Tiempo real (fase 18) | `Events/`, Reverb | Ver `avicore-tiempo-real/references/eventos.md` |
