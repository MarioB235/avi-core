---
name: avicore-auditoria
description: Audita archivos AviCore en modo solo lectura (tabla % por archivo) o aplica correcciones tras auditoría. Mensaje 2 = solo analizar; mensaje 3 = corregir y dejar listo para PR.
disable-model-invocation: true
---

# AviCore — Auditoría

Flujo y plantilla usuario: mensajes 2 y 3 en `docs/02-avicore-mensajes-reutilizables.html` · catálogo: `.cursor/skills/README.md`.

## Modo revisar (mensaje 2) — SOLO LECTURA

**No modificar código.**

1. Base: [`references/estandares-codigo.md`](references/estandares-codigo.md) + `references/` del skill dueño según alcance.
2. Auditar solo archivos con `@rutas` al final del mensaje 2.
3. Si hay código de app, incluir tests relacionados en `tests/`.

| Dimensión | Contraste principal |
|-----------|---------------------|
| Negocio | `avicore-negocio/references/` |
| Permisos | `avicore-negocio/references/permisos.md` |
| Código | `references/estandares-codigo.md` |
| UI | `avicore-design-system/references/` |
| Tests | Par en `tests/` |
| Arquitectura | `avicore-contexto/references/arquitectura.md` |

**Salida obligatoria:** resumen + tabla clasificadora + brechas + plan ≤5 acciones.

## Modo aplicar-correcciones (mensaje 3)

Alcance: tabla del mensaje 2 en **este mismo chat**. Orden: bugs → tests → deuda → resto.

**Verificación:** `php artisan test` · `npm run build` si front · `vendor/bin/pint` si PHP.

Sin commit/push/PR. Si altera contrato → anotar para mensaje 4.
