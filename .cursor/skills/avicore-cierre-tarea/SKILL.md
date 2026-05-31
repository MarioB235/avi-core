---
name: avicore-cierre-tarea
description: Verifica y actualiza documentación AviCore según archivos o módulos indicados por el usuario, usando README y fuentes maestras. Mensaje 4.
disable-model-invocation: true
---

# AviCore — Documentación

El usuario lista **archivos de código o módulo** que motivan la revisión documental.

## Base obligatoria

- `docs/README.md` — tabla de fuentes maestras
- `docs/CHANGELOG.md` — si cambió contrato documental

## Qué documento actualizar (según cambio)

| Cambio | Fuente maestra |
|--------|----------------|
| Regla negocio | `05-reglas-de-negocio.md` |
| Pantalla/flujo | `02-pantallas-y-flujos.md` |
| UI | `03-guia-visual-ui.md` |
| Esquema | `reference/estructura-base-datos.md` |
| Permisos | `06-roles-y-permisos.md` |
| Carpetas/código | `reference/estructura-proyecto.md`, `07` |
| Tiempo real | `08-tiempo-real-eventos.md` |

Revisar solo docs pertinentes a los **archivos listados** por el usuario.

## Salida

Tabla `documento | alineado | acción` · lista de docs a commitear · línea para PR: `Verifiqué documentación y actualicé [...] según el cambio.`

Un cambio conceptual → **un** documento maestra; no duplicar.
