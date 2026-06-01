# 02 — Pantallas y flujos

## 1. Objetivo

Definir las pantallas principales de AviCore, sus campos, acciones, usuarios autorizados y comportamiento esperado.

---

## 2. Pantalla: Login

### Objetivo

Permitir el acceso seguro al sistema.

### Usuarios

- Admin AviCore.
- Dueño.
- Administrativo.
- Encargado.
- Operario.

### Campos

- Documento.
- Contraseña.
- Recordarme (opcional).

### Acciones

- Iniciar sesión.
- Cerrar sesión (`POST /logout`).

### Presentación (MVP implementado)

- Layout público en **split** (≥1024px): panel de marca a la izquierda, tarjeta de login a la derecha.
- En **móvil** (<1024px): fondo `background-mobile.jpg`, logo apilado centrado sobre la foto y tarjeta blanca anclada abajo con esquinas superiores redondeadas (bottom sheet).
- Inputs con icono Lucide (`id-card`, `lock-keyhole`) y **toggle** para mostrar/ocultar contraseña (un solo control visible).
- Checkbox «Recordarme» con foco visible.
- Recuperación de contraseña: **texto informativo** («contactá a tu administrador»); sin enlace ni flujo automático en MVP (ver regla de negocio en `05`).

### Validaciones

- Documento obligatorio (máx. 50 caracteres).
- Contraseña obligatoria.
- Usuario activo.
- Empresa activa (estado `activa`; no aplica a Admin AviCore).
- Credenciales válidas.
- Máximo 5 intentos fallidos por documento e IP en 60 segundos; luego mensaje con tiempo de espera.
- Si el documento coincide en más de una cuenta activa con la misma contraseña, se rechaza el acceso (contactar administrador).

### Comportamiento

Tras login exitoso:

1. Si `must_change_password` → `/password/change`.
2. Si no → home según rol: operario → `/operario`; resto (Dueño, Administrativo, Encargado, Admin AviCore) → `/admin`.

Usuario autenticado que visita `/login` se redirige a su home correspondiente.

La raíz `/` redirige: sin sesión → `/login`; con sesión → home del rol (o `/password/change` si aplica).

---

## 3. Pantalla: Cambio obligatorio de contraseña

### Objetivo

Forzar al usuario a cambiar la contraseña temporal.

### Campos

- Contraseña actual.
- Nueva contraseña.
- Confirmar nueva contraseña.

### Acciones

- Guardar nueva contraseña.

### Validaciones

- Contraseña actual correcta.
- Nueva contraseña segura: mínimo 8 caracteres, letras, mayúsculas y minúsculas, números.
- Nueva contraseña distinta a la actual.
- Confirmación coincidente.
- No permitir seguir sin cambiarla.

### Presentación

Mismo **layout público** que login: split en escritorio (≥1024px) y bottom sheet en móvil; inputs con iconos y toggle de contraseña donde aplique.

### Comportamiento

Mientras `must_change_password` sea verdadero, el middleware bloquea el acceso a `/admin`, `/operario` y demás rutas protegidas excepto esta pantalla.

Tras guardar: `must_change_password` pasa a falso y redirección al home del rol (`/admin` o `/operario`).

---

## 4. Pantalla: Dashboard

### Objetivo

Mostrar lectura general de la operación.

### Usuarios

- Dueño.
- Administrativo.
- Encargado.

### Tarjetas

- Producción de hoy.
- Producción semanal.
- Productividad por galpón.
- Mortalidad del día.
- Mortalidad acumulada.
- Aves vivas estimadas.
- Alimento entregado.
- Galpones sin carga.
- Alertas.
- Variación de producción.

### Filtros

- Fecha.
- Rango de fechas.
- Granja.
- Galpón.
- Tipo de huevo.

### Tiempo real

Debe actualizarse ante:

- Nueva carga.
- Anulación.
- Corrección.
- Ajuste de aves vivas.
- Cierre de lote.
- Salida parcial de aves.

---

## 5. Pantalla: Vista móvil del operario

### Objetivo

Permitir carga rápida desde celular.

### Usuarios

- Operario.
- Encargado, si necesita cargar.

### Elementos

- Logo AviCore.
- Galpón actual.
- Botón cambiar galpón.
- Botón Huevos.
- Botón Muertes.
- Botón Alimento.
- Botón Carga combinada.
- Últimas cargas del día.
- Estado de guardado.

### Flujo

```text
Seleccionar tipo de carga → ingresar cantidad → guardar → confirmar → volver al galpón actual
```

---

## 6. Pantalla: Selector de galpón

### Objetivo

Permitir elegir galpón de trabajo.

### Campos

- Empresa actual.
- Granja.
- Galpón.

### Reglas

- El usuario puede elegir cualquier galpón de su empresa.
- El sistema recuerda el último galpón seleccionado.
- Al iniciar sesión se abre el último galpón usado.

---

## 7. Pantalla: Carga de huevos

### Campos

- Galpón actual.
- Cantidad de huevos.
- Observación opcional.

### Reglas

- Fecha y hora automática.
- No hay selector de fecha/hora para operario.
- Cantidad obligatoria.
- Debe guardar en unidad huevos.
- Debe emitir evento en tiempo real.

---

## 8. Pantalla: Carga de muertes

### Campos

- Galpón actual.
- Cantidad de muertes.
- Observación opcional.

### Reglas

- Fecha y hora automática.
- Cantidad obligatoria.
- Descuenta aves vivas.
- No permite aves vivas negativas.
- Debe emitir evento en tiempo real.

---

## 9. Pantalla: Carga de alimento

### Campos

- Galpón actual.
- Kilos entregados.
- Observación opcional.

### Reglas

- Fecha y hora automática.
- Permite decimales.
- No requiere cargar huevos.
- No maneja stock en MVP.
- Debe emitir evento en tiempo real.

---

## 10. Pantalla: Carga combinada

### Campos

- Huevos.
- Muertes.
- Alimento en kilos.
- Observación opcional.

### Regla

Debe existir al menos un dato cargado.

---

## 11. Pantalla: Empresas

### Usuarios

- Admin AviCore.

### Campos

- Nombre.
- Código.
- Logo.
- Estado.
- Plan.
- Configuración.

---

## 12. Pantalla: Granjas

### Usuarios

- Dueño.
- Administrativo.

### Campos

- Empresa.
- Nombre.
- Código.
- Ubicación.
- Estado.

---

## 13. Pantalla: Galpones

### Usuarios

- Dueño.
- Administrativo.
- Encargado, si se habilita.

### Campos

- Granja.
- Nombre.
- Código.
- Capacidad máxima.
- Estado.
- Activo/inactivo.
- Observación.

---

## 14. Pantalla: Lotes

### Campos

- Código.
- Fecha de nacimiento.
- Fecha de ingreso.
- Cantidad inicial.
- Galpón actual.
- Línea/raza.
- Tipo de huevo.
- Estado.
- Observación.

---

## 15. Pantalla: Usuarios

### Usuarios

- Dueño.
- Administrativo.
- Admin AviCore.

### Campos

- Nombre.
- Documento.
- Contraseña temporal.
- Rol.
- Estado.
- Empresa.

---

## 16. Pantalla: Auditoría

### Usuarios

- Admin AviCore.
- Dueño.
- Encargado autorizado.

### Muestra

- Usuario.
- Acción.
- Fecha.
- Registro afectado.
- Valor anterior.
- Valor nuevo.
- Motivo.

---

## 17. Pantalla: Reportes

### Reportes

- Diario.
- Semanal.
- Mensual.
- Por galpón.
- Por lote.

### Acciones

- Filtrar.
- Generar.
- Exportar PDF.
- Exportar Excel.
