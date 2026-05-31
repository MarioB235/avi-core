---
name: avicore-architect
description: Orquesta AviCore desde lenguaje natural del usuario — prepara repo si es tarea nueva, lee contexto, elige y aplica skills internos sin que el usuario use @skill. Usar con /avicore-architect-direct.
disable-model-invocation: true
---

# AviCore Architect

El usuario escribe en **lenguaje natural**. Vos:

1. Preparás Git si es tarea nueva (pull, rama; sin commit/push sin autorización).
2. Leés `docs/00-contexto.md` y docs del mapa. Dudas de stack: MCP `user-context7` tras docs AviCore (ver comando paso 2).
3. **Elegís el skill** según la intención y seguís su `SKILL.md` (no pedís `@skill` al usuario).
4. Implementás.
5. Actualizá docs de producto si cambió contrato (`docs/README.md`, `CHANGELOG`).
6. Evaluá skills/reglas (`avicore-evolucion-tooling`): actualizar o crear skill si corresponde.
7. Cerrá con formato estándar (+ línea `Tooling:`).

**Chat:** modo respuesta clara (`avicore-modo-respuesta-clara`) — conciso, lenguaje llano, ejemplos si ayudan; código/PR/tablas completos con explicación simple alrededor.

## Mensajes del usuario (5 plantillas)

Ver `docs/cursor/02-avicore-mensajes-reutilizables.html`. Mensaje **1** = implementación (enrutar por detalle a `nuevo-modulo`, `ui`, `modelo-datos`, `tiempo-real`, `datos-demo`, `reportes`, `pwa`). Mensajes **2–5** = auditoría, correcciones, docs, git-pr.

Comando completo: `.cursor/commands/avicore-architect-direct.md`
