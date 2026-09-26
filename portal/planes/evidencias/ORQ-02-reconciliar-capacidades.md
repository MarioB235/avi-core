# ORQ-02 — Reconciliar capacidades

**Estado:** VERIFICADA · **Fecha:** 2026-09-26

## Objetivo

Roadmap, producto y arquitectura alineados con código, rutas y tests. Sin contradicciones PWA/admin; previews no cuentan como módulo.

## Contradicciones resueltas

| Antes | Después |
|-------|---------|
| `producto.md`: Administrativo = mismo alcance que Dueño | Alineado a `permisos.md`: roles diferenciados |
| `arquitectura.md`: PWA pendiente | PWA hecho MVP; Reverb/Echo pendiente |
| `estrategia-implementacion.md`: «admin no existe» | Admin usuarios, estructura, Inicio, Resumen documentados |
| `plan-desarrollo.md` corte 2026-08-01 | Corte 2026-09-26 + puntero a matriz única |
| PWA en tabla orden §2 y bloque 7 duplicado | Una sola lectura en `estado-capacidades.md` |
| Comercial/Reparto sin etiqueta | **Preview etapa 2** — excluidos de progreso v1 |

## Fuente única creada

`avicore-contexto/references/estado-capacidades.md` — matriz implementado / parcial / preview / pendiente con rutas.

## Verificación

| Comando / acción | Resultado |
|------------------|-----------|
| `pnpm run check:agent-docs` | Exit 0 |
| `routes/web.php` vs matriz | Coincide (admin, operario, reparto preview) |
| `permisos.md` vs `producto.md` §6 | Sin contradicción |

## Siguiente ID

**ORQ-03** — Incorporar modo ejecutar-plan en comando (checkpoint, selección por ID).
