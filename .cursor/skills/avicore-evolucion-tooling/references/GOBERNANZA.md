# Evolución de skills, reglas y documentación

El arquitecto (`/avicore-architect-direct`) debe **mantener el repo al día**, no solo el código de producto.

---

## Jerarquía canónica (una fuente por tema)

| Tema | Fuente canónica |
|------|-----------------|
| Flujo slash (7 pasos) | `.cursor/commands/avicore-architect-direct.md` |
| Mensaje → skill | `.cursor/skills/README.md` (única tabla) |
| Plantillas usuario | `portal/contenido/desarrollo/mensajes-reutilizables.html` (índice) · mensajes 1–5 en `portal/contenido/desarrollo/plantillas-cursor.html` (**sin** duplicar tablas de skills ni mapa de fuentes) |
| Procedimiento auditoría / PR / docs | Skills `avicore-auditoria`, `avicore-git-pr`, `avicore-cierre-tarea` |
| Gobernanza tooling | Este archivo |
| Contrato producto humano | `portal/contenido/desarrollo/contexto.html` |
| Detalle de producto | `.cursor/skills/*/references/` |
| Contrato UI Refined Agro | `avicore-design-system/references/refined-agro-principios.md` |

---

## Matriz de mantenimiento

| Si cambia… | Editar (en orden) |
|------------|-------------------|
| Flujo 2→5 / adjuntos / pasos del slash | Comando → `portal/contenido/desarrollo/plantillas-cursor.html` → skill afectado → `skills/README.md` → `CHANGELOG [cursor]` |
| Nuevo skill enrutable (msg 1–5) | `SKILL.md` → README → enrutamiento msg 1 en comando → `CHANGELOG` |
| Criterios auditoría | `avicore-auditoria/references/estandares-codigo.md` → skill auditoría → HTML msg 2 |
| Tono chat | `avicore-modo-respuesta-clara.mdc` → `avicore-contexto/references/modo-respuesta-clara.md` |
| MCP / auth PR | `.cursor/README.md` § MCP → skill `avicore-git-pr` |
| Contrato producto | `references/` del skill dueño → `CHANGELOG` |
| Anti-drift tooling | `pnpm run check:agent-docs` → `check-agent-docs-sync.cjs` + `check-skill-references.cjs` (enlaces + plantillas delgadas) |
| Impacto docs por diff | `pnpm run check:docs-impact` → `check-docs-impact.cjs` (sugerencias; mensaje 4 decide) |

---

## Cuándo actualizar un skill existente

| Señal | Acción |
|-------|--------|
| El flujo real difirió del `SKILL.md` | Actualizar pasos y salida |
| Nuevo archivo de referencia | Enlazar en el skill |
| Cambió mensaje reutilizable (HTML) | Alinear skill con plantilla |
| Nuevo criterio de negocio | Actualizar `avicore-negocio/references/` + skill UI si aplica |

Archivos a tocar: `.cursor/skills/<nombre>/SKILL.md` → `.cursor/skills/README.md` → `CHANGELOG.md` (línea `[cursor]`).

---

## Cuándo crear un skill nuevo

Crear `.cursor/skills/avicore-<nombre>/SKILL.md` si el workflow no encaja, se repetirá y tiene pasos claros.

Tras crear: registrar en `.cursor/skills/README.md` + `CHANGELOG.md`.

---

## Cuándo actualizar documentación de producto

Mapa canónico: tabla en `portal/contenido/desarrollo/contexto.html` § Regla de una sola fuente maestra.

Proactivo al cerrar cualquier tarea con cambio de contrato (paso 5 del architect).

**No** duplicar ese mapa ni el catálogo de skills en HTML de plantillas: el humano copia mensajes cortos; el detalle vive en `references/` y en `.cursor/skills/README.md`.

Antes del mensaje 4 / PR: `pnpm run check:docs-impact` (sugerencias por rutas del diff).

---

## Cuándo actualizar reglas `.mdc`

- Nueva convención transversal → nueva regla o ampliar existente.
- Conflicto entre reglas → unificar en `00-contexto` o skill `references/`.

---

## Checklist del arquitecto (fin de tarea)

- [ ] ¿Cambió contrato? → `references/` del skill dueño + `CHANGELOG`
- [ ] ¿Desvío real del flujo? → skill(s) + README + comando si aplica
- [ ] ¿Workflow nuevo recurrente? → crear skill o documentar por qué no
