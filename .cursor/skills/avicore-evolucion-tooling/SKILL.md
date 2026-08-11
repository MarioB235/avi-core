---
name: avicore-evolucion-tooling
description: Crea o actualiza skills, reglas Cursor, mensajes reutilizables y docs de tooling AviCore cuando el flujo del agente cambió o aparece un workflow recurrente sin skill. Lo aplica el arquitecto de forma interna al cerrar tareas.
disable-model-invocation: true
---

# AviCore — Evolución del tooling (interno)

**Fuente canónica:** [`references/GOBERNANZA.md`](references/GOBERNANZA.md)

## Checklist de salida

- [ ] Skill(s) o regla(s) actualizados según GOBERNANZA
- [ ] `.cursor/skills/README.md` si cambió mapeo
- [ ] `portal/CHANGELOG.md` — línea `[cursor]`
- [ ] `node scripts/check-agent-docs-sync.cjs` en verde

Formato de skill nuevo: registrar en README · no crear mensaje HTML salvo plantilla dedicada necesaria.
