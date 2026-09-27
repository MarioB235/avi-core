# Checkpoint AviCore — ejecución del plan

Archivo persistente para retomar sesiones sin depender del historial del chat.  
Plan maestro: [PLAN-MAESTRO-ENTREGA-AVICORE.md](PLAN-MAESTRO-ENTREGA-AVICORE.md).

## Estado actual

| Campo | Valor |
|---|---|
| Revisión | 2026-09-26 |
| Base git | cambios locales EST-05 → CAP-06 en `feature/emp-01-alta-empresa` |
| Rama | `feature/emp-01-alta-empresa` |
| Siguiente ID | **CAP-07** (recomendado) u **ORQ-05** |
| En curso | Ninguno |
| Tests | **746** total · **746** OK · 2554 aserciones (2026-09-26) |
| Build | Pint OK |
| `check:agent-docs` | OK |
| Bloque SEG | **Cerrado** (SEG-01 → SEG-12) |
| Bloque EMP | **Cerrado** (EMP-01 → EMP-08) |
| Bloque EST | **Cerrado** (EST-01 → EST-10) |
| Bloque CAP (parcial) | CAP-01 → CAP-06 verificadas |
| Alcance v1 | Operación avícola completa; comercial/reparto etapa 2 |

## Últimos cierres

| ID | Estado | Fecha | Evidencia |
|---|---|---|---|
| CAP-06 | VERIFICADA | 2026-09-26 | [evidencias/CAP-06-vacunacion-basica.md](evidencias/CAP-06-vacunacion-basica.md) |
| CAP-05 | VERIFICADA | 2026-09-26 | [evidencias/CAP-05-alimento-entregado.md](evidencias/CAP-05-alimento-entregado.md) |
| CAP-04 | VERIFICADA | 2026-09-26 | [evidencias/CAP-04-descarte-aves.md](evidencias/CAP-04-descarte-aves.md) |
| CAP-03 | VERIFICADA | 2026-09-26 | [evidencias/CAP-03-muertes-transaccionales.md](evidencias/CAP-03-muertes-transaccionales.md) |
| CAP-02 | VERIFICADA | 2026-09-26 | [evidencias/CAP-02-huevos-punta-a-punta.md](evidencias/CAP-02-huevos-punta-a-punta.md) |

## Mensaje para continuar

```text
/avicore-architect-direct
Ejecutá portal/planes/PLAN-MAESTRO-ENTREGA-AVICORE.md.
Leé portal/planes/CHECKPOINT.md.
Empezá por CAP-07 (idempotencia transversal) o ORQ-05 si priorizás tooling.
No hagas commit, push, PR ni despliegue sin autorización explícita.
```
