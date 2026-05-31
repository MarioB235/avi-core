---
name: avicore-auditoria
description: Audita archivos AviCore en modo solo lectura (tabla % por archivo) o aplica correcciones tras auditoría. Mensaje 2 = solo analizar; mensaje 3 = corregir.
disable-model-invocation: true
---

# AviCore — Auditoría

## Modo revisar (mensaje 2) — SOLO LECTURA

Mensaje usuario: `docs/cursor/02-avicore-mensajes-reutilizables.html` (mensaje 2) · catálogo: `docs/cursor/03-skills-avicore.md`.

**No modificar código.** No aplicar correcciones. Solo analizar.

1. Leer base: `docs/reference/estandares-codigo.md` + docs del mensaje (`05`, `06`, `02`, `03`, `04`, `reference`, `07`, `08`, `11` según alcance).
2. Auditar **únicamente** los archivos listados o adjuntados por el usuario.
3. Por cada archivo, estimar **% de cumplimiento global** (0–100) contra reglas de negocio, permisos, arquitectura, estándares de código y UI si aplica.

**Salida obligatoria:**

```markdown
## Resumen ejecutivo
[2–4 líneas]

## Tabla clasificadora de cumplimiento
| Archivo | Cumplimiento % | Negocio | Permisos | Código/UI | Brecha principal |
|---------|----------------|---------|----------|-----------|------------------|
| ruta/archivo | 85 | OK | OK | Parcial | [recomendación concreta para esa fila] |
```

- Los % por dimensión (Negocio, Permisos, Código/UI): OK / Parcial / No / N/A.
- **Toda recomendación accionable** va en **Brecha principal**; no agregar lista aparte de «mejoras sugeridas».
- Si falta un archivo en la lista del usuario, pedirlo solo si bloquea el análisis.

## Modo aplicar-correcciones (mensaje 3)

Modificar según la **tabla clasificadora** del mensaje 2: filas con Parcial/No y lo indicado en Brecha principal. Prioridad: bugs → seguridad → multiempresa → validaciones → auditoría → responsive. Sin refactors fuera de la tabla.

**Salida:** cambios aplicados, archivos, verificación · **resumen breve del trabajo** al cerrar.
