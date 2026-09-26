# ORQ-03 — Incorporar modo ejecutar plan

**Estado:** VERIFICADA · **Fecha:** 2026-09-26

## Objetivo

El comando `/avicore-architect-direct` incluye bucle de ejecución del plan maestro con checkpoint persistente; una sesión nueva retoma sin historial del chat.

## Resultado observable

- Sección **Modo ejecutar plan** en `.cursor/commands/avicore-architect-direct.md` (fuentes, bucle 9 pasos, autonomía acotada).
- Plantilla copiable **1b** en `portal/contenido/desarrollo/plantillas-cursor.html`.
- Punteros en `contexto.html`, `plan-entrega.html`, `avicore-agente-permanente.mdc`.
- Anti-drift: `check-agent-docs-sync.cjs` exige `CHECKPOINT.md` y plantilla 1b.

## Tareas ORQ relacionadas cerradas en la misma pasada

| ID | Qué se incorporó |
|----|------------------|
| ORQ-04 | Puerta de cierre en paso 4 del slash (tests/criterios antes de marcar hecho) |
| ORQ-07 | Worktree: baseline, colisiones reales; no bloqueo global por cambios ajenos |
| ORQ-08 | Plantilla 1b + mensaje en CHECKPOINT |

## Verificación

| Comando | Resultado |
|---------|-----------|
| `pnpm run check:agent-docs` | Exit 0 |

## Siguiente ID

**ORQ-05** (auditoría integral) o salto a **BAS-02** (primer fix de producto desbloqueado).
