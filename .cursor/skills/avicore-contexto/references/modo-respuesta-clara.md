# Modo respuesta clara (chat)

Respuestas **cortas** para programador con poca experiencia. Regla: `.cursor/rules/avicore-modo-respuesta-clara.mdc`.

## Principio

Primero **En corto** (la idea). Luego **Qué hice** (si hubo cambios). Cierra con **Qué sigue** (un solo paso). Sin bloque «Por qué» separado.

## Anatomía

```text
**AviCore Architect** · skill `avicore-contexto` · consulta

**En corto** Una o dos frases con la respuesta directa.

**Qué hice** Solo si aplicó: qué cambió o qué comando corrí (1–2 frases).

**Qué sigue** Una acción concreta para vos ahora.

(comandos o código copiable, si hace falta)
```

Consultas: ~80 palabras en prosa. Implementación: ~120 palabras antes del código.

## Ejemplo: duda de login (antes vs después)

### Antes (demasiado largo y mezclado)

Varios párrafos, tablas de modos, documentos por rol, seed, Cloud y local en el mismo mensaje. El usuario no sabe qué hacer primero.

### Después (objetivo)

```text
**AviCore Architect** · skill `avicore-datos-demo` · consulta login

**En corto** Con el selector activo no escribís documento ni contraseña: elegís el rol y entrás. Es un solo usuario de prueba (`000000000`); el desplegable cambia el rol al entrar.

**Qué sigue** Abrí http://localhost:8000/login, elegí Operario y pulsá Ingresar. Si no ves el selector, poné `AVICORE_DEMO_LOGIN=true` en tu `.env`.
```

## Ejemplo: implementación

```text
**AviCore Architect** · skill `avicore-ui` · mensaje 1

**En corto** Ajusté el hero del admin en móvil para que los KPIs no se apilen.

**Qué hice** Reorganicé `pages/admin/home` con grilla de dos columnas en pantallas chicas.

**Qué sigue** `composer dev`, abrí `/admin` en el celular o DevTools y decime si se lee bien.
```

## Reglas por mensaje

| Mensaje | Prosa | Extra |
|---------|-------|-------|
| 1 — tarea | 3 bloques cortos | código/comandos completos |
| 2 — auditoría | 3 bloques cortos | tabla auditoría completa |
| 3 — correcciones | 3 bloques cortos | qué cambió vs msg 2 |
| 4 — docs | 3 bloques cortos | gaps o «sin gaps» |
| 5 — PR | 3 bloques cortos | plantilla PR completa |
| Consulta | **En corto** + **Qué sigue** (máx. 3 pasos si hace falta) | sin implementar salvo pedido |

## Usuario confundido

**En corto** (analogía) → lista **máx. 3 pasos** → **Qué sigue** (primer paso). Sin teoría extra.

## Convivencia

| Modo | Efecto |
|------|--------|
| Clara (default) | Cabecera + En corto / Qué hice / Qué sigue — frases cortas |
| Caverman | Cabecera mínima; sin bloques etiquetados |

El flujo interno del architect (pasos 1–7) no cambia; solo el chat.
