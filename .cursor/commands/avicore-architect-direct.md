---
name: avicore-architect-direct
description: Orquestador AviCore — el usuario escribe en lenguaje natural; vos elegís el skill interno y ejecutás el flujo completo
---

# AviCore Architect Direct

Sos el arquitecto/desarrollador de AviCore. El usuario **no invoca `@skills`**; interpretás su mensaje, leés el `SKILL.md` interno que corresponda y ejecutás el flujo.

## Modo chat (obligatorio en respuestas)

Regla `avicore-modo-respuesta-clara` · guía `docs/cursor/06-modo-respuesta-clara.md`.

- **Prosa natural** en párrafos cortos; sin informes con muchos títulos, listas ni tablas de resumen.
- **Mantener completos:** código, comandos, tabla auditoría (mensaje 2), plantilla PR (mensaje 5).
- **Cierre paso 7:** 1–2 oraciones integradas; mencionar docs/tooling solo si cambió algo relevante.

Modo **Caverman** (opcional): menos palabras, misma prosa. **No activar Clara + Caverman** a la vez (`04-modo-respuesta-caverman.md`).

## Flujo obligatorio

### 1 — Preparación (si el usuario inicia una tarea nueva)

Cuando el mensaje indica comenzar o arrancar:

1. `git status` y rama actual.
2. Si está en `main`/`master`: `git pull` (si hay remoto) y `git checkout -b [tipo]/[nombre-descriptivo]`.
3. No commit, push ni PR salvo mensaje **5** con autorización explícita.

### 2 — Orientación (leer antes de codear)

| Paso | Acción |
|------|--------|
| 2.1 | `docs/00-contexto.md` + mapa de lectura |
| 2.2 | `docs/reference/estructura-base-datos.md` si hay datos |
| 2.3 | `docs/reference/estructura-proyecto.md` si hay código |
| 2.4 | Docs del mapa en `00-contexto` según módulo |

**Dudas de stack:** primero docs AviCore; si no alcanza, MCP `user-context7` (máx. 3 consultas). No para negocio AviCore.

### 3 — Clasificar y elegir skill (interno)

Inferir alcance: `feature` | `fix` | `refactor` | `docs` | `style` | `chore` | `hotfix`

Plantillas usuario: `docs/cursor/02-avicore-mensajes-reutilizables.html`

| Mensaje | Skill(s) |
|---------|----------|
| **1 — Pedir una tarea** | Ver enrutamiento abajo |
| **2 — Revisar calidad** | `avicore-auditoria` (solo lectura; tabla % con Negocio/Permisos/Código/UI/Tests/Arquitectura; **sin modificar**) |
| **3 — Corregir auditoría** | `avicore-auditoria` (maximizar cumplimiento; tests en verde; listo para PR; **sin commit**) |
| **4 — Alinear documentación y tooling** | `avicore-cierre-tarea` — docs, skills y coherencia del flujo (ver paso 5 y `05-evolucion-skills-y-docs.md`) |
| **5 — Subir cambios / PR** | `avicore-git-pr` (**solo** con autorización explícita) |

**Enrutamiento mensaje 1** (elegir **uno** principal; combinar solo si la tarea lo pide):

| Intención del usuario | Skill principal | Combinar solo si… |
|----------------------|-----------------|-------------------|
| Módulo o CRUD completo | `avicore-nuevo-modulo` | — |
| Solo pantalla / UI (web u operario) | `avicore-ui` | No es módulo nuevo entero |
| Solo migración, modelo, seeders | `avicore-modelo-datos` | No incluye pantallas nuevas |
| WebSockets, dashboard en vivo | `avicore-tiempo-real` | — |
| Seeders demo, escenarios | `avicore-datos-demo` | — |
| PDF / Excel | `avicore-reportes` | — |
| Manifest, instalación PWA | `avicore-pwa` | — |

Si el mensaje 1 pide inicio de tarea: paso 1 antes de implementar.

### 4 — Implementar

Sin confirmaciones intermedias salvo bloqueo crítico. Reglas en `docs/00-contexto.md` (multiempresa, Policies, Services/Actions, anulación lógica).

### 5 — Documentación de producto

Si el cambio alteró contrato (reglas, pantallas, esquema, permisos, arquitectura): actualizar **una** fuente maestra según `docs/README.md` + línea en `docs/CHANGELOG.md`. **Proactivo** al cerrar la tarea — no esperar solo al mensaje 4.

**Mensaje 4 vs paso 5:** el paso 5 actualiza docs **durante** la implementación si cambió el contrato. El mensaje 4 es una **revisión dedicada** (otra sesión, muchos archivos, o duda de alineación). Si en la misma sesión ya actualizaste la fuente maestra en el paso 5, el mensaje 4 solo verifica gaps — no duplicar trabajo.

### 6 — Evolución del tooling

Solo si hubo **desvío real** respecto a un skill o flujo documentado (`docs/cursor/05-evolucion-skills-y-docs.md`, skill `avicore-evolucion-tooling`):

| Situación | Acción |
|-----------|--------|
| Flujo ejecutado ≠ `SKILL.md` | Actualizar skill + `03-skills-avicore.md` |
| Workflow recurrente sin skill | Crear skill + registrar + mapeo aquí |
| Nueva convención transversal | Regla `.mdc` o `docs/reference/` + CHANGELOG |
| Cambió este comando o MCP | Actualizar comando + `00-configuracion-cursor.md` |

**No** crear skill ni tocar reglas por tareas puntuales. No pedir permiso salvo cambio grande (nuevo mensaje HTML, reestructurar skills).

### 7 — Cierre

Último párrafo en lenguaje natural — qué quedó listo, docs/tooling si cambió, siguiente paso. Ver `06-modo-respuesta-clara.md`.

## Referencia

`docs/cursor/01-indice-agente.md` · `docs/cursor/00-configuracion-cursor.md`
