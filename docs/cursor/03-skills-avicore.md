# AviCore — Skills (13 internos · 5 mensajes usuario)

**Usuario:** solo los **5 mensajes** en [`02-avicore-mensajes-reutilizables.html`](02-avicore-mensajes-reutilizables.html) + `/avicore-architect-direct`.

**Agente:** aplica los `SKILL.md` según el mensaje y el detalle del usuario.

| Mensaje | Skills que puede activar |
|---------|-------------------------|
| 1 Pedir una tarea | `nuevo-modulo`, `ui`, `design-system`, `modelo-datos`, `tiempo-real`, `datos-demo`, `reportes`, `pwa` |
| 2 Revisar calidad | `avicore-auditoria` (revisar) |
| 3 Corregir auditoría | `avicore-auditoria` (aplicar-correcciones) |
| 4 Alinear documentación | `avicore-cierre-tarea` (pasada dedicada; mapa en `docs/README.md`) |
| 5 Subir cambios / PR | `avicore-git-pr` |
| (interno) Evolución tooling | `avicore-evolucion-tooling` — crear/actualizar skills y reglas |

## Documentación por mensaje

| Mensaje | Skill interno | Documentación y archivos a mantener alineados |
|---------|---------------|-----------------------------------------------|
| 1 — Tarea | Según tarea → tabla arriba | [`00-contexto.md`](../00-contexto.md), [`README.md`](../README.md), docs del módulo (`02`–`12`, `reference/`), [`CHANGELOG.md`](../CHANGELOG.md) si cambia contrato; [`05-evolucion-skills-y-docs.md`](05-evolucion-skills-y-docs.md) si cambia tooling |
| 2 — Auditoría (leer) | `avicore-auditoria` (revisar) | [`estandares-codigo.md`](../reference/estandares-codigo.md) (incl. tests y arquitectura), [`README.md`](../README.md), dimensiones Negocio/Permisos/Código/UI/Tests/Arquitectura |
| 3 — Corregir auditoría | `avicore-auditoria` (aplicar) | Tabla mensaje 2; maximizar %; tests en verde; pint/build según alcance |
| 4 — Documentación y tooling | `avicore-cierre-tarea` | [`README.md`](../README.md), [`CHANGELOG.md`](../CHANGELOG.md), [`05-evolucion-skills-y-docs.md`](05-evolucion-skills-y-docs.md), `03-skills-avicore.md`, skills en `.cursor/skills/` |
| 5 — Git / PR | `avicore-git-pr` | Plantilla en el skill; auth: [`00-configuracion-cursor.md`](00-configuracion-cursor.md) |

Gobernanza: [`05-evolucion-skills-y-docs.md`](05-evolucion-skills-y-docs.md)

Listado completo: `.cursor/skills/*/SKILL.md`
