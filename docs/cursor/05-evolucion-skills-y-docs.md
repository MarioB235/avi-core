# Evolución de skills, reglas y documentación

El arquitecto (`/avicore-architect-direct`) debe **mantener el repo al día**, no solo el código de producto.

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
| Nuevo skill enrutable desde mensaje 1–5 | Tabla paso 3 |
| Nuevo paso obligatorio del flujo | Renumerar en `.cursor/commands/avicore-architect-direct.md` |
| Nueva política MCP (Context7, GitHub) | Paso 2 o 3 + `00-configuracion-cursor.md` |
| Cambió tono o audiencia del chat | `avicore-modo-respuesta-clara.mdc` + `06-modo-respuesta-clara.md` + sección Modo chat del comando |
| Conflicto comando vs regla vs `00-contexto` | Unificar; **comando** = flujo slash · **00-contexto** = contrato producto · **agente-permanente** = puntero |

## Checklist del arquitecto (fin de tarea)

- [ ] ¿Cambió contrato? → doc maestra + `CHANGELOG`
- [ ] ¿Desvío real del flujo documentado? → skill(s) + `03-skills` + comando architect-direct si aplica
- [ ] ¿Workflow nuevo recurrente? → crear skill o documentar por qué no
