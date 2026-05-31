---
name: avicore-evolucion-tooling
description: Crea o actualiza skills, reglas Cursor, mensajes reutilizables y docs de tooling AviCore cuando el flujo del agente cambió o aparece un workflow recurrente sin skill. Lo aplica el arquitecto de forma interna al cerrar tareas.
disable-model-invocation: true
---

# AviCore — Evolución del tooling (interno)

Leer `docs/cursor/05-evolucion-skills-y-docs.md`. El usuario no invoca este skill.

## Actualizar skill existente

1. Leer `.cursor/skills/<nombre>/SKILL.md` y comparar con lo ejecutado en la sesión.
2. Si hay brecha: editar `SKILL.md` (conciso, &lt; 500 líneas).
3. Actualizar `docs/cursor/03-skills-avicore.md` si cambió descripción o cuándo usarlo.
4. `docs/CHANGELOG.md` — línea `[cursor]`.

## Crear skill nuevo

Solo si no encaja en mensajes 1–5 ni skills actuales y es recurrente.

```text
.cursor/skills/avicore-<nombre-kebab>/
  SKILL.md
```

Frontmatter obligatorio:

```yaml
---
name: avicore-<nombre-kebab>
description: [tercera persona] Qué hace y cuándo usarlo. Incluir términos de búsqueda.
disable-model-invocation: true
---
```

- `name` = nombre de carpeta, minúsculas, guiones.
- Registrar en `03-skills-avicore.md`.
- Actualizar mapeo en `.cursor/commands/avicore-architect-direct.md` si el arquitecto debe enrutar ahí.
- No crear mensaje HTML salvo que el usuario necesite plantilla dedicada.

## Documentación y reglas

- Producto: fuente maestra según `docs/README.md`.
- Tooling: `00-configuracion-cursor.md`, `00-contexto.md`, reglas `.cursor/rules/*.mdc`.
- Referencias nuevas: `docs/reference/` + regla `avicore-docs-referencia.mdc` si aplica.

Incluir en el cierre natural del chat (1 oración) si hubo cambios de tooling — p. ej. «También actualicé el skill X».
