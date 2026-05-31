# Modo respuesta — COMPRESIÓN CAVERMAN

Prosa mínima (~75% menos texto), misma precisión técnica. Regla del proyecto: `.cursor/rules/avicore-modo-caverman.mdc`.

---

## Opción A — Global (todos los proyectos)

1. Cursor → **Settings** (`Ctrl+,` / `Cmd+,`)
2. **Features** → **Rules for AI**
3. Pegar:

```text
Modo COMPRESIÓN CAVERMAN. Sin relleno ni cortesías. Comandos y código exactos.
Prosa ~75% más corta. Precisión técnica intacta. Español directo.
```

Para AviCore, preferir la **Opción B** (excepciones de auditoría y PR).

---

## Opción B — Solo AviCore (recomendado)

1. Abrir `.cursor/rules/avicore-modo-caverman.mdc`
2. Cambiar `alwaysApply: false` → `alwaysApply: true`
3. Commitear `.cursor/rules/` para el equipo

Incluye excepciones: tablas de auditoría, plantilla PR, código completo, cierre en bullets cortos.

---

## Opción C — Legacy `.cursorrules`

Algunas versiones leen un archivo `.cursorrules` en la raíz. En este repo la fuente canónica es `.cursor/rules/`. Si tu Cursor solo lee `.cursorrules`, copiá el contenido de `avicore-modo-caverman.mdc` (sin el frontmatter YAML).

---

## Convivencia con `/avicore-architect-direct`

El arquitecto sigue el flujo completo (docs, skills, MCP). **Por defecto** usa modo **respuesta clara** (`06-modo-respuesta-clara.md`). Caverman es opcional y no debe sacrificar claridad si ambos están activos.
