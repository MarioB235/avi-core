# Documentación AviCore

Índice del repositorio. El **código Laravel** vive en la raíz del repo; la verdad de contrato (negocio, BD, pantallas) está en `docs/`.

**Tres capas de documentación:** producto (`01`–`12` + `reference/` = fuentes maestras técnicas y de negocio) · tooling humano (`cursor/` = plantillas mensajes 1–5, catálogo skills, gobernanza) · ejecutable Cursor (`.cursor/` = reglas, skills internos, comando slash). Las reglas `.mdc` solo enlazan; no duplican `reference/`. En `reference/` solo se documenta lo implementado (migración, clase o UI); la visión futura va en `12-plan-de-desarrollo.md`.

## Punto de entrada (agentes y humanos)

| Archivo | Uso |
|---------|-----|
| [`00-contexto.md`](00-contexto.md) | **Contexto del proyecto** — stack, mapa de lectura, principios |
| [`reference/estructura-base-datos.md`](reference/estructura-base-datos.md) | **Esquema BD** — solo tablas con migración; relaciones (mantener al día) |
| [`reference/estructura-proyecto.md`](reference/estructura-proyecto.md) | **Árbol del repo** — carpetas y clases existentes |
| [`reference/arranque-local.md`](reference/arranque-local.md) | **Entorno local** — PostgreSQL, `.env`, migrate, serve |
| [`reference/estandares-codigo.md`](reference/estandares-codigo.md) | **Estándares de código** (auditoría y desarrollo) |
| [`reference/sistema-diseno.md`](reference/sistema-diseno.md) | **Sistema de diseño** — tokens Tailwind, componentes UI, accesibilidad |
| [`CHANGELOG.md`](CHANGELOG.md) | Cambios de contrato documental |
| [`cursor/02-avicore-mensajes-reutilizables.html`](cursor/02-avicore-mensajes-reutilizables.html) | **Plantillas copiables** mensajes 1–5 (abrir en navegador) |

Raíz del repo: [`AGENTS.md`](../AGENTS.md) (resumen para Cursor/CLI).

## Documentos de producto (`01`–`12`)

| Doc | Contenido | ¿Fuente maestra? | Mantenimiento |
|-----|-----------|------------------|---------------|
| [01-producto-avicore.md](01-producto-avicore.md) | Visión, alcance MVP | Alcance | Bajo |
| [02-pantallas-y-flujos.md](02-pantallas-y-flujos.md) | Pantallas, campos, flujos | **Pantallas/UX** | Alto |
| [03-guia-visual-ui.md](03-guia-visual-ui.md) | Identidad, Tailwind, componentes | **UI** | Medio |
| [04-modelo-de-datos.md](04-modelo-de-datos.md) | Criterios y reglas del modelo | Narrativa del modelo | Medio |
| [05-reglas-de-negocio.md](05-reglas-de-negocio.md) | Reglas operativas | **Negocio** | Muy alto |
| [06-roles-y-permisos.md](06-roles-y-permisos.md) | Roles y matriz de permisos | **Permisos** | Alto |
| [07-arquitectura-tecnica.md](07-arquitectura-tecnica.md) | Stack, principios, Livewire, Reverb | Arquitectura | Medio |
| [08-tiempo-real-eventos.md](08-tiempo-real-eventos.md) | Eventos y canales | Tiempo real | Medio |
| [09-reportes-exportaciones.md](09-reportes-exportaciones.md) | PDF/Excel MVP | Reportes | Medio |
| [10-datos-demo.md](10-datos-demo.md) | Seeders y escenarios demo | Demo | Medio |
| [11-checklist-modulos.md](11-checklist-modulos.md) | Definición de “módulo terminado” | Proceso | Bajo |
| [12-plan-de-desarrollo.md](12-plan-de-desarrollo.md) | Orden de implementación y estado de bloques | Roadmap | Medio (hitos) |

## Regla de una sola fuente maestra

| Si cambia… | Editar primero | Los demás solo… |
|------------|----------------|-----------------|
| Tabla, campo, FK, índice | `reference/estructura-base-datos.md` + línea en `CHANGELOG.md` | Referencian; `04` solo si cambia criterio narrativo |
| Estilo de código (PHP, Tailwind, etc.) | `reference/estandares-codigo.md` | Reglas `.mdc` al editar código |
| Regla de negocio | `05-reglas-de-negocio.md` | `02` si afecta pantalla |
| Pantalla o flujo | `02-pantallas-y-flujos.md` | `03` si afecta UI |
| Permiso por rol | `06-roles-y-permisos.md` | `02` si oculta acciones |
| Evento WebSocket | `08-tiempo-real-eventos.md` | `07` si cambia patrón técnico |
| Carpeta/clase estándar | `reference/estructura-proyecto.md` | `07` si cambia principio |
| Entorno local (PG, migrate, serve) | `reference/arranque-local.md` | README raíz resume; `07` enlaza |

**Gobernanza incremental (`01`–`12`):** detalle de pantallas (`02`), eventos (`08`), reportes (`09`) y datos demo ampliados (`10`) solo cuando exista ruta, UI o migración en el repo. Visión futura → [`12-plan-de-desarrollo.md`](12-plan-de-desarrollo.md) §13; al implementar un módulo, expandir su doc maestra en el mismo PR + línea en `CHANGELOG.md`.

## Cursor (tooling, no negocio)

| Ruta | Contenido |
|------|-----------|
| [cursor/00-configuracion-cursor.md](cursor/00-configuracion-cursor.md) | Setup Cursor, flujo del agente, reglas |
| [cursor/01-indice-agente.md](cursor/01-indice-agente.md) | Índice de enlaces del agente |
| [cursor/02-avicore-mensajes-reutilizables.html](cursor/02-avicore-mensajes-reutilizables.html) | **5 mensajes** con directiva ▶ y pasos explícitos (acordeón + copiar); acordeones de referencia: comandos, documentación pre-PR, usuarios demo |
| [cursor/03-skills-avicore.md](cursor/03-skills-avicore.md) | Catálogo de skills **internos** y docs por mensaje |
| [cursor/04-modo-respuesta-caverman.md](cursor/04-modo-respuesta-caverman.md) | Modo respuesta corto (opcional) |
| [cursor/05-evolucion-skills-y-docs.md](cursor/05-evolucion-skills-y-docs.md) | Cuándo crear/actualizar skills y docs |
| [cursor/06-modo-respuesta-clara.md](cursor/06-modo-respuesta-clara.md) | Chat en prosa natural (default) |
| `../.cursor/commands/avicore-architect-direct.md` | Flujo canónico del slash |
| `../.cursor/rules/` | Reglas always-apply y por glob |
| `../.cursor/skills/` | Workflows **internos** (el arquitecto los elige) |

## Orden de lectura por tipo de tarea

Ver sección **Mapa de lectura** en [`00-contexto.md`](00-contexto.md).
