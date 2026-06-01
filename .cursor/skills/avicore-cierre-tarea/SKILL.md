---
name: avicore-cierre-tarea
description: Verifica y actualiza documentación AviCore según archivos o módulos indicados por el usuario. Mensaje 4 — pasada dedicada; si el paso 5 del architect ya actualizó la fuente maestra, solo verificar gaps.
disable-model-invocation: true
---

# AviCore — Documentación (mensaje 4)

Pasada **dedicada** de alineación documental. Si en la misma sesión el architect ya actualizó la fuente maestra (paso 5), verificar que no falte nada — no duplicar.

El usuario completa la sección **«Archivos modificados en esta sesión»** del mensaje 4 (`@rutas`, una por línea) o adjunta archivos al chat al pegar. Sin esa lista, pedir las rutas antes de editar docs.

## Base obligatoria

- `docs/README.md` — tabla «Regla de una sola fuente maestra» (mapa canónico negocio→05, pantallas→02, etc.)
- `docs/CHANGELOG.md` — si cambió contrato documental
- Mensaje usuario: `docs/cursor/02-avicore-mensajes-reutilizables.html` (mensaje 4) · catálogo: `docs/cursor/03-skills-avicore.md`

## Qué documento actualizar

Usar **solo** la tabla en `docs/README.md` § «Regla de una sola fuente maestra». Revisar docs pertinentes a los archivos listados por el usuario.

Si hubo desvío de flujo o convenciones: `docs/cursor/05-evolucion-skills-y-docs.md`, `.cursor/skills/` afectados, `docs/cursor/03-skills-avicore.md` y coherencia con `02-avicore-mensajes-reutilizables.html` / comando architect-direct.

## Salida

Tabla `documento | alineado (sí/no) | acción` · docs y skills tocados · **resumen copiable para PR** (mensaje 5, «Resumen adicional»).

Un cambio conceptual → **un** documento maestra; no duplicar.
