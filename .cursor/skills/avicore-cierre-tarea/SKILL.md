---
name: avicore-cierre-tarea
description: Verifica y actualiza documentación AviCore según archivos o módulos indicados por el usuario. Mensaje 4 — pasada dedicada; si el paso 5 del architect ya actualizó la fuente maestra, solo verificar gaps.
disable-model-invocation: true
---

# AviCore — Documentación (mensaje 4)

Pasada **dedicada** de alineación documental. Si en la misma sesión el architect ya actualizó la fuente maestra (paso 5), verificar que no falte nada — no duplicar.

El usuario lista **archivos de código o módulo** que motivan la revisión.

## Base obligatoria

- `docs/README.md` — tabla «Regla de una sola fuente maestra» (mapa canónico negocio→05, pantallas→02, etc.)
- `docs/CHANGELOG.md` — si cambió contrato documental

## Qué documento actualizar

Usar **solo** la tabla en `docs/README.md` § «Regla de una sola fuente maestra». Revisar docs pertinentes a los archivos listados por el usuario.

## Salida

Tabla `documento | alineado | acción` · docs tocados · línea para PR: `Verifiqué documentación y actualicé [...] según el cambio.`

Un cambio conceptual → **un** documento maestra; no duplicar.
