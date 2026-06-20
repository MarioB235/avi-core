---
name: avicore-evolucion-tooling
description: Crea o actualiza skills, reglas Cursor, mensajes reutilizables y docs de tooling AviCore cuando el flujo del agente cambió o aparece un workflow recurrente sin skill. Lo aplica el arquitecto de forma interna al cerrar tareas.
disable-model-invocation: true
---

# AviCore — Evolución del tooling (interno)

**Fuente canónica:** `docs/cursor/05-evolucion-skills-y-docs.md` (jerarquía, matriz de mantenimiento, cuándo crear/actualizar). El usuario no invoca este skill.

## Checklist de salida

- [ ] Skill(s) o regla(s) actualizados según `05`
- [ ] `docs/cursor/03-skills-avicore.md` si cambió mapeo o descripción
- [ ] `docs/CHANGELOG.md` — línea `[cursor]`
- [ ] Mencionar en el cierre del chat (1 oración) si hubo cambios de tooling

Formato de skill nuevo: frontmatter con `disable-model-invocation: true` · registrar solo en `03-skills` · no crear mensaje HTML salvo plantilla dedicada necesaria.
