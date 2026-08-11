---
name: avicore-contexto
description: Contexto transversal AviCore — producto, arquitectura, plan de desarrollo, arranque local y árbol del proyecto. Usar al iniciar cualquier tarea, cuando falte orientación del MVP, o antes de leer skills de dominio.
---

# AviCore — Contexto

Punto de entrada de documentación de producto tras `portal/contenido/desarrollo/contexto.html`.

## Leer primero

| Necesidad | Referencia |
|-----------|------------|
| Visión y alcance MVP | [`references/producto.md`](references/producto.md) |
| Mercado Uruguay / SMA / coeficientes | [`references/mercado-uruguay.md`](references/mercado-uruguay.md) |
| Stack y principios | [`references/arquitectura.md`](references/arquitectura.md) |
| Roadmap y bloques | [`references/plan-desarrollo.md`](references/plan-desarrollo.md) |
| Árbol Laravel | [`references/arbol-proyecto.md`](references/arbol-proyecto.md) |
| Entorno local | [`references/arranque-local.md`](references/arranque-local.md) |
| Despliegue Laravel Cloud | [`references/deploy-laravel-cloud.md`](references/deploy-laravel-cloud.md) |
| Modo chat (ejemplos) | [`references/modo-respuesta-clara.md`](references/modo-respuesta-clara.md) |

## Mapa rápido → skill de dominio

| Tarea | Skill |
|-------|-------|
| Pantallas / UI | `avicore-ui` |
| Tokens / componentes base | `avicore-design-system` |
| Reglas de negocio / permisos | `avicore-negocio` |
| BD / migraciones | `avicore-modelo-datos` |
| Módulo completo | `avicore-nuevo-modulo` |
| Demo / seeders | `avicore-datos-demo` |
| Tiempo real | `avicore-tiempo-real` |
| Reportes | `avicore-reportes` |
| PWA | `avicore-pwa` |

Catálogo completo: [`.cursor/skills/README.md`](../README.md)

## Contrato de ejecución

Ejecutar de punta a punta sin «¿procedo?». Un cambio conceptual → `references/` del skill dueño + línea en `portal/CHANGELOG.md`.
