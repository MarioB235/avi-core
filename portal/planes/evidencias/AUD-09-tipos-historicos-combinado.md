# AUD-09 — Tipos históricos (`combinado`)

**ID:** AUD-09  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/aud-historial-operario`

## Objetivo

Definir el tratamiento del tipo legado `combinado` para que anulación y saldo de aves usen el mismo camino que muertes/descarte, sin capturas nuevas ni corrección UI.

## Hallazgo

`AnularRegistroOperativoAction` solo restauraba `aves_actuales` para tipos `muertes` y `descarte`; filas `combinado` con muertes/descarte no devolvían saldo.

## Implementación

| Área | Cambio |
|------|--------|
| Soporte | `RegistroOperativoImpactoAves` — cantidad a restaurar (muertes + descarte en combinado; respeta `cero_confirmado`) |
| Anulación | `AnularRegistroOperativoAction` usa el helper unificado |
| Modelo | `RegistroOperativo::esMortalidad()` incluye `descarte_aves` en combinado |
| Enum | `RegistroOperativoTipo::esLegado()` / `admiteCapturaNueva()`; label «Combinado (legado)» |
| Contrato | `tipos-historicos.md`, `reglas.md` §10.6, `esquema-bd.md` |

## Decisión

- `combinado` = legado, solo lectura en historial.
- Sin altas nuevas ni corrección (anular y recargar por tipo).
- Migración a registros separados: deferida post-MVP.

## Prueba de cierre

```bash
php artisan test tests/Feature/Auditoria/TipoCombinadoLegadoTest.php tests/Unit/Support/RegistroOperativoImpactoAvesTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- `TipoCombinadoLegadoTest` + `RegistroOperativoImpactoAvesTest`: **5/5** OK
- Suite completa: **851/851** OK · 2982 aserciones · Pint OK · `check:agent-docs` OK

## Siguiente ID

**MOV-01** — Modelo mínimo de movimientos (bloque MOV).
