# Documentación AviCore

Índice del repositorio de especificación del MVP. El código Laravel vivirá en la raíz; la verdad de contrato está en `docs/`.

## Punto de entrada (agentes y humanos)

| Archivo | Uso |
|---------|-----|
| [`00-contexto.md`](00-contexto.md) | **Contexto del proyecto** — stack, mapa de lectura, principios |
| [`reference/estructura-base-datos.md`](reference/estructura-base-datos.md) | **Esquema BD** — tablas, campos, relaciones (mantener al día) |
| [`reference/estructura-proyecto.md`](reference/estructura-proyecto.md) | **Árbol del repo y carpetas Laravel** |
| [`reference/estandares-codigo.md`](reference/estandares-codigo.md) | **Estándares de código** (auditoría y desarrollo) |
| [`CHANGELOG.md`](CHANGELOG.md) | Cambios de contrato documental |

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
| [12-plan-de-desarrollo.md](12-plan-de-desarrollo.md) | Orden de implementación | Roadmap | Bajo (hitos) |

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

## Cursor (tooling, no negocio)

| Ruta | Contenido |
|------|-----------|
| [cursor/00-configuracion-cursor.md](cursor/00-configuracion-cursor.md) | Setup Cursor, flujo del agente, reglas |
| [cursor/01-indice-agente.md](cursor/01-indice-agente.md) | Índice de enlaces del agente |
| [cursor/02-avicore-mensajes-reutilizables.html](cursor/02-avicore-mensajes-reutilizables.html) | 5 mensajes naturales (acordeón + copiar) |
| [cursor/02-avicore-mensajes-reutilizables.md](cursor/02-avicore-mensajes-reutilizables.md) | Índice de los 5 mensajes |
| [cursor/03-skills-avicore.md](cursor/03-skills-avicore.md) | Catálogo de skills **internos** |
| [cursor/04-modo-respuesta-caverman.md](cursor/04-modo-respuesta-caverman.md) | Modo respuesta corto (opcional) |
| [cursor/05-evolucion-skills-y-docs.md](cursor/05-evolucion-skills-y-docs.md) | Cuándo crear/actualizar skills y docs |
| [cursor/06-modo-respuesta-clara.md](cursor/06-modo-respuesta-clara.md) | Chat en prosa natural (default) |
| `../.cursor/commands/avicore-architect-direct.md` | Flujo canónico del slash |
| `../.cursor/rules/` | Reglas always-apply y por glob |
| `../.cursor/skills/` | Workflows **internos** (el arquitecto los elige) |

## Orden de lectura por tipo de tarea

Ver sección **Mapa de lectura** en [`00-contexto.md`](00-contexto.md).
