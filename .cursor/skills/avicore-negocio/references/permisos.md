# 06 — Roles y permisos

## 1. Roles

- Admin AviCore.
- Dueño.
- Administrativo.
- Encargado.
- Operario.

---

## 2. Matriz general

| Acción | Admin AviCore | Dueño | Administrativo | Encargado | Operario |
|---|---|---|---|---|---|
| Crear empresa cliente | Sí | No | No | No | No |
| Suspender empresa | Sí | No | No | No | No |
| Acceso modo soporte | Sí | No | No | No | No |
| Ver dashboard empresa | Soporte | Sí | Sí/Opcional | Sí | No |
| Crear granja | No | Sí | Sí | No/Opcional | No |
| Crear galpón | No | Sí | Sí | No/Opcional | No |
| Crear lote | No | Sí | Sí | Sí/Opcional | No |
| Crear usuario | No | Sí | Sí | No/Opcional | No |
| Resetear contraseña | No | Sí | Sí | Sí/Opcional | No |
| Cargar huevos | No | Sí | Sí | Sí | Sí |
| Cargar muertes | No | Sí | Sí | Sí | Sí |
| Cargar alimento | No | Sí | Sí | Sí | Sí |
| Anular registro propio del día | No | Sí | Sí | Sí | Sí |
| Anular registro de otro usuario | No | Sí | Sí | Sí | No |
| Corregir registros | No | Sí | Sí | Sí | No |
| Ajustar aves vivas | No | Sí | Sí | Sí | No |
| Ver auditoría | Soporte | Sí | Sí/Opcional | Sí | No |
| Exportar PDF | No | Sí | Sí | Sí | No |
| Exportar Excel | No | Sí | Sí | Sí | No |

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

- Dashboard.
- Reportes.
- Usuarios.
- Granjas.
- Galpones.
- Lotes.
- Auditoría.
- Exportaciones.
- Ajustes.

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

| Rol | Home tras login | Rutas protegidas |
|---|---|---|
| Operario | `/operario` | Solo `/operario`; `/admin` redirige a `/operario` |
| Dueño, Administrativo, Encargado, Admin AviCore | `/admin` | Solo `/admin`; `/operario` redirige a `/admin` |

Si `must_change_password`, todas las rutas autenticadas excepto `/password/change` redirigen al cambio obligatorio.

Valores de rol en BD: `admin_avicore`, `dueno`, `administrativo`, `encargado`, `operario` (enum `UserRole`).

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
| `Galpon` | `GalponPolicy` | `viewAny` y `view` si el usuario tiene `empresa_id` y coincide con `galpon.empresa_id`. El acceso a rutas `/operario` lo restringe además `EnsureOperarioAccess` (solo rol operario). La selección y carga validan galpón disponible en `OperarioGalponService` y `RegistrarCargaHuevosAction`. |
