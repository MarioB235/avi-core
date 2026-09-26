# Checkpoint AviCore — ejecución del plan

Archivo persistente para retomar sesiones sin depender del historial del chat.  
Plan maestro: [PLAN-MAESTRO-ENTREGA-AVICORE.md](PLAN-MAESTRO-ENTREGA-AVICORE.md).

## Estado actual

| Campo | Valor |
|---|---|
| Revisión | 2026-09-26 |
| Base git | `b2623ef` + cambios locales SEG-02–12 |
| Rama | `feature/admin-resumen-galpon-select` |
| Siguiente ID | **EMP-01** (recomendado) o **ORQ-05** |
| En curso | Ninguno |
| Tests | **588** total · **588** OK · 2116 aserciones (2026-09-26) |
| Build | Exit 0 · Pint OK |
| `check:agent-docs` | OK (15 skills; incluye invariantes plan 1b) |
| Bloque SEG | **Cerrado** (SEG-01 → SEG-12) |
| Alcance v1 | Operación avícola completa; comercial/reparto etapa 2 |

## Contrato activo (entrada única)

| Prioridad | Archivo | Rol |
|---|---|---|
| 1 | `portal/contenido/desarrollo/contexto.html` | Contrato humano |
| 2 | `.cursor/commands/avicore-architect-direct.md` | Flujo slash |
| 3 | `.cursor/skills/README.md` | Catálogo y enrutamiento |
| 4 | `AGENTS.md` | Puntero raíz |
| 5 | `portal/planes/PLAN-MAESTRO-ENTREGA-AVICORE.md` | Cola de trabajo v1 |

## Decisiones pendientes

D01, D03–D07 — ver plan maestro §4. **D02 cerrada** (SEG-07).

## Últimos cierres

| ID | Estado | Fecha | Evidencia |
|---|---|---|---|
| SEG-10 | VERIFICADA | 2026-09-26 | [evidencias/SEG-10-recuperacion-sesiones.md](evidencias/SEG-10-recuperacion-sesiones.md) |
| SEG-11 | VERIFICADA | 2026-09-26 | [evidencias/SEG-11-separar-demo.md](evidencias/SEG-11-separar-demo.md) |
| SEG-12 | VERIFICADA | 2026-09-26 | [evidencias/SEG-12-superficies-tecnicas.md](evidencias/SEG-12-superficies-tecnicas.md) |

## Mensaje para continuar

```text
/avicore-architect-direct
Ejecutá portal/planes/PLAN-MAESTRO-ENTREGA-AVICORE.md.
Leé portal/planes/CHECKPOINT.md y el diagnóstico enlazado.
Empezá por EMP-01 (alta empresa real) o ORQ-05 si priorizás tooling.
No hagas commit, push, PR ni despliegue sin autorización explícita.
Al cerrar sesión actualizá este checkpoint y la evidencia del plan.
```
