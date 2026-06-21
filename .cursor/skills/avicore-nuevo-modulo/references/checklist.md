# 11 — Checklist de desarrollo por módulo

## 1. Objetivo

Definir cuándo un módulo se considera terminado.

---

## 2. Checklist general

Un módulo no está terminado solo porque se ve bien.

Debe cumplir:

- Pantalla creada.
- Formulario funcional.
- Datos guardan correctamente.
- Validaciones aplicadas.
- Permisos aplicados.
- Multiempresa respetado.
- Errores controlados.
- Estados vacíos definidos.
- Diseño responsivo.
- Prueba en PC.
- Prueba en celular.
- Auditoría aplicada si corresponde.
- Eventos en tiempo real si corresponde.
- Reportes actualizados si corresponde.

---

## 3. Validaciones

Revisar:

- Campos obligatorios.
- Tipos de dato.
- Cantidades positivas.
- No permitir aves vivas negativas.
- No guardar carga vacía.
- No acceder a datos de otra empresa.

---

## 4. Permisos

Probar con:

- Admin AviCore.
- Dueño.
- Administrativo.
- Encargado.
- Operario.

Verificar:

- Acceso permitido.
- Acceso denegado.
- Acciones ocultas por rol.
- Acciones bloqueadas por backend.

---

## 5. Multiempresa

Probar:

- Usuario empresa A no ve empresa B.
- Registros quedan con empresa_id.
- Filtros respetan empresa.
- Eventos tiempo real respetan empresa.

---

## 6. Auditoría

Aplicar en:

- Anulación.
- Corrección.
- Ajuste aves vivas.
- Cierre lote.
- Reapertura lote.
- Acceso soporte.

---

## 7. Responsivo

Probar en:

- PC.
- Tablet.
- Celular.

Verificar:

- Botones grandes en móvil.
- Tablas adaptadas.
- Formularios legibles.
- Menús usables.

---

## 8. Criterio final

Un módulo está terminado cuando:

```text
Funciona visualmente
Funciona con datos reales
Respeta permisos
Respeta empresa
Maneja errores
Está probado en PC y móvil
```
