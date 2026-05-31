# Modo respuesta clara (chat)

Respuestas **concisas** y **entendibles** para quien no es desarrollador o está aprendiendo. Regla: `.cursor/rules/avicore-modo-respuesta-clara.mdc` (`alwaysApply: true` en este repo).

## Qué hace el agente

| Sí | No |
|----|-----|
| Explica en pocas palabras qué cambió y por qué | Asume que conocés Laravel, Git, MCP |
| Usa ejemplos del dominio (galpón, empresa, operario) | Párrafos largos de arquitectura |
| Recomienda el siguiente paso en lenguaje simple | Omite el «qué significa para vos» |

Código, comandos, tablas de auditoría y plantilla de PR siguen **completos**; la prosa alrededor es la que se simplifica.

## Activación

Por defecto en AviCore: regla con `alwaysApply: true`. El comando `/avicore-architect-direct` lo refuerza en la sección **Modo chat**.

## Convivencia con Caverman

| Modo | Efecto |
|------|--------|
| **Clara** (default) | Lenguaje llano + conciso |
| **Caverman** (opcional) | Aún más corto; si ambos activos, **no** sacrificar claridad |

Para solo Caverman técnico: `avicore-modo-caverman.mdc` con `alwaysApply: true` y `avicore-modo-respuesta-clara.mdc` con `alwaysApply: false` (no recomendado si el usuario es no técnico).

## Convivencia con architect-direct

El flujo (docs, skills, MCP, pasos 1–7) no cambia; solo **cómo se escribe la respuesta en el chat**.
