---
name: avicore-auditoria
description: Audita archivos AviCore en modo solo lectura (tabla % por archivo) o aplica correcciones tras auditoría. Mensaje 2 = solo analizar; mensaje 3 = corregir y dejar listo para PR.
disable-model-invocation: true
---

# AviCore — Auditoría

## Modo revisar (mensaje 2) — SOLO LECTURA

Mensaje usuario: `docs/cursor/02-avicore-mensajes-reutilizables.html` (mensaje 2) · catálogo: `docs/cursor/03-skills-avicore.md`.

**No modificar código.** No aplicar correcciones. Solo analizar.

1. Leer base: `docs/reference/estandares-codigo.md` + docs del mensaje según alcance (`05`, `06`, `02`, `03`, `04`, `07`, `08`, `11`, `reference/` — mapa en `docs/README.md`).
2. Auditar **únicamente** los archivos listados o adjuntados por el usuario.
3. Si la lista incluye código de aplicación (`app/`, `routes/`, Livewire, etc.), revisar también los **tests relacionados** en `tests/` (incluilos en la tabla o marcá gap en Tests si faltan).
4. Por cada archivo, estimar **% de cumplimiento global** (0–100) contra todas las dimensiones aplicables.

**Dimensiones (OK / Parcial / No / N/A):**

| Dimensión | Contraste principal |
|-----------|---------------------|
| Negocio | `docs/05-reglas-de-negocio.md`, flujos `docs/02` |
| Permisos | `docs/06-roles-y-permisos.md`, Policies/Gates |
| Código | `estandares-codigo.md`, capas, validaciones, multiempresa |
| UI | `docs/03`, `reference/sistema-diseno.md`, WCAG/touch operario |
| Tests | Par en `tests/`, cobertura de comportamiento crítico |
| Arquitectura | `docs/07`, escalabilidad, consultas, separación de capas |

**Salida obligatoria:**

```markdown
## Resumen ejecutivo
[2–4 líneas]

## Tabla clasificadora de cumplimiento
| Archivo | Cumplimiento % | Negocio | Permisos | Código | UI | Tests | Arquitectura | Brecha principal |
|---------|----------------|---------|----------|--------|-----|-------|--------------|------------------|
| ruta/archivo | 85 | OK | OK | Parcial | OK | No | OK | [recomendación concreta para esa fila] |
```

- **Toda recomendación accionable** va en **Brecha principal**; no agregar lista aparte de «mejoras sugeridas».
- Si falta un archivo (p. ej. test del feature auditado), pedirlo solo si bloquea el análisis; si no, registrar gap en Tests.

## Modo aplicar-correcciones (mensaje 3)

Objetivo: **subir el cumplimiento de cada fila auditada lo máximo posible** según la tabla del mensaje 2 y sus brechas — no solo un parche mínimo.

1. Corregir **todas** las filas con Parcial o No en cualquier dimensión; luego revisar filas OK/Parcial leves para optimizaciones concretas de la brecha.
2. **Tests:** implementar o ampliar tests cuando Tests sea Parcial/No o la brecha lo indique; tests significativos, no triviales.
3. Prioridad: bugs → seguridad → multiempresa → validaciones → tests críticos → UI/responsive → mantenibilidad.
4. Sin refactors grandes **fuera del alcance auditado** (archivos de la tabla o su par test/doc directo).

**Verificación obligatoria antes de cerrar:**

- `php artisan test` — **todo en verde**; corregir fallos hasta pasar.
- `npm run build` si hubo cambios en front, CSS o assets.
- `vendor/bin/pint` (o `vendor\bin\pint` en Windows) si tocaste PHP.

Si altera contrato documental → anotar para mensaje 4. Si el flujo difirió del skill → `docs/cursor/05-evolucion-skills-y-docs.md`.

**No** commit, push ni PR (mensaje 5). Dejar el working tree listo para que el usuario abra la PR.

**Salida:** cambios aplicados, % estimado tras corrección por archivo · verificaciones corridas · **resumen copiable para PR** (mensaje 5, «Resumen adicional»).
