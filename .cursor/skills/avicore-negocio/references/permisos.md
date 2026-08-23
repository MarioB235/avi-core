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
| Acceso modo soporte | Sí | No | No | No | No |
| Ver dashboard empresa | Soporte | Sí | Sí/Opcional | Sí | No |
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

---

## 3. Admin AviCore

Puede:

- Crear empresas.
- Suspender empresas.
- Configurar clientes.
- Crear administrador inicial.
- Acceder en modo soporte.
- Gestionar datos demo.

No debe:

- Operar datos productivos como usuario común.
- Acceder a clientes sin motivo auditado.

---

## 4. Dueño

Puede ver y gestionar toda su empresa.

Puede:

- Dashboard y Resumen operativo.
- Reportes (post-MVP).
- Alta de lote vía vista móvil `/operario` (no panel Estructura).
- Equipo (solo lectura) y Comercial (preview).

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

Si `must_change_password`, todas las rutas autenticadas excepto `/password/change` redirigen al cambio obligatorio.

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

---

## 9. Policies implementadas (MVP operario)

| Modelo | Policy | Reglas |
|--------|--------|--------|
| `Galpon` | `GalponPolicy` | `viewAny`/`view` si `empresa_id` coincide; `create`/`update` si `canManageEstructura()` (administrativo). Carga operario: galpón disponible en `OperarioGalponService`. |
| `Granja` | `GranjaPolicy` | `viewAny` si `canViewEstructura()`; `create`/`update` si `canManageEstructura()`. CRUD en `/{rol}/estructura` (administrativo y encargado; dueño sin tab Estructura — ver §10). |
| `Lote` | `LotePolicy` | `create`/`update` si `canManageLotes()` (administrativo, encargado; dueño solo vía operario móvil). Alta vía `RegistrarLoteAction` (operario hub o panel estructura). |
| `User` | `UserPolicy` | `viewAny` / `view` / `create` / `update` / `resetPassword` / `toggleActive` según `UserRole::canViewUsers|canManageUsers|canResetUserPassword` y scope multiempresa (Admin AviCore ve todos). `updateProfile`: solo el propio usuario activo (`$actor->is($target) && $actor->activo`); usado por `UpdateProfileAction` y `ChangePasswordAction`. Encargado: ver listado + `resetPassword`; sin `create`/`update`/`toggleActive`. Roles asignables vía `UserRole::assignableRoles()`. CRUD en `/{rol}/usuarios`. |
| `RegistroOperativo` | `RegistroOperativoPolicy` | `anular`: mismo `empresa_id`; solo registros del **día** (`created_at` hoy); propio → operario y roles superiores; ajeno → dueño, administrativo, encargado (no operario). Lógica compartida en `Policies/Concerns/AuthorizesOperarioAnulacion`. UI Historial: solo registros propios del usuario. |
| `Vacunacion` | `VacunacionPolicy` | `anular`: mismas reglas que `RegistroOperativoPolicy::anular` (trait compartido). Vacunación anulada vía `AnularVacunacionAction`. |

---

## 10. MVP: foco Dueño (panel admin)

**Decisión (2026-08-15):** el panel del **Dueño** (`/dueno`) es la referencia de diseño MVP para Inicio y Resumen. Administrativo y Encargado tienen prefijo propio; hoy comparten vistas Livewire hasta desarrollar pantallas diferenciadas.

| Rol | MVP hoy | Próximo paso (post-MVP) |
|-----|---------|-------------------------|
| **Dueño** | Inicio (solo KPIs), Resumen, **Equipo**, **Comercial** (preview); sin Estructura ni Usuarios | Reportes, auditoría, Comercial con datos reales |
| **Administrativo** | Inicio, Resumen, **Estructura**, **Usuarios** (CRUD) | Sin asignar rol Dueño; sin ajustes sensibles de empresa |
| **Encargado** | Inicio, Resumen, Estructura (ver + lotes), Usuarios (ver + reset contraseña) | Pantallas propias en `/encargado` |
| **Admin AviCore** | Inicio, Usuarios (multiempresa) | Sin cambio |
| **Operario** | Solo `/operario` | Sin cambio |

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
| Comercial | Sí (preview) | No | No | No |
| Estructura | No | Sí (CRUD completo) | Sí (ver + lotes) | No |
| Usuarios | No | Sí (CRUD) | Sí (ver + reset) | Sí (CRUD multiempresa) |

Métodos en `UserRole`: `canViewResumen`, `canViewEquipo`, `canViewComercial`, `canViewEstructura`, `canViewUsers`, `canManageEstructura`, `canManageUsers`, `canResetUserPassword`.
