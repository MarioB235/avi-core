# 03 — Guía visual UI

**Implementación técnica (tokens, componentes, quality gates):** [`reference/sistema-diseno.md`](reference/sistema-diseno.md).  
Guía externa base: [awesome-design-skills](https://github.com/bergside/awesome-design-skills) — skill **clean** + patrones **enterprise**; la paleta verde/agro de esta página tiene prioridad.

---

## 1. Identidad visual

AviCore usará una identidad:

```text
Verde / agro moderno
```

El diseño debe ser profesional, simple, limpio y confiable.

---

## 2. Estilo general

Debe sentirse:

- Agro moderno.
- Empresarial.
- Claro.
- Responsivo.
- Fácil de usar.
- Confiable.

Debe evitar:

- Aspecto infantil.
- Exceso de íconos.
- Interfaces recargadas.
- Estilo de planilla antigua.
- Colores sin criterio.

---

## 3. Paleta base sugerida

| Uso | Color |
|---|---|
| Verde principal | #1F5E3B |
| Verde secundario | #3A7D44 |
| Verde suave | #EAF5EC |
| Fondo | #F5F7FA |
| Tarjeta | #FFFFFF |
| Texto principal | #1F2933 |
| Texto secundario | #667085 |
| Advertencia | #F59E0B |
| Crítico | #DC2626 |
| Información | #2563EB |

El isotipo oficial (`logo-avicore.svg`) usa el verde principal `#1F5E3B` sobre fondo transparente.

---

## 4. Tipografía

Usar fuente sans-serif moderna.

Recomendación:

- Inter.
- System UI.
- Nunito Sans.
- Source Sans.

Criterio:

- Títulos claros.
- Texto legible.
- No usar tipografías decorativas.

---

## 5. Botones

### Botón primario

Uso:

- Guardar.
- Crear.
- Confirmar.

Estilo:

- Verde principal.
- Texto blanco.
- Bordes redondeados.
- Alto cómodo para móvil.

### Botón secundario

Uso:

- Cancelar.
- Volver.
- Ver detalle.

Estilo:

- Fondo blanco.
- Borde gris.
- Texto oscuro.

### Botón de peligro

Uso:

- Anular.
- Desactivar.

Estilo:

- Rojo.
- Texto blanco.
- Confirmación obligatoria.

---

## 6. Inputs y selectores

Reglas:

- Label siempre visible.
- Placeholder simple.
- Mensajes de error claros.
- Estados de foco visibles.
- Tamaño cómodo en móvil.
- No saturar formularios.

---

## 7. Cards

Uso:

- KPIs.
- Resúmenes.
- Accesos rápidos.
- Alertas.

Estructura recomendada:

- Título.
- Valor principal.
- Variación o subtítulo.
- Estado visual.

---

## 8. Tablas

Reglas:

- Encabezados claros.
- Filtros superiores.
- Acciones agrupadas.
- Filas limpias.
- Estados con badges.
- Responsive en móvil.

---

## 9. Badges

Estados recomendados:

- Activo.
- Inactivo.
- En producción.
- Cerrado.
- Anulado.
- Alerta.
- Revisado.

Colores:

- Verde: activo/correcto.
- Gris: inactivo.
- Azul: información.
- Amarillo: advertencia.
- Rojo: crítico/anulado.

---

## 10. Alertas

Las alertas deben ser visuales y claras.

Tipos:

- Información.
- Advertencia.
- Crítica.
- Éxito.

Ejemplo:

```text
Este galpón tiene más de un lote activo. La producción se registrará sobre el galpón completo.
```

---

## 11. Layout web

Usar:

- Sidebar lateral.
- Header superior.
- Área de contenido.
- Cards.
- Filtros.
- Tablas.

---

## 12. Layout móvil

Prioridades:

- Galpón actual visible.
- Botones grandes.
- Pocos campos.
- Acciones rápidas.
- Confirmaciones claras.

---

## 13. Iconos

Usar iconos simples y funcionales vía **`x-ui.icon`** (SVG inline, trazo `currentColor`).

Nombres ya disponibles en el componente: `home`, `users`, `warehouse`, `egg`, `chart`, `document`, `lock`, `menu`, `logout`, `eye`, `eye-off`, entre otros.

No abusar de iconos decorativos; no importar librerías externas de iconos salvo decisión explícita en arquitectura.
