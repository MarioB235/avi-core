# AviCore — Skills (13 internos · 5 mensajes usuario)

**Usuario:** solo los **5 mensajes** en [`02-avicore-mensajes-reutilizables.html`](02-avicore-mensajes-reutilizables.html) + `/avicore-architect-direct`.

**Agente:** aplica los `SKILL.md` según el mensaje y el detalle del usuario.

**Única tabla mensaje → skill** — el comando no duplica esta tabla; solo mantiene el enrutamiento por intención del mensaje 1.

| Mensaje | Skills que puede activar |
|---------|-------------------------|
| 1 Pedir una tarea | `nuevo-modulo`, `ui`, `design-system`, `modelo-datos`, `tiempo-real`, `datos-demo`, `reportes`, `pwa` |
| 2 Revisar calidad | `avicore-auditoria` (revisar) |
| 3 Corregir auditoría | `avicore-auditoria` (aplicar-correcciones) |
| 4 Alinear documentación | `avicore-cierre-tarea` (pasada dedicada; mapa en `docs/README.md`) |
| 5 Subir cambios / PR | `avicore-git-pr` |
| (interno) Evolución tooling | `avicore-evolucion-tooling` — crear/actualizar skills y reglas |
| (interno) Ledger deuda | `avicore-deuda-tecnica` — escanear `avicore-defer:` (mensaje 1 si el usuario pide ledger, o pasada msg 4) |

## Enrutamiento mensaje 1 (por intención)

| Intención | Skill principal | Combinar con |
|-----------|-----------------|--------------|
| Módulo o CRUD completo | `avicore-nuevo-modulo` | — |
| Pantalla / UI web u operario | `avicore-ui` | `avicore-design-system` si toca tokens o componentes base |
| Sistema de diseño / tokens / layouts transversales | `avicore-design-system` | `avicore-ui` si es pantalla concreta |
| Migración, modelo, seeders | `avicore-modelo-datos` | — |
| WebSockets, dashboard en vivo | `avicore-tiempo-real` | — |
| Seeders demo, escenarios | `avicore-datos-demo` | — |
| PDF / Excel | `avicore-reportes` | — |
| PWA, manifest, instalación | `avicore-pwa` | — |
| Ledger deuda técnica (`avicore-defer`) | `avicore-deuda-tecnica` | — |

## Documentación por mensaje

| Mensaje | Skill interno | Documentación y archivos a mantener alineados |
|---------|---------------|-----------------------------------------------|
| 1 — Tarea | Según tarea → tablas arriba | [`00-contexto.md`](../00-contexto.md), [`README.md`](../README.md), docs del módulo (`02`–`12`, `reference/`), [`CHANGELOG.md`](../CHANGELOG.md) si cambia contrato; [`05-evolucion-skills-y-docs.md`](05-evolucion-skills-y-docs.md) si cambia tooling |
| 2 — Auditoría (leer) | `avicore-auditoria` (revisar) | [`estandares-codigo.md`](../reference/estandares-codigo.md) (incl. tests, arquitectura, tags YAGNI), [`README.md`](../README.md), dimensiones Negocio/Permisos/Código/UI/Tests/Arquitectura; salida: resumen + tabla + brechas + tests + plan ≤5 acciones |
| 3 — Corregir auditoría | `avicore-auditoria` (aplicar) | Tabla y plan del mensaje 2 en el mismo chat; orden bugs→tests→deuda; maximizar %; tests en verde; pint/build según alcance |
| 4 — Documentación y tooling | `avicore-cierre-tarea` | Alcance del chat (2+3); [`README.md`](../README.md), [`CHANGELOG.md`](../CHANGELOG.md), [`05-evolucion-skills-y-docs.md`](05-evolucion-skills-y-docs.md), `03-skills-avicore.md`, skills en `.cursor/skills/`; salida: tabla + frase PR + resumen para msg 5 |
| 5 — Git / PR | `avicore-git-pr` | Chat completo 2–4 + diff; plantilla en el skill; auth: [`00-configuracion-cursor.md`](00-configuracion-cursor.md) |

Gobernanza y matriz de mantenimiento: [`05-evolucion-skills-y-docs.md`](05-evolucion-skills-y-docs.md)

Listado completo: `.cursor/skills/*/SKILL.md`
