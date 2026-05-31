# Changelog — Contrato documental AviCore

Solo cambios que alteran **reglas, esquema, pantallas, permisos o arquitectura acordada**.  
Formato: `YYYY-MM-DD — [área] descripción breve — archivos tocados`

---

## 2026-05-30

- **[estructura]** Reorganización documental: contexto (`00-contexto.md`), referencias BD/proyecto, README, CHANGELOG. — `docs/`, `AGENTS.md`, `.cursor/rules/`
- **[cursor]** Skills 15→11 (fusionados ui, auditoria, cierre-tarea, architect). `00-contexto` unifica agente. `02-mensajes` índice conciso → skills. `01-agente` reducido a índice. — `.cursor/skills/`, `docs/cursor/`
- **[cursor]** Revisión de estructura: README sin duplicado, flujo en `00-configuracion-cursor`, alineación nombres skills. — `docs/README.md`, `docs/cursor/`
- **[cursor]** `01-avicore-agente-permanente.md` → `01-indice-agente.md`; comando y skill `avicore-architect` con flujo obligatorio en 5 pasos. — `docs/cursor/`, `.cursor/commands/`, `.cursor/skills/avicore-architect/`
- **[cursor]** Plantillas en `02-avicore-mensajes-reutilizables.html` (acordeones, copiar, expandir/colapsar). `.md` como índice. — `docs/cursor/`
- **[cursor]** Mensajes en lenguaje natural; arquitecto enruta skills internos (sin `@skill` del usuario). Prep Git en tarea nueva. — `02-*.html`, comando architect, `03-skills`
- **[cursor]** Mensajes reducidos a 5: tarea general, auditoría, correcciones, documentación, commit/PR. — `02-avicore-mensajes-reutilizables.*`
- **[cursor]** Mensajes 2–4: base de reglas/docs explícita + sección para adjuntar archivos (@rutas). — `02-*.html`, skills auditoria y cierre-tarea
- **[referencia]** `estandares-codigo.md` + regla `.mdc`. Auditoría mensaje 2 solo lectura con tabla %. PR mensaje 5 con MCP GitHub y plantilla. — `docs/reference/`, skills git-pr y auditoria
- **[cursor]** Modo COMPRESIÓN CAVERMAN opcional (`avicore-modo-caverman.mdc`, `04-modo-respuesta-caverman.md`, `.cursorrules` índice). — `.cursor/rules/`, architect
- **[cursor]** Gobernanza evolución skills/docs (`05-evolucion-skills-y-docs.md`, skill `avicore-evolucion-tooling`). Architect pasos 5–7: docs producto + tooling + cierre. — `.cursor/`, `docs/cursor/`
- **[cursor]** Context7 en paso 2 del architect-direct; fila actualizar comando en paso 6; MCP ordenado en `00-configuracion-cursor`. — architect, agente-permanente, `00-contexto`
- **[cursor]** Modo respuesta clara en chat (`avicore-modo-respuesta-clara.mdc`, `06-modo-respuesta-clara.md`); architect-direct obliga lenguaje llano; Caverman opcional de nuevo (`alwaysApply: false`). — `.cursor/rules/`, architect

---

## Plantilla (copiar al registrar)

```text
## YYYY-MM-DD

- **[negocio|bd|pantalla|permiso|arquitectura|tiempo-real|reporte|demo]** Qué cambió y por qué. — `archivo1`, `archivo2`
```
