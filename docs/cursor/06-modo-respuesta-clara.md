# Modo respuesta clara y didáctico (chat)

Respuestas **naturales** y **entendibles** para quien está aprendiendo. Regla: `.cursor/rules/avicore-modo-respuesta-clara.mdc` (`alwaysApply: true` en este repo).

## Principio

Contar qué pasó, por qué y qué sigue **en prosa didáctica**, con transparencia sobre qué skill interno activó el orquestador. El agente ejecuta igual (docs, skills, MCP, pasos 1–7 del architect); solo cambia **cómo se escribe el chat**.

## Anatomía de una buena respuesta

1. **Cabecera operativa** (1 línea): rol + skill interno + contexto (mensaje 1–5, rama, o «solo consulta»).
2. **Qué hice** — párrafo corto con la acción concreta.
3. **Por qué** — párrafo con el criterio (negocio o técnico).
4. **Qué sigue** — párrafo con el siguiente paso para el usuario.
5. **Artefactos técnicos** (si aplican): código, comandos, tabla auditoría o plantilla PR — completos y copiables.

Longitud orientativa: cabecera + **3 párrafos** + artefactos; menos si la tarea fue trivial.

Plantilla de cabecera:

```text
**AviCore Architect** · skill `avicore-ui` · mensaje 1
```

## Ejemplo: antes vs después

Contexto: reorganizar el hero del admin home en móvil.

### Antes (solo prosa, sin transparencia ni estructura)

```text
Ya está todo en GitHub: https://github.com/MarioB235/avi-core

Armé un README en la raíz que explica qué es AviCore…

Sobre tu pregunta de las pull requests…

Abrí el repo en el navegador y confirmá que el README se ve bien…
```

Funciona, pero el novato no sabe qué agente/skill actuó ni dónde está el «por qué» vs el «qué sigue».

### Después (objetivo didáctico)

```text
**AviCore Architect** · skill `avicore-ui` · mensaje 1

**Qué hice** Reorganicé el hero del admin home usando el componente `home-hero` y moví los KPIs a una grilla de dos columnas en móvil.

**Por qué** En pantallas chicas los números quedaban apilados y costaba comparar galpones; el diseño en `02-pantallas-y-flujos` pide jerarquía visual clara antes de los accesos rápidos.

**Qué sigue** Corré `npm run dev`, abrí `/admin` en el teléfono o DevTools responsive y contame si el hero se lee bien; si está ok, en el mismo chat podés mandar el mensaje 2 para auditar.
```

## Reglas por tipo de mensaje

| Mensaje | Cabecera | Cuerpo |
|---------|----------|--------|
| 1 — implementación | skill principal (+ secundario si aplica) · mensaje 1 | 3 bloques + código/comandos |
| 2 — auditoría | skill `avicore-auditoria` · mensaje 2 | Tabla `%` completa + 3 bloques breves |
| 3 — correcciones | skill según corrección · mensaje 3 | 3 bloques: qué cambió vs msg 2 |
| 4 — docs | skill `avicore-cierre-tarea` · mensaje 4 | 3 bloques; gaps o «sin gaps» |
| 5 — PR | skill `avicore-git-pr` · mensaje 5 | 3 bloques intro + plantilla PR completa |
| Consulta / plan | skill inferido o «consulta» | 3 bloques; sin implementar salvo pedido |

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
| **Clara / didáctico** (default) | Cabecera + 3 bloques en prosa |
| **Caverman** (opcional) | Cabecera mínima; sin bloques etiquetados; menos palabras |

Para solo Caverman técnico: `avicore-modo-caverman.mdc` con `alwaysApply: true` y `avicore-modo-respuesta-clara.mdc` con `alwaysApply: false` (no recomendado si el usuario está aprendiendo).

## Convivencia con architect-direct

El flujo interno (docs, skills, MCP, pasos 1–7) no cambia. El paso 7 usa el bloque **Qué sigue** en lugar de un cierre suelto duplicado. El usuario no usa `@skills`; la cabecera muestra el skill interno para transparencia didáctica.
