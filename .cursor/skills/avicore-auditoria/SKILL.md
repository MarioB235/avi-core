---
name: avicore-auditoria
description: Audita archivos AviCore en modo solo lectura (tabla % por archivo) o aplica correcciones tras auditoría. Mensaje 2 = solo analizar; mensaje 3 = corregir y dejar listo para PR.
disable-model-invocation: true
---

# AviCore — Auditoría

Flujo y plantilla usuario: mensajes 2 y 3 en `docs/cursor/02-avicore-mensajes-reutilizables.html` · catálogo: `docs/cursor/03-skills-avicore.md`.

## Modo revisar (mensaje 2) — SOLO LECTURA

**No modificar código.**

1. Base: `docs/reference/estandares-codigo.md` + docs según alcance (mapa en `docs/README.md`).
2. Auditar solo archivos con `@rutas` al final del mensaje 2.
3. Si hay código de app, incluir tests relacionados en `tests/` (tabla o gap en Tests).
4. Por archivo: % global (0–100) y dimensiones OK / Parcial / No / N/A.

| Dimensión | Contraste principal |
|-----------|---------------------|
| Negocio | `docs/05`, flujos `docs/02` |
| Permisos | `docs/06`, Policies/Gates |
| Código | `estandares-codigo.md`, capas, multiempresa |
| UI | `docs/03`, `reference/sistema-diseno.md` |
| Tests | Par en `tests/`, comportamiento crítico |
| Arquitectura | `docs/07`, escalabilidad, capas |

**Salida obligatoria:**

```markdown
## Resumen ejecutivo
[2–4 líneas]

## Tabla clasificadora de cumplimiento
| Archivo | Cumplimiento % | Negocio | Permisos | Código | UI | Tests | Arquitectura | Brecha principal |
```

Recomendaciones accionables solo en **Brecha principal**. Tags opcionales de complejidad (`yagni:`, `shrink:`, `stdlib:`, `delete:`) según `estandares-codigo.md` § Simplificación — **después** de negocio/permisos/tests; nunca contradecir `00-contexto`.

## Modo aplicar-correcciones (mensaje 3)

Alcance: tabla del mensaje 2 en **este mismo chat**. No pedir `@rutas` de nuevo.

1. Corregir filas Parcial/No; maximizar % por brecha (no parche mínimo).
2. Tests significativos cuando Tests lo requiera.
3. Prioridad: bugs → seguridad → multiempresa → validaciones → tests → UI.
4. Sin refactors fuera del alcance auditado.

**Verificación:** `php artisan test` (verde) · `npm run build` si front · `vendor/bin/pint` si PHP.

Sin commit/push/PR. Si altera contrato → anotar para mensaje 4. Desvío de flujo → `05-evolucion-skills-y-docs.md`.

**Salida:** % estimado por archivo · verificaciones · resumen copiable para mensaje 5 («Resumen adicional»).
