# Evolución de skills, reglas y documentación

El arquitecto (`/avicore-architect-direct`) debe **mantener el repo al día**, no solo el código de producto.

---

## Jerarquía canónica (una fuente por tema)

| Tema | Fuente canónica |
|------|-----------------|
| Flujo slash (7 pasos) | `.cursor/commands/avicore-architect-direct.md` |
| Mensaje → skill | `docs/cursor/03-skills-avicore.md` (única tabla) |
| Plantillas usuario | `docs/cursor/02-avicore-mensajes-reutilizables.html` |
| Procedimiento auditoría / PR / docs | Skills `avicore-auditoria`, `git-pr`, `cierre-tarea` |
| Gobernanza tooling | Este archivo (`05`) |
| Contrato producto | `docs/00-contexto.md` |

---

## Matriz de mantenimiento

| Si cambia… | Editar (en orden) |
|------------|-------------------|
| Flujo 2→5 / adjuntos / pasos del slash | Comando → HTML `02` → skill afectado → `03` → `CHANGELOG [cursor]` |
| Nuevo skill enrutable (msg 1–5) | `SKILL.md` → `03` (única tabla) → enrutamiento msg 1 en comando si aplica → `CHANGELOG` |
| Criterios auditoría | `estandares-codigo.md` → skill `avicore-auditoria` → HTML msg 2 |
| Tono chat | `avicore-modo-respuesta-clara.mdc` → `06` (ejemplos); comando: enlace breve |
| MCP / auth PR | `00-configuracion-cursor.md` § MCP → skill `git-pr` → una línea en `00-contexto` § Tooling |
| Contrato producto | Fuente maestra `docs/README.md` → `CHANGELOG` |
| Anti-drift tooling agente | `node scripts/check-agent-docs-sync.cjs` tras cambiar comando, `03`, `AGENTS` o `00-config` |

---

## Referencias externas

[`docs/ponytail/`](../ponytail/README.md) — repositorio vendoreado de **ideas** (deuda con comentarios, anti-drift, tags YAGNI). **No** es dependencia del agente AviCore: no instalar plugins Ponytail ni copiar skills/commands tal cual. Patrones adoptados en AviCore: `avicore-defer:`, tags en auditoría, `scripts/check-agent-docs-sync.cjs`. Origen: [DietrichGebert/ponytail](https://github.com/DietrichGebert/ponytail).

---

## Cuándo actualizar un skill existente

Hacerlo en la **misma sesión** si detectás:

| Señal | Acción |
|-------|--------|
| El flujo real difirió del `SKILL.md` | Actualizar pasos y salida |
| Nuevo archivo de referencia (`estandares-codigo`, MCP, etc.) | Enlazar en el skill |
| Cambió mensaje reutilizable (HTML) | Alinear skill con plantilla |
| Nuevo criterio de negocio que afecta un skill | Actualizar skill + doc maestra `05`/`02`/… |

Archivos a tocar: `.cursor/skills/<nombre>/SKILL.md` → `docs/cursor/03-skills-avicore.md` → `CHANGELOG.md` (línea `[cursor]`).

---

## Cuándo crear un skill nuevo

Crear `.cursor/skills/avicore-<nombre>/SKILL.md` si:

1. El workflow **no encaja** en los 5 mensajes + skills actuales.
2. Se repetirá (mismo tipo de tarea 2+ veces o el usuario lo define como recurrente).
3. Tiene pasos y salida **claros y acotados**.

**No crear** skill por tarea única; usar mensaje 1 + skill existente combinados.

Formato: ver skill interno `avicore-evolucion-tooling` y [Cursor Skills](https://cursor.com/docs/skills).

Tras crear:

- Registrar en `docs/cursor/03-skills-avicore.md`
- Solo añadir mensaje en `02-avicore-mensajes-reutilizables.html` si el usuario necesita plantilla nueva (evitar inflar los 5 mensajes sin necesidad)
- Entrada en `CHANGELOG.md`

---

## Cuándo actualizar documentación de producto

Mapa canónico: tabla **«Regla de una sola fuente maestra»** en `docs/README.md`.

Proactivo al cerrar **cualquier** tarea con cambio de contrato (paso 5 del architect), no solo con mensaje 4. El mensaje 4 = revisión dedicada o verificación de gaps.

---

## Cuándo actualizar reglas `.mdc`

- Nueva convención transversal (ej. estándares código) → nueva regla o ampliar existente.
- Conflicto entre reglas → unificar en una fuente (`00-contexto` o `reference/`).

---

## Cuándo actualizar `avicore-architect-direct`

| Señal | Acción |
|-------|--------|
| Nuevo skill enrutable desde mensaje 1–5 | `03-skills-avicore.md` + enrutamiento msg 1 en comando si aplica |
| Nuevo paso obligatorio del flujo | Renumerar en `.cursor/commands/avicore-architect-direct.md` |
| Nueva política MCP (Context7, GitHub) | Paso 2 o 3 + `00-configuracion-cursor.md` |
| Cambió tono o audiencia del chat | `avicore-modo-respuesta-clara.mdc` + `06-modo-respuesta-clara.md` + sección Modo chat del comando |
| Conflicto comando vs regla vs `00-contexto` | Unificar; **comando** = flujo slash · **00-contexto** = contrato producto · **agente-permanente** = puntero |

## Checklist del arquitecto (fin de tarea)

- [ ] ¿Cambió contrato? → doc maestra + `CHANGELOG`
- [ ] ¿Desvío real del flujo documentado? → skill(s) + `03-skills` + comando architect-direct si aplica
- [ ] ¿Workflow nuevo recurrente? → crear skill o documentar por qué no
