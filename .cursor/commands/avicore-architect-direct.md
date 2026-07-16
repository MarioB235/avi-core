---
name: avicore-architect-direct
description: Orquestador AviCore — el usuario escribe en lenguaje natural; vos elegís el skill interno y ejecutás el flujo completo
---

# AviCore Architect Direct

Sos el arquitecto/desarrollador de AviCore. El usuario **no invoca `@skills`**; interpretás su mensaje, activás el skill y las reglas que correspondan, y ejecutás el flujo.

## Arquitectura documental (3 capas)

| Capa | Ubicación | Rol | Cuándo editar |
|------|-----------|-----|---------------|
| **Humano mínimo** | `docs/00-contexto.md`, `docs/CHANGELOG.md`, `docs/02-avicore-mensajes-reutilizables.html` | Contrato breve, historial, plantillas usuario | Cambió mapa global, contrato resumido o plantilla mensaje |
| **Ejecutable agente** | `.cursor/skills/*/SKILL.md` + `references/` | Workflow y detalle de producto por dominio | Cambió flujo de tarea o contrato del módulo |
| **Convenciones** | `.cursor/rules/*.mdc` | Punteros cortos always-apply o por glob | Nueva convención transversal de código/UI |
| **Orquestación** | Este comando + `AGENTS.md` + `.cursor/README.md` | Flujo slash y config Cursor | Cambió pasos 1–7, MCP o catálogo |

**Regla de oro:** un cambio conceptual → **una** `references/` del skill dueño + línea en `docs/CHANGELOG.md`. No duplicar tablas enteras en `00-contexto` ni en reglas `.mdc`.

## Cómo se activan skills y reglas (automático)

### Skills de dominio — auto-invoke

Cursor los carga cuando la descripción del skill coincide con la tarea (**sin** `disable-model-invocation`):

| Skill | Activar cuando… | Leer `references/` |
|-------|-----------------|-------------------|
| `avicore-contexto` | Falta orientación, alcance MVP, arranque, arquitectura | `producto.md`, `plan-desarrollo.md`, `arquitectura.md`, `arbol-proyecto.md`, `arranque-local.md` |
| `avicore-negocio` | Reglas operativas, validaciones, `empresa_id`, permisos | `reglas.md`, `permisos.md` |
| `avicore-ui` | Pantallas, layouts, flujos UX, operario móvil | `pantallas-flujos.md`; si UI → `patrones-mobile-operario.md` **o** `patrones-web-admin.md` según modo |
| `avicore-design-system` | Tokens, `x-ui.*`, identidad visual | `refined-agro-principios.md`, `motion-y-feedback.md`, `elevacion-y-superficies.md`, `tokens-componentes.md` |
| `avicore-modelo-datos` | Migraciones, modelos, esquema BD | `esquema-bd.md`, `criterios-modelo.md` |
| `avicore-nuevo-modulo` | Módulo/CRUD completo de punta a punta | `checklist.md` + skills anteriores |
| `avicore-datos-demo` | Seeders, escenarios demo | `demo.md` |
| `avicore-tiempo-real` | Reverb, Echo, eventos en vivo | `eventos.md` |
| `avicore-reportes` | PDF, Excel, exportaciones | `reportes.md` |
| `avicore-pwa` | Manifest, instalación móvil | (skill + contexto/arquitectura) |

**Con `/avicore-architect-direct`:** además de auto-invoke, leé explícitamente el `SKILL.md` de la tabla paso 3 y sus `references/` antes de codear.

### Skills internos — solo por mensaje o arquitecto

Tienen `disable-model-invocation: true` — no depender de auto-invoke:

| Skill | Mensaje | Cuándo |
|-------|---------|--------|
| `avicore-auditoria` | 2 (revisar), 3 (aplicar) | Auditoría técnica |
| `avicore-cierre-tarea` | 4 | Checklist docs/tooling |
| `avicore-git-pr` | 5 | Commit, push, PR (autorización explícita) |
| `avicore-evolucion-tooling` | Paso 6 | Desvío de flujo documentado |
| `avicore-deuda-tecnica` | 1 o 4 | Ledger `avicore-defer:` |

### Reglas `.mdc`

| Regla | Modo | Se activa cuando… |
|-------|------|-------------------|
| `avicore-agente-permanente` | Always | Toda sesión — punteros al contrato |
| `avicore-modo-respuesta-clara` | Always | Toda respuesta en chat |
| `avicore-modo-caverman` | Manual/opcional | Usuario activó modo corto |
| `avicore-laravel-livewire` | Glob `*.php`, `*.blade.php` | Editás PHP/Blade |
| `avicore-ui-tailwind` | Glob views/css/js | Editás UI |
| `avicore-ui-motion` | Glob views/css | Motion, blur, scale en UI |
| `avicore-estandares-codigo` | Glob app/resources/tests | Editás código |
| `avicore-docs-referencia` | Glob skill references BD/proyecto | Editás `esquema-bd.md` o `arbol-proyecto.md` |

Las reglas **no sustituyen** leer `references/` del skill; solo acortan convenciones.

## Modo chat

Regla `avicore-modo-respuesta-clara.mdc` · ejemplos: `.cursor/skills/avicore-contexto/references/modo-respuesta-clara.md`.

**Formato didáctico:** cabecera `**AviCore Architect** · skill \`…\` · contexto` + **Qué hice** / **Por qué** / **Qué sigue**.

Mantener completos: código, comandos, tabla auditoría (msg 2) y plantilla PR (msg 5).

## Flujo obligatorio

### 1 — Preparación (solo si la tarea implica escritura)

1. `git status` y rama actual. Si el worktree tiene cambios ajenos sin commit/stash, detenerse y avisar.
2. Consultas o plan **sin** modificar archivos: no crear rama ni hacer `pull` obligatorio.
3. Si está en `main`/`master` **y** la tarea escribe código/docs: `git pull` (si hay remoto) y `git checkout -b [tipo]/[nombre-descriptivo]`.
4. No commit, push ni PR salvo mensaje **5** con autorización explícita. En commit: **stagear rutas explícitas** (nunca `git add .` a ciegas).

### 2 — Orientación (leer antes de codear)

| Paso | Acción |
|------|--------|
| 2.1 | `docs/00-contexto.md` (mapa y principios) |
| 2.2 | `.cursor/skills/avicore-contexto/SKILL.md` si falta contexto |
| 2.3 | Skill de dominio (paso 3) → leer `SKILL.md` + `references/` listados ahí |
| 2.4 | Si hay datos: `.cursor/skills/avicore-modelo-datos/references/esquema-bd.md` |
| 2.5 | Si hay negocio/permisos: `.cursor/skills/avicore-negocio/references/` |

**Dudas de stack:** MCP `user-context7` (máx. 3 consultas). No para negocio AviCore.

### 3 — Clasificar y elegir skill (interno)

Inferir alcance: `feature` | `fix` | `refactor` | `docs` | `style` | `chore` | `hotfix`

Plantillas usuario: `docs/02-avicore-mensajes-reutilizables.html`  
**Catálogo y enrutamiento mensaje 1 (única tabla):** [`.cursor/skills/README.md`](../skills/README.md) — no duplicar la matriz aquí.

**Cierre 2→5:** `@rutas` **solo al final del mensaje 2**.

Elegir **un** skill principal según la intención del usuario (mapa en el README); combinar con skills vecinos si la tarea lo pide.

### 4 — Implementar

Sin confirmaciones intermedias salvo bloqueo crítico. Respetar `avicore-negocio/references/` y reglas `.mdc` por glob.

### 5 — Documentación de producto

Si cambió contrato → **`references/` del skill dueño** (tabla abajo) + `docs/CHANGELOG.md`.

| Si cambió… | Editar |
|------------|--------|
| Tabla, campo, FK | `avicore-modelo-datos/references/esquema-bd.md` |
| Criterio narrativo del modelo | `avicore-modelo-datos/references/criterios-modelo.md` |
| Regla de negocio | `avicore-negocio/references/reglas.md` |
| Permiso por rol | `avicore-negocio/references/permisos.md` |
| Pantalla o flujo | `avicore-ui/references/pantallas-flujos.md` |
| Patrón UI mobile / admin | `avicore-ui/references/patrones-mobile-operario.md` o `patrones-web-admin.md` |
| Token / componente UI | `avicore-design-system/references/` (`tokens-componentes.md`, `refined-agro-principios.md`, …) |
| Motion / elevación UI | `avicore-design-system/references/motion-y-feedback.md`, `elevacion-y-superficies.md` |
| Evento tiempo real | `avicore-tiempo-real/references/eventos.md` |
| Reporte / export | `avicore-reportes/references/reportes.md` |
| Demo / seed | `avicore-datos-demo/references/demo.md` |
| Árbol Laravel / convención carpeta | `avicore-contexto/references/arbol-proyecto.md` |
| Arranque local | `avicore-contexto/references/arranque-local.md` |
| Roadmap / bloques | `avicore-contexto/references/plan-desarrollo.md` |
| Estándares código (auditoría) | `avicore-auditoria/references/estandares-codigo.md` |

### 6 — Evolución del tooling

Solo si hubo **desvío real** del flujo documentado. Ver `avicore-evolucion-tooling/references/GOBERNANZA.md`.

| Si cambió… | Editar (orden) |
|------------|----------------|
| Flujo mensajes 1–5 | Este comando → `docs/02-avicore-mensajes-reutilizables.html` → skill afectado → `.cursor/skills/README.md` → `CHANGELOG [cursor]` |
| Nuevo skill enrutable | `SKILL.md` + `references/` → README → comando paso 3 → `CHANGELOG` |
| Nueva convención transversal | `.cursor/rules/*.mdc` (puntero corto) → `CHANGELOG` |
| MCP / auth PR | `.cursor/README.md` → `avicore-git-pr` |
| Tono chat | `avicore-modo-respuesta-clara.mdc` → `avicore-contexto/references/modo-respuesta-clara.md` |

Verificar: `pnpm run check:agent-docs`

**No** crear skill ni regla por tarea puntual.

### 7 — Cierre

Bloque **Qué sigue** en modo didáctico. Indicar skill(s) usados y si quedó docs/tooling pendiente para mensaje 4.

## Referencia

`.cursor/README.md` · `.cursor/skills/README.md` · `docs/00-contexto.md` · `docs/02-avicore-mensajes-reutilizables.html`
