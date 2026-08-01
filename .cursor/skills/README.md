# AviCore — Skills (15 internos · 5 mensajes usuario)

**Usuario:** solo los **5 mensajes** en [`docs/02-avicore-mensajes-reutilizables.html`](../../docs/02-avicore-mensajes-reutilizables.html) + `/avicore-architect-direct`.

**Agente:** aplica los `SKILL.md` según el mensaje y el detalle del usuario. Skills de dominio se auto-invocaban cuando la descripción coincide; skills internos requieren flujo mensajes 2–5 o arquitecto.

**Única tabla mensaje → skill** — el comando `/avicore-architect-direct` **enlaza** este README; no duplica la matriz de enrutamiento.

| Mensaje | Skills que puede activar |
|---------|-------------------------|
| 1 Pedir una tarea | `avicore-contexto`, `nuevo-modulo`, `ui`, `design-system`, `negocio`, `modelo-datos`, `tiempo-real`, `datos-demo`, `reportes`, `pwa` |
| 2 Revisar calidad | `avicore-auditoria` (revisar) |
| 3 Corregir auditoría | `avicore-auditoria` (aplicar-correcciones) |
| 4 Alinear documentación | `avicore-cierre-tarea` (pasada dedicada) |
| 5 Subir cambios / PR | `avicore-git-pr` |
| (interno) Evolución tooling | `avicore-evolucion-tooling` — crear/actualizar skills y reglas |
| (interno) Ledger deuda | `avicore-deuda-tecnica` — escanear `avicore-defer:` |

## Enrutamiento mensaje 1 (por intención)

| Intención | Skill principal | Combinar con |
|-----------|-----------------|--------------|
| Orientación / contexto | `avicore-contexto` | skill de dominio según tarea |
| Módulo o CRUD completo | `avicore-nuevo-modulo` | `avicore-negocio` |
| Pantalla / UI web u operario | `avicore-ui` | `avicore-design-system` si toca tokens o componentes base |
| Sistema de diseño / tokens / layouts | `avicore-design-system` | `avicore-ui` si es pantalla concreta |
| Reglas de negocio / permisos | `avicore-negocio` | `avicore-ui` o `avicore-modelo-datos` |
| Migración, modelo, seeders | `avicore-modelo-datos` | `avicore-negocio` si afecta reglas |
| WebSockets, dashboard en vivo | `avicore-tiempo-real` | — |
| Seeders demo, escenarios | `avicore-datos-demo` | — |
| PDF / Excel | `avicore-reportes` | — |
| PWA, manifest, instalación | `avicore-pwa` | — |
| Ledger deuda técnica (`avicore-defer`) | `avicore-deuda-tecnica` | — |

## Documentación por mensaje

| Mensaje | Skill interno | Fuentes a mantener alineadas |
|---------|---------------|------------------------------|
| 1 — Tarea | Según tarea → tablas arriba | `docs/00-contexto.md`, `references/` del skill dueño, `docs/CHANGELOG.md` si cambia contrato |
| 2 — Auditoría | `avicore-auditoria` | `avicore-auditoria/references/estandares-codigo.md`; salida: resumen + tabla + brechas + plan ≤5 acciones |
| 3 — Corregir | `avicore-auditoria` | Tabla y plan del mensaje 2; tests en verde |
| 4 — Docs y tooling | `avicore-cierre-tarea` | `docs/00-contexto.md`, `CHANGELOG.md`, `pnpm run check:docs-impact`, `GOBERNANZA.md` si desvío |
| 5 — Git / PR | `avicore-git-pr` | Chat 2–4 + diff; auth en `.cursor/README.md` |

Gobernanza: [`avicore-evolucion-tooling/references/GOBERNANZA.md`](avicore-evolucion-tooling/references/GOBERNANZA.md)

Listado completo: `.cursor/skills/*/SKILL.md`
