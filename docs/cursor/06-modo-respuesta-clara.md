# Modo respuesta clara (chat)

Respuestas **naturales** y **entendibles** para quien no es desarrollador o está aprendiendo. Regla: `.cursor/rules/avicore-modo-respuesta-clara.mdc` (`alwaysApply: true` en este repo).

## Principio

Contar qué pasó y qué sigue **en prosa**, como en una conversación. El agente ejecuta igual (docs, skills, MCP, pasos 1–7 del architect); solo cambia **cómo se escribe el chat**.

## Anatomía de una buena respuesta

1. **Apertura:** qué se hizo o qué respondés, en una oración clara.
2. **Desarrollo:** el porqué y los detalles importantes en 1–3 párrafos cortos.
3. **Recomendación** (si aplica): integrada en el texto, no en tabla.
4. **Cierre:** una frase con el siguiente paso concreto.

Longitud orientativa: **3–6 párrafos** para tareas medianas; menos si la tarea fue trivial.

## Ejemplo: antes vs después

Contexto: subir el repo a GitHub, crear README, usuario pregunta si conviene PR trabajando solo.

### Antes (evitar)

Muchos bloques, tabla de ventajas, lista duplicada y cierre etiquetado:

```text
Listo en GitHub
- README.md en la raíz
- .gitignore para .env
- Primer commit: 50 archivos

¿PR aunque trabajes solo?
| Ventaja | Qué significa |
| Historial claro | ... |

Flujo simple:
1. main = estable
2. rama feature/...

Resumen: Repo en GitHub...
Archivos: README.md, .gitignore
Verificación: push exitoso
Siguiente: Abrí el repo...
```

### Después (objetivo)

```text
Ya está todo en GitHub: https://github.com/MarioB235/avi-core

Armé un README en la raíz que explica qué es AviCore, en qué estado está el proyecto y dónde están la documentación y la config de Cursor. También dejé un .gitignore para que no se suban secretos (como el .env) ni carpetas pesadas de Laravel o Node cuando agregues código. El primer commit incluye la documentación completa y la carpeta .cursor/.

Sobre tu pregunta de las pull requests (PR): sí, conviene usarlas aunque trabajes solo. Cada cambio queda en su rama y podés revisar el diff en GitHub como si fuera otra persona; cuando sumes gente al equipo, el flujo ya está armado. Para este arranque subimos directo a main, que es lo normal en un repo vacío. De acá en adelante: rama feature/nombre → commits → PR → revisás → merge. Para docs chicas podés ir a main si querés; para código Laravel mejor siempre con PR (mensaje 5 del HTML).

Abrí el repo en el navegador y confirmá que el README se ve bien; el próximo cambio empezalo en una rama feature/….
```

## Qué sigue completo (no simplificar)

| Caso | Formato |
|------|---------|
| Auditoría (mensaje 2) | Tabla `%` por archivo |
| PR (mensaje 5) | Plantilla completa del skill `avicore-git-pr` |
| Código / comandos | Bloques copiables + una frase de contexto |

## Activación

Por defecto en AviCore: regla con `alwaysApply: true`. El comando `/avicore-architect-direct` lo refuerza en **Modo chat**.

## Convivencia con Caverman

| Modo | Efecto |
|------|--------|
| **Clara** (default) | Prosa natural, párrafos cortos |
| **Caverman** (opcional) | Menos palabras; **no** volver a listas/tablas de resumen |

Para solo Caverman técnico: `avicore-modo-caverman.mdc` con `alwaysApply: true` y `avicore-modo-respuesta-clara.mdc` con `alwaysApply: false` (no recomendado si el usuario es no técnico).

## Convivencia con architect-direct

El flujo interno (docs, skills, MCP, pasos 1–7) no cambia. El paso 7 ya no exige bloque `Resumen:` / `Archivos:`; el cierre va integrado en el último párrafo del chat.
