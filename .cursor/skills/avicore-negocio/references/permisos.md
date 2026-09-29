# 06 — Roles y permisos

## 1. Roles

- Admin AviCore.
- Dueño.
- Administrativo.
- Encargado.
- Operario.
- Reparto.

---

## 2. Matriz general

| Acción | Admin AviCore | Dueño | Administrativo | Encargado | Operario |
|---|---|---|---|---|---|
| Crear empresa cliente | Sí | No | No | No | No |
| Suspender empresa | Sí | No | No | No | No |
| Acceso modo soporte | Sí (`enterSupport`) | No | No | No | No |
| Ver Resumen operativo | Solo con sesión soporte activa | Sí | Sí/Opcional | Sí | No |
| Ver Historial operativo (equipo) | Solo con sesión soporte activa | Sí | Sí | Sí | No |
| Mutar lotes/cargas en soporte | No (solo lectura) | Sí* | Sí | Sí | Sí |
| Crear granja | No | No | Sí | No | No |
| Crear galpón | No | No | Sí | No | No |
| Crear lote | No | Sí* | Sí | Sí | No |
| Crear usuario | Sí | No | Sí | No | No |
| Editar / activar-desactivar usuario | Sí | No | Sí | No | No |
| Resetear contraseña | Sí | No | Sí | Sí | No |
| Cargar huevos | No | Sí | Sí | Sí | Sí |
| Cargar muertes | No | Sí | Sí | Sí | Sí |
| Cargar alimento | No | Sí | Sí | Sí | Sí |
| Editar perfil propio (nombre, correo, contraseña) | Sí | Sí | Sí | Sí | Sí |
| Anular registro propio del día | No | Sí | Sí | Sí | Sí |
| Anular registro de otro usuario | No | Sí | Sí | Sí | No |
| Corregir registros | No | Sí | Sí | Sí | No |
| Ajustar aves vivas | No | Sí | Sí | Sí | No |
| Ver auditoría | Soporte | Sí | Sí/Opcional | Sí | No |
| Exportar PDF | No | Sí | Sí | Sí | No |
| Exportar Excel | No | Sí | Sí | Sí | No |

\* Dueño: alta de lote solo vía vista móvil `/operario` (hub Cargar), no panel Estructura.

### Decisión D02 (cerrada SEG-07, 2026-09-26)

**Problema:** el Dueño necesita lectura simple de su empresa; la operación no puede depender de una sola persona ausente.

**Acuerdo MVP:**

| Faceta | Dueño | Administrativo |
|--------|-------|----------------|
| Supervisión (Resumen) | Sí | Sí |
| Equipo | Sí (lectura) | No |
| Comercial | No (v1 RES-01) | No |
| Estructura / Usuarios | No | Sí (CRUD oficina) |
| Móvil `/operario` | Sí (lote + cargas) | Sí (lote + cargas) |
| Asignar rol Dueño | No (solo Admin Avicore crea Dueño inicial) | No |

**Regla:** roles **complementarios** — el Administrativo cubre granjas, galpones, lotes y usuarios si el Dueño no está; el Dueño mantiene visión estratégica sin duplicar la gestión de oficina.

**Verificación:** `tests/Support/RoleCapabilitiesMatrix.php` + `RoleCapabilitiesMatrixTest` + `DuenoAdministrativoAccessTest`.

---

## 3. Admin Avicore

Puede:

- Crear empresas.
- Suspender empresas.
- Configurar clientes.
- Crear administrador inicial.
- Acceder en modo soporte (`StartSoporteEmpresaAction`, motivo mínimo configurable).
- Ver Resumen y KPIs de la empresa cliente **solo** con sesión de soporte activa y banner visible.
- Gestionar datos demo.

No debe:

- Ver datos operativos de clientes sin sesión de soporte (scope vacío).
- Mutar producción durante soporte (`LotePolicy` + `blocksProductionMutations`).
- Acceder a clientes sin motivo auditado en `soporte_sesiones`.

**Verificación EMP-06 solo lectura:** `tests/Unit/Policies/LotePolicyTest.php`, `tests/Feature/Admin/AdminSoporteEmpresaTest.php` (`test_support_mode_blocks_production_mutations_via_gate`).

---

## 4. Dueño

Puede ver y gestionar toda su empresa.

Puede:

- Dashboard y Resumen operativo.
- Reportes (post-MVP).
- Alta de lote vía vista móvil `/operario` (no panel Estructura).
- Equipo (solo lectura); Comercial deshabilitado en v1 (RES-01).

No gestiona granjas/galpones en panel Estructura (Administrativo).

---

## 5. Administrativo

Puede gestionar estructura.

Puede:

- Granjas.
- Galpones.
- Lotes.
- Usuarios.
- Reportes si se habilita.
- Reset de contraseña.

---

## 6. Encargado

Puede supervisar y corregir operación.

Puede:

- Dashboard operativo.
- Reportes.
- Alertas.
- Cargas.
- Correcciones.
- Anulaciones.
- Auditoría operativa.
- Reset de contraseña si se habilita.

---

## 7. Acceso post-login (Bloque 2)

Cada rol tiene **prefijo de ruta propio** (Opción A). `/admin` y `/admin/*` redirigen al prefijo del rol autenticado (compatibilidad con bookmarks).

| Rol | Home tras login | Prefijo panel | Vista móvil `/operario` |
|---|---|---|---|
| Operario | `/operario` | — | Sí (carga operativa) |
| Dueño | `/dueno` | `/dueno/*` | Sí |
| Administrativo | `/administrativo` | `/administrativo/*` | Sí |
| Encargado | `/encargado` | `/encargado/*` | Sí |
| Reparto | `/reparto` | `/reparto/*` (stub MVP) | No |
| Admin AviCore | `/avicore` | `/avicore/*` | No (`/operario` redirige a `/avicore`) |

Middleware `EnsureRolePanelAccess`: solo el rol dueño del prefijo accede a ese panel; otro rol → redirect a su `homeRouteName()`.

`EnsureAccountVigente` (grupo `web`): en **cada request** revalida usuario `activo` y empresa `permiteLogin()` vía `AccountAccessService`; si falla, cierra sesión y redirige a login (incluye actualizaciones Livewire). Refresca el modelo en memoria para que cambios de rol o `must_change_password` apliquen sin re-login.

`EnsurePasswordChanged`, `EnsureOperarioAccess` y `EnsureRolePanelAccess` también están registrados como **middleware persistente de Livewire** (`AppServiceProvider`): las acciones de componente con snapshot previo respetan el rol y el cambio de clave obligatorio.

**Autorización por acción Livewire (SEG-04):** Resumen/Equipo/Comercial/Historial/Auditoría/Movimientos usan `AdminModulePolicy` vía Gates `admin.viewResumen|Equipo|Comercial|HistorialOperativo|Auditoria|Movimientos` y trait `RequiresAdminModuleAccess` (`mount` + `hydrate`); Usuarios/Estructura usan `$this->authorize(...)` con policies de modelo; operario rechaza `guardarLote` / `abrirFormularioLote` vía `Gate` + `LotePolicy::create` (403), no fallo silencioso. Pantalla **Movimientos** (MOV-12): solo roles con `canManageLotes` y gate `admin.viewMovimientos`; operario 403 en Livewire y en Actions de ledger.

**Aislamiento multiempresa (SEG-05):** `EmpresaScopeService` centraliza `constrainQuery` / `findForActor` para Livewire admin; policies + `forEmpresa()` en Actions/servicios; suite `EmpresaIsolationTest` cubre filtro, alta, edición, anulación y estructura.

**Coherencia relacional (SEG-06):** `EmpresaRelationalGuard` valida actor↔empresa, granja↔galpón y lote↔galpón en Actions de estructura, lotes, cargas y vacunación; suite `RelationalCoherenceTest` + unit `EmpresaRelationalGuardTest`.

**Matriz Dueño/Administrativo (SEG-07 / D02):** `tests/Support/RoleCapabilitiesMatrix.php` es la fuente de verdad para tests; `RoleCapabilitiesMatrixTest` y `DuenoAdministrativoAccessTest` verifican enum + rutas.

**Administración segura (SEG-08):** `UserManagementGuard` impide dejar la empresa sin administrativo activo (desactivar o degradar el último con `canManageUsers`); `UpdateUserAction` ya bloquea auto-desactivación y roles no asignables (`assignableRoles`). Tests: `UserManagementGuardTest`, `AdminUsuariosTest`.

**Login multiempresa (SEG-09):** `LoginCandidateResolver` centraliza documento + contraseña + vigencia (`AccountAccessService`); sin selector de empresa en MVP; ambigüedad → mensaje genérico sin revelar empresas. Tests: `LoginCandidateResolverTest`, `MultiEmpresaLoginTest`, `LoginFlowTest`.

**Recuperación y sesiones (SEG-10):** reset autorizado vía `ResetUserPasswordAction` (`must_change_password` + invalidación de sesiones); cambio voluntario en `ChangePasswordAction` cierra otras sesiones; desactivar usuario invalida sesiones (`UpdateUserAction`); clave temporal solo en UI, sin logs. Invalidación efectiva requiere `SESSION_DRIVER=database` en producción/staging (`arranque-local.md`). Tests: `UserSessionServiceTest`, `SessionRecoveryTest`, `ChangePasswordTest`, `SessionVigenciaTest`.

Si `must_change_password`, todas las rutas autenticadas excepto `/password/change` redirigen al cambio obligatorio (GET y `POST /livewire/update`).

Valores de rol en BD: `admin_avicore`, `dueno`, `administrativo`, `encargado`, `operario`, `reparto` (enum `UserRole`).

---

## 8. Operario

Usa vista móvil simplificada.

Puede:

- Seleccionar galpón.
- Cargar huevos.
- Cargar muertes.
- Cargar alimento.
- Ver sus registros recientes.
- Anular registros propios del día.
- Editar su perfil (nombre, correo, contraseña) en `/operario/perfil`.

No puede:

- Ver panel completo.
- Gestionar estructura.
- Corregir registros ajenos.
- Ver auditoría general.
- Exportar reportes.

### Reportes v1 (REP-01 — catálogo; export REP-08)

| Reporte (`ReportesCatalogoV1`) | Dueño | Administrativo | Encargado | Operario |
|--------------------------------|-------|----------------|-----------|----------|
| Producción diaria | Sí | Sí | Sí | No |
| Movimientos / existencias | Sí | Sí | Sí | No |
| Historia de lote | Sí | Sí | Sí | No |
| Sanidad básica | Sí | Sí | Sí | No |
| Auditoría operativa | Sí | Sí | No | No |

Ability propuesta al implementar: `admin.exportReportes` (revisar en REP-08).

---

## 9. Policies implementadas (MVP operario)

| Modelo | Policy | Reglas |
|--------|--------|--------|
| `Galpon` | `GalponPolicy` | `viewAny`/`view` si `empresa_id` coincide; `create`/`update` si `canManageEstructura()` (administrativo). Carga operario: galpón disponible en `OperarioGalponService`. |
| `Granja` | `GranjaPolicy` | `viewAny` si `canViewEstructura()`; `create`/`update` si `canManageEstructura()`. CRUD en `/{rol}/estructura` (administrativo y encargado; dueño sin tab Estructura — ver §10). |
| `Lote` | `LotePolicy` | `create`/`update`/`transition` si `canManageLotes()`; `transition` en lote cerrado solo si `canReabrirLote()` (Dueño/Administrativo); lote trasladado sin transiciones. Reapertura operativa: `ReabrirLoteExcepcionalAction` (MOV-07). Metadatos: `UpdateLoteAction`; estado: `TransicionarLoteEstadoAction` con motivo. UI Estructura: opciones de «Cambiar estado» y guardado usan scope `empresa_id` (lote ajeno → sin opciones / 404). Tests reapertura: `MovimientoAvesReaperturaLoteTest`. |
| `User` | `UserPolicy` | `viewAny` / `view` / `create` / `update` / `resetPassword` / `toggleActive` según `UserRole::canViewUsers|canManageUsers|canResetUserPassword` y scope multiempresa (Admin AviCore ve todos). `updateProfile`: solo el propio usuario activo (`$actor->is($target) && $actor->activo`); usado por `UpdateProfileAction` y `ChangePasswordAction`. Encargado: ver listado + `resetPassword`; sin `create`/`update`/`toggleActive`. Roles asignables vía `UserRole::assignableRoles()`. CRUD en `/{rol}/usuarios`. |
| `RegistroOperativo` | `RegistroOperativoPolicy` | `anular`: mismo `empresa_id`; solo registros del **día** (`created_at` hoy); propio → operario y roles superiores; ajeno → dueño, administrativo, encargado (no operario). Lógica compartida en `Policies/Concerns/AuthorizesOperarioAnulacion`. `corregir`: dueño, administrativo, encargado; registro activo de la misma empresa; sin límite de día; trait `AuthorizesOperarioCorreccion`. UI corrección: historial supervisor (`/{rol}/historial-operativo`). UI Historial operario: solo registros propios del usuario. |
| `Vacunacion` | `VacunacionPolicy` | `anular`: mismas reglas que `RegistroOperativoPolicy::anular` (trait compartido). Vacunación anulada vía `AnularVacunacionAction`. |
| `MovimientoAves` | `MovimientoAvesPolicy` | `create`: `canManageLotes()` (dueño, administrativo, encargado); bloqueado en soporte AviCore (`blocksProductionMutations`). `view`: mismo `empresa_id`. Entrada externa vía `RegistrarEntradaAvesAction`, traslado vía `RegistrarTrasladoAvesAction`, ajuste vía `RegistrarAjusteInventarioAvesAction`, cierre vía `RegistrarCierreLoteAction` y reversión vía `RevertirMovimientoAvesAction` + `Gate::authorize('create')` y `view` del movimiento (cierre de ciclo también `transition` en `Lote`). Tests: `MovimientoAvesPolicyTest`, `MovimientoAvesEntradaSaldoInicialTest`, `MovimientoAvesTrasladoTest`, `MovimientoAvesAjusteInventarioTest`, `MovimientoAvesCierreLoteTest`, `MovimientoAvesReversionTest` (403 operario, galpón ajeno, soporte). |

---

## 10. MVP: foco Dueño (panel admin)

**Decisión (2026-08-15):** el panel del **Dueño** (`/dueno`) es la referencia de diseño MVP para Inicio y Resumen. Administrativo y Encargado tienen prefijo propio; hoy comparten vistas Livewire hasta desarrollar pantallas diferenciadas.

| Rol | MVP hoy | Próximo paso (post-MVP) |
|-----|---------|-------------------------|
| **Dueño** | Inicio, Resumen, Historial, Auditoría, Movimientos, **Equipo**; sin Comercial/Estructura/Usuarios en v1 | Comercial etapa 2, reportes |
| **Administrativo** | Inicio, Resumen, **Estructura**, **Usuarios** (CRUD) | Sin asignar rol Dueño; sin ajustes sensibles de empresa |
| **Encargado** | Inicio, Resumen, Estructura (ver + lotes), Usuarios (ver + reset contraseña) | Pantallas propias en `/encargado` |
| **Admin AviCore** | Inicio, Usuarios (multiempresa) | Sin cambio |
| **Operario** | Solo `/operario` | Sin cambio |
| **Reparto** | Stub `/reparto` (etapa 2); sin móvil ni Resumen | `canAccessOperarioMobile` y `canViewResumen` en **false**; `assignableRoles` vacío |

**Práctica de desarrollo:** login demo **Dueño** para Inicio/Resumen; **Administrativo** para Estructura; **Operario** para campo; **Encargado** para supervisión.

**No eliminar** el rol Administrativo del enum, seed ni selector demo — evita retrabajo cuando se diferencien permisos.

---

## 11. Módulos visibles en nav por rol

Tabs en `AdminNav` (bottom nav / sidebar). Ruta = `/{prefijo-rol}/…`.

| Módulo | Dueño | Administrativo | Encargado | Admin AviCore |
|--------|-------|----------------|-----------|---------------|
| Inicio | Sí | Sí | Sí | Sí |
| Resumen | Sí | Sí | Sí | No |
| Equipo | Sí (solo lectura) | No (tab Usuarios) | No (tab Usuarios) | No |
| Comercial | No (v1) | No | No | No |
| Estructura | No | Sí (CRUD completo) | Sí (ver + lotes) | No |
| Usuarios | No | Sí (CRUD) | Sí (ver + reset) | Sí (CRUD multiempresa) |
| Reparto (stub) | No | No | No | No |

Métodos en `UserRole`: `canViewResumen`, `canViewEquipo`, `canViewComercial`, `canViewEstructura`, `canViewUsers`, `canManageEstructura`, `canManageUsers`, `canResetUserPassword`.

Acceso directo a rutas sin permiso (p. ej. encargado en `/encargado/equipo` o `/encargado/comercial`) → **HTTP 403** vía `AuthorizationException` en `mount()` del Livewire correspondiente.
