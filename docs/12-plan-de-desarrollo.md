# 12 — Plan de desarrollo

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

## 5. Bloque 1 — Base

- Crear proyecto.
- Configurar PostgreSQL.
- Configurar Tailwind.
- Configurar Livewire.
- Configurar Alpine.
- Crear layout base.
- Crear componentes UI básicos.

---

## 6. Bloque 2 — Seguridad

- Login.
- Contraseña temporal.
- Cambio obligatorio.
- Roles.
- Redirección por rol.
- Usuario activo/inactivo.

---

## 7. Bloque 3 — Multiempresa

- Empresas.
- Empresa activa.
- Usuarios por empresa.
- Configuración por empresa.
- Panel Admin AviCore.

---

## 8. Bloque 4 — Estructura avícola

- Granjas.
- Galpones.
- Lotes.
- Estados.
- Tipo de huevo.

---

## 9. Bloque 5 — Operación móvil

- Layout móvil.
- Selector galpón.
- Último galpón.
- Carga huevos.
- Carga muertes.
- Carga alimento.
- Últimas cargas.

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
