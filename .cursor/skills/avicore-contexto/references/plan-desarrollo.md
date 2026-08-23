# 12 — Plan de desarrollo

## 0. Estado de avance (2026-08-01)

| Bloque | Estado | Notas |
|--------|--------|-------|
| **1 — Base** | **Hecho** | Laravel 13, Livewire 4, Tailwind 4, layouts, UI base, PostgreSQL + `migrate` OK |
| **2 — Seguridad** | **Parcial** | Login + cambio obligatorio; **CRUD usuarios admin** (`/admin/usuarios`) hecho; falta auditoría de accesos soporte |
| **4 — Estructura avícola** | **Parcial** | Migraciones + seeder demo; alta de lote desde operario; **CRUD admin** granjas/galpones/lotes + DICOSE en `/admin/estructura` |
| **5 — Operación móvil** | **Hecho MVP** | Home, Cargar (huevos, muertes, descarte, vacunación, alimento, lote), Historial (detalle + anulación), perfil, PWA; sin offline completo |
| 3, 6–7 | Pendiente | Según orden de la sección 2 |

Detalle técnico del Bloque 1: [`arbol-proyecto.md`](arbol-proyecto.md) · entorno local: [`arranque-local.md`](arranque-local.md).

---

## 1. Estrategia

Desarrollar módulo por módulo.

No construir todas las pantallas antes del backend.

**Operario primero (2026-08-10):** la captura en galpón es el origen de los datos; completar operario antes de admin/dashboard. **Panel admin Dueño-first (2026-08-15):** diseñar y probar `/admin` como Dueño; Administrativo comparte permisos en MVP. Olas: [`estrategia-implementacion.md`](estrategia-implementacion.md).

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
| 15 | Anulación y auditoría |
| 16 | Dashboard |
| 17 | Tiempo real |
| 18 | Reportes |
| 19 | Datos demo |
| 20 | PWA | **Hecho MVP (2026-08-01)** — instalable, sin offline completo; ver `avicore-pwa/references/pwa.md` |

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
- [x] CRUD usuarios y asignación de roles (módulo 5 del plan) — `/admin/usuarios`.

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
- [x] CRUD admin granjas/galpones/lotes + DICOSE — `/admin/estructura`.
- [ ] Estados y tipo de huevo en UI admin (filtros avanzados).

---

## 9. Bloque 5 — Operación móvil

- [x] Layout móvil (home Livewire en `/operario`).
- [x] Selector galpón (chip en Inicio/Cargar/Historial; `users.ultimo_galpon_id`) — **sin** ruta dedicada `/operario/galpon`.
- [x] Hub Cargar (`/operario/cargar`) + deep links `form=`.
- [x] Carga huevos (aptos + descarte).
- [x] Carga muertes.
- [x] Carga descarte de aves.
- [x] Carga vacunación.
- [x] Carga alimento (kg por entrega en hub).
- [x] Alta lote desde operario (hub / redirect `CargaLote`; código SMA opcional).
- [x] Historial con filtro fecha, detalle por ítem y anulación propia del día.
- [x] Perfil de cuenta (`/operario/perfil`).

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
- ~~PWA instalable.~~ **Hecho MVP (2026-08-01)** — ver `avicore-pwa/references/pwa.md` (offline completo fuera de alcance).

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
| Sanidad / vacunas (plan completo) | módulo `Sanidad/` o calendario sanitario | **Post-MVP** — stock, calendario y reportes sanitarios |
| Vacunación operario (registro por lote) | `vacunaciones` | **Hecho MVP (2026-07-02)** — hub Cargar + `RegistrarVacunacionAction`; ver `esquema-bd.md` y `pantallas-flujos.md` §8.5 |
| Multiempresa / config (fase 6–7) | `configuraciones_empresa` | Maple, logos, módulos |
| Dashboard | `Livewire/Dashboard/`, `DashboardService` | Tarjetas, KPIs |
| Reportes (fase 19) | `Livewire/Reportes/`, `ReporteService` | PDF/Excel |
| Usuarios admin (fase 5) | `Livewire/Admin/Usuarios/` | **Hecho MVP (2026-07-16)** — listado, alta/edición, reset clave, activar/desactivar; ver `pantallas-flujos.md` §3.2 |
| CRUD avícola admin | `Livewire/Galpones/`, `Livewire/Lotes/` | Granjas, galpones, lotes |
| Tiempo real (fase 18) | `Events/`, Reverb | Ver `avicore-tiempo-real/references/eventos.md` |
| PWA instalable (fase 21) | `vite.config.js`, `pwa.js`, `x-ui.pwa-*` | **Hecho MVP (2026-08-01)** — manifest + SW assets + banner; ver `pwa.md` |
| Coeficientes técnicos Uruguay | `DashboardService`, alertas, curvas postura | Postura 269–278 huevos/año, conversión alimenticia, mortalidad &lt;1,1%; ver `mercado-uruguay.md` §3 |
| Integración MGAP / SMA / SNIG | export SNIG, pre-llenado formularios | Trazabilidad oficial; ver `mercado-uruguay.md` §4 |
| Sistemas Free Range / pastoreo | campos o módulo diferenciado | Nicho INIA; variables distintas a jaula tradicional |
| DICOSE / habilitación SMA | `configuraciones_empresa` o `granjas` | N° DICOSE, refrendación anual — ver `mercado-uruguay.md` §4 §7.1 |
| Bioseguridad — ingreso personas/vehículos | módulo registros §7.3 | Planilla control ingresos; filtros sanitarios |
| Vacío sanitario | estado galpón / bloqueo carga | Intervalo entre lotes; POES |
| Stock alimento / insumos §7.4 | `ingresos_alimento`, silos | Proveedor, lote, vencimiento; formulación raciones |
| Planilla control sanitario §7.10 | extensión `vacunaciones` + ATB | Plan sanitario VLE; antibióticos con prescripción |
| Residuos / PGRS §7.11 | módulo residuos | Movimiento cama/abono; alertas mortalidad masiva |
| Control plagas §7.9 | módulo cebos | Inspección semanal; plano instalaciones |
| Capacitación §7.6 | registros capacitación | Temario, participantes; retención 2 años |
| Remitos SMA / faena §8.4 | integración SMA | Protocolo envío a faena; Res. 325/024 |
| Export planillas Anexo A | `ReporteService` | PDF/Excel con DICOSE; catálogo §8.4 + §9 |
| Comercial / clientes (post-MVP) | `clientes`, `pedidos_huevos`, `repartos` | Pedidos recurrentes, reservas, última venta; rol reparto; preview en Inicio admin |
