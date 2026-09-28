# Checkpoint AviCore — ejecución del plan

Archivo persistente para retomar sesiones sin depender del historial del chat.  
Plan maestro: [PLAN-MAESTRO-ENTREGA-AVICORE.md](PLAN-MAESTRO-ENTREGA-AVICORE.md).

## Estado actual

| Campo | Valor |
|---|---|
| Revisión | 2026-09-28 |
| Base git | MOV-03 en `feature/aud-historial-operario` |
| Rama | `feature/aud-historial-operario` |
| Siguiente ID | **MOV-04** |
| En curso | Ninguno |
| Tests | **867** total · **867** OK · 3021 aserciones (2026-09-28, post-MOV-03) |
| Build | Pint OK |
| `check:agent-docs` | OK |
| Bloque SEG | **Cerrado** (SEG-01 → SEG-12) |
| Bloque EMP | **Cerrado** (EMP-01 → EMP-08) |
| Bloque EST | **Cerrado** (EST-01 → EST-10) |
| Bloque CAP | **Cerrado** (CAP-01 → CAP-14) |
| Bloque AUD | **Cerrado** (AUD-01 → AUD-09) |
| Bloque MOV | **En curso** (MOV-01 ✓ · MOV-02 ✓ · MOV-03 ✓; MOV-04 siguiente) |
| Alcance v1 | Operación avícola completa; comercial/reparto etapa 2 |

## Cierre de sesión (P3 · 2026-09-28)

**MOV-03** verificado: saldo inicial enlazado al alta de lote sin doble incremento; entradas externas idempotentes. Evidencia en `portal/planes/evidencias/MOV-03-entrada-saldo-inicial.md`.

**Verificación:** `php artisan test` **867/867** OK · Pint OK · `check:agent-docs` OK.

**Pendiente humano (rama CAP):** mensaje **5** para `feature/cap-est-operacion-estructura` si aún no se hizo commit/PR del bloque CAP.

## Últimos cierres

| ID | Estado | Fecha | Evidencia |
|---|---|---|---|
| MOV-03 | VERIFICADA | 2026-09-28 | [evidencias/MOV-03-entrada-saldo-inicial.md](evidencias/MOV-03-entrada-saldo-inicial.md) |
| MOV-02 | VERIFICADA | 2026-09-28 | [evidencias/MOV-02-poblacion-lote-d01.md](evidencias/MOV-02-poblacion-lote-d01.md) |
| MOV-01 | VERIFICADA | 2026-09-28 | [evidencias/MOV-01-modelo-minimo.md](evidencias/MOV-01-modelo-minimo.md) |
| AUD-09 | VERIFICADA | 2026-09-28 | [evidencias/AUD-09-tipos-historicos-combinado.md](evidencias/AUD-09-tipos-historicos-combinado.md) |

## Mensajes para continuar

Plantillas **P1–P3** al final de `portal/contenido/desarrollo/plantillas-cursor.html`.  
Agregá `/avicore-architect-direct` en la primera línea del chat (no va en los bloques).

| Paso | Plantilla | Cuándo |
|------|-----------|--------|
| Inicio | **P1** | Primera vez o chat nuevo |
| Repetir | **P2** | Cada siguiente tarea del plan |
| Cierre bloque | **P3** | Antes de auditoría (msg 2→5) |
