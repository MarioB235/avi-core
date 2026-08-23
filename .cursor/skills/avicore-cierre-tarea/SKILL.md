---
name: avicore-cierre-tarea
description: Verifica y actualiza documentación AviCore según el alcance del chat (mensajes 2–3). Mensaje 4 — pasada dedicada; ver gaps si el paso 5 del architect ya actualizó la fuente maestra.
disable-model-invocation: true
---

# AviCore — Documentación (mensaje 4)

Plantilla usuario: mensaje 4 en `portal/contenido/desarrollo/plantillas-cursor.html` (índice `portal/contenido/desarrollo/mensajes-reutilizables.html`).

Alcance: tabla del mensaje 2 + archivos tocados en el mensaje 3 (**mismo chat**). Si no hubo 2–3 → diff de la rama.

## Base

- `portal/contenido/desarrollo/contexto.html` — mapa de fuentes maestras (única tabla humana)
- `portal/CHANGELOG.md` — si cambió contrato
- Sugerencias por rutas: `pnpm run check:docs-impact` (no edita solo)
- Desvío de flujo: `avicore-evolucion-tooling/references/GOBERNANZA.md`, `.cursor/skills/README.md`
- Si tocaste `.cursor/` o plantillas: `pnpm run check:agent-docs`

## Salida

1. Tabla `documento | alineado (sí/no) | acción`.
2. Lista de `references/` editados.
3. **Versión semver:** ¿sube en esta sesión? (sí/no + valor si aplica) — ver plantilla «Versión del producto» en `plantillas-cursor.html`.
4. Frase para la PR.
5. Bloque resumen copiable para mensaje 5.

Un cambio conceptual → **`references/` del skill dueño** + `CHANGELOG.md`. No regenerar docs por cambios cosméticos. Subir `AVICORE_VERSION` solo en demo/piloto/release (no en cada PR).
