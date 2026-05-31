---
name: avicore-auditoria
description: Audita archivos AviCore en modo solo lectura (tabla % por archivo) o aplica correcciones tras auditoría. Mensaje 2 = solo analizar; mensaje 3 = corregir.
disable-model-invocation: true
---

# AviCore — Auditoría

## Modo revisar (mensaje 2) — SOLO LECTURA

**No modificar código.** No aplicar correcciones. Solo analizar.

1. Leer base: `docs/reference/estandares-codigo.md` + docs del mensaje (`05`, `06`, `02`, `03`, `04`, `reference`, `07`, `08`, `11` según alcance).
2. Auditar **únicamente** los archivos listados o adjuntados por el usuario.
3. Por cada archivo, estimar **% de cumplimiento global** (0–100) contra reglas de negocio, permisos, arquitectura, estándares de código y UI si aplica.

**Salida obligatoria:**

```markdown
## Resumen ejecutivo
[2–4 líneas]

## Tabla de cumplimiento
| Archivo | Cumplimiento % | Negocio | Permisos | Código/UI | Brecha principal |
|---------|----------------|---------|----------|-----------|------------------|
| ruta/archivo | 85 | OK | OK | Parcial | [una línea] |

## Plan sugerido (máx. 5 ítems, sin ejecutar)
1. ...
```

- Los % por dimensión (Negocio, Permisos, Código/UI) pueden ser: OK / Parcial / No / N/A.
- Si falta un archivo en la lista del usuario, pedirlo solo si bloquea el análisis.

## Modo aplicar-correcciones (mensaje 3)

Ahí sí modificar: solo archivos y hallazgos indicados. Prioridad: bugs → seguridad → multiempresa → validaciones → auditoría → responsive. Sin refactors grandes.

**Salida:** cambios aplicados, archivos, verificación.
