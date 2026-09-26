# ORQ-01 — Baseline y contrato activo

**Estado:** VERIFICADA · **Fecha:** 2026-09-26 · **Revisión base:** `b2623ef`

## Objetivo

Registrar la línea base y confirmar que la entrada AviCore no depende de documentos de otro proyecto (SIPROD, Windsurf, `docs/PROJECT-CONTEXT`).

## Resultado observable

La orquestación AviCore usa solo `AGENTS.md`, `portal/`, `.cursor/` y el plan en `portal/planes/`. No hay carpetas ni punteros activos a SIPROD, `.windsurf` ni `docs/`.

## Inventario de entrada

| Fuente | Estado | Notas |
|---|---|---|
| `AGENTS.md` | OK | Apunta a portal + `.cursor/skills/README.md` |
| `portal/contenido/desarrollo/contexto.html` | OK | Contrato humano canónico |
| `.cursor/commands/avicore-architect-direct.md` | OK | Flujo slash; sin matriz duplicada |
| `.cursor/README.md` | OK | Inventario 15 skills |
| `.cursor/skills/README.md` | OK | Única tabla mensaje → skill |
| `README.md` (raíz) | OK | Empezar aquí → portal y skills |
| `docs/` | Ausente (esperado) | Migrado a `portal/`; script anti-drift lo verifica |
| `.windsurf/` | Ausente | No existe en el repo |
| SIPROD / `PROJECT-CONTEXT` | Ausente | Solo mencionado en diagnóstico como hallazgo de sesión externa |
| `.agents/` | Ausente | No devolvió archivos en inventario |

## Comandos y exit codes

| Comando | Resultado |
|---|---|
| `pnpm run check:agent-docs` | Exit 0 — 15 skills, punteros alineados |
| `git rev-parse --short HEAD` | `b2623ef` |
| Búsqueda `SIPROD\|windsurf\|PROJECT-CONTEXT` en repo activo | Solo en `portal/planes/` (diagnóstico histórico), no en config ejecutable |

## Archivos afectados en este cierre

- `portal/planes/CHECKPOINT.md` (nuevo)
- `portal/planes/evidencias/ORQ-01-baseline-contrato.md` (este archivo)
- `portal/contenido/desarrollo/plan-entrega.html` (enlace ORQ-12)
- `portal/js/site.nav.js` (nav)
- `portal/planes/PLAN-MAESTRO-ENTREGA-AVICORE.md` (estado ORQ-01)
- `portal/CHANGELOG.md`

## Referencia canónica

No cambió contrato de producto. Checkpoint y evidencia viven en `portal/planes/` (capa humana).

## Riesgos / bloqueos

Ninguno para ORQ-01.

## Siguiente ID

**ORQ-02** — Reconciliar capacidades (roadmap, producto, arquitectura vs código real).
