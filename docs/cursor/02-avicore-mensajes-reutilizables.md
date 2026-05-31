# AviCore — Mensajes reutilizables (5)

**HTML (copiar):** [`02-avicore-mensajes-reutilizables.html`](02-avicore-mensajes-reutilizables.html)  
**Uso:** `/avicore-architect-direct` + mensaje. Sin `@skill`. Completar solo **«Aquí detallo…»** o el bloque final de cada plantilla.

| # | Mensaje | Cuándo | Skill interno (agente) |
|---|---------|--------|-------------------------|
| **1** | Cualquier tarea | Implementar, fix, módulo, UI, BD, tiempo real, demo, reportes, PWA, inicio con rama nueva | Según detalle: architect + el que corresponda |
| **2** | Auditoría | Solo lectura · tabla **% por archivo** · sin tocar código | `avicore-auditoria` (revisar) |
| **3** | Aplicar mejoras | Misma base + archivos + hallazgos | `avicore-auditoria` (aplicar-correcciones) |
| **4** | Actualizar documentación | README + fuente maestra según cambio + **archivos que motivan revisión** | `avicore-cierre-tarea` |
| **5** | Commit / push / PR | Git local + PR vía **MCP user-github** (plantilla en skill) | `avicore-git-pr` |

Estándares de código: `docs/reference/estandares-codigo.md`

El mensaje **1** cubre el 90 % del trabajo diario. Los mensajes **2–5** son fases del ciclo de vida de una tarea.
