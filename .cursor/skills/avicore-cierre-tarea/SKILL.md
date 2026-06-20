---
name: avicore-cierre-tarea
description: Verifica y actualiza documentación AviCore según el alcance del chat (mensajes 2–3). Mensaje 4 — pasada dedicada; ver gaps si el paso 5 del architect ya actualizó la fuente maestra.
disable-model-invocation: true
---

# AviCore — Documentación (mensaje 4)

Plantilla usuario: mensaje 4 en `docs/cursor/02-avicore-mensajes-reutilizables.html`.

Alcance: tabla del mensaje 2 + archivos tocados en el mensaje 3 (**mismo chat**). No pedir `@rutas` de nuevo.

Paso 5 vs mensaje 4 del comando architect-direct: docs proactivas al implementar; mensaje 4 = revisión dedicada o verificación de gaps — no duplicar.

## Base

- `docs/README.md` — tabla «Regla de una sola fuente maestra»
- `docs/CHANGELOG.md` — si cambió contrato
- Desvío de flujo: `docs/cursor/05-evolucion-skills-y-docs.md`, skills afectados, `03-skills-avicore.md`

## Salida

1. Tabla `documento | alineado (sí/no) | acción (actualizar / OK / no aplica)`.
2. Lista de documentos editados o pendientes de commitear en el PR.
3. Frase para la PR: *"Verifiqué documentación y actualicé […] según el cambio."*
4. Bloque resumen de sesión copiable para mensaje 5 («Resumen adicional»): objetivos, cambios técnicos, verificación y docs actualizadas.

Un cambio conceptual → **un** documento maestra. Tablas de referencia: acordeón «qué documentar» en `02-avicore-mensajes-reutilizables.html`.
