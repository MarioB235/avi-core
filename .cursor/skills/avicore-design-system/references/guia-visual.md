# 03 — Guía visual UI

**Implementación técnica (tokens, componentes, quality gates):** [`tokens-componentes.md`](tokens-componentes.md).  
**Refined Agro (motion, elevación, mobile vs admin):** [`refined-agro-principios.md`](refined-agro-principios.md).  
Guía externa base: [awesome-design-skills](https://github.com/bergside/awesome-design-skills) — skill **clean** + patrones **enterprise** + polish **Refined Agro**; la paleta verde/agro de esta página tiene prioridad.

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

El isotipo oficial (`logo-avicore.png`) usa el verde principal `#1F5E3B` sobre fondo transparente.

**Wordmark «AviCore»** (texto junto al isotipo en `x-ui.logo`): `font-semibold`, color primario; **sin** uppercase, tracking amplio ni tipografía decorativa. Tamaños por contexto: `hero` en panel de marca (escritorio), `auth-mobile` + `stacked` en login móvil. Detalle técnico: [`tokens-componentes.md`](tokens-componentes.md).

**Auth (login y cambio de contraseña):** escritorio en split marca + tarjeta; móvil con fondo de marca, logo apilado y tarjeta tipo bottom sheet — ver [`02-pantallas-y-flujos.md`](02-pantallas-y-flujos.md) § Login.

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

- **Header contextual:** título de la pestaña activa (Inicio, Galpón, Cargar, Historial) y galpón actual como subtítulo informativo; sin galpón, subtítulo *«Elegí un galpón en la pestaña Galpón»*; el cambio de galpón es solo en la pestaña Galpón (sin flecha ni enlace duplicado en el header).
- **Dock inferior inset** con 4 pestañas: ítem activo con fondo verde en la celda; animación ligera (`prefers-reduced-motion` sin escala).
- Hub de cargas (grid 2×2) en pestaña Cargar; formularios mantienen la barra con «Cargar» activo.
- Botones grandes (touch ≥ 44px).
- Pocos campos por pantalla.
- Confirmaciones claras al guardar.

---

## 13. Iconos

Usar iconos simples y funcionales vía **`x-ui.icon`**.

**Fuente:** archivos Lucide en `resources/images/icons/` (kebab-case) cargados por `App\Support\IconSvg`; si falta el archivo, fallback inline en `components/ui/icons/inline.blade.php`.

**Color:** trazo `stroke="currentColor"` en el SVG — **no** colorear al exportar desde Lucide. En pantalla, aplicar Tailwind sobre el componente: `text-avicore-primary` en inputs auth y diálogo de soporte; `text-avicore-muted` en toggles (ojo). El verde de marca es `#1F5E3B` (`avicore-primary`).

**Auth y diálogo de contacto:** `id-card`, `lock-keyhole`, `key-round`, `shield-check`, `eye`, `eye-off`, `mail`, `message-circle-check`, `circle-x` (cerrar modal).

**Nav y dashboard:** `home`, `users`, `warehouse`, `egg`, `chart`, `document`, `lock`, `menu`, `logout`, entre otros.

No abusar de iconos decorativos; no importar librerías de iconos en runtime salvo esta convención Lucide + `IconSvg`.

---

## 14. Refined Agro — polish controlado

Evolución del estilo **clean**: misma paleta verde/agro, con feedback táctil y elevación sutil donde aporta claridad (no decoración).

| Aspecto | Operario (móvil) | Admin (web) |
|---------|------------------|-------------|
| Feedback | `active:`, fondo en ítem activo | `md:hover:` en filas, nav, cards |
| Elevación | Dock con blur + sombra suave | Cards `shadow-sm` → `md:hover:shadow-md` |
| Motion | ≤200ms; sin entrada en listas | Drawer Alpine ≤300ms |
| Referencia | `avicore-ui/references/patrones-mobile-operario.md` | `avicore-ui/references/patrones-web-admin.md` |

**Inspiración TALL** (patrones, no dependencias): [`INDICE-TALL-REFERENCIA.md`](INDICE-TALL-REFERENCIA.md).

**Prohibido:** gradientes Soft UI, `scale-102` en hover, glass global, UI kits como dependencia composer/npm.
