---
name: avicore-negocio
description: Reglas de negocio y permisos AviCore — multiempresa, operaciones avícolas, roles, Policies. Usar al implementar lógica, validaciones, autorización o cuando la tarea afecte reglas operativas.
---

# AviCore — Negocio y permisos

## Leer primero

| Necesidad | Referencia |
|-----------|------------|
| Reglas operativas | [`references/reglas.md`](references/reglas.md) |
| Roles y matriz de permisos | [`references/permisos.md`](references/permisos.md) |

## Principios no negociables

- Multiempresa (`empresa_id`) en toda consulta
- Negocio en Services/Actions, no en Blade
- Policies/Gates para autorización
- Anulación lógica con motivo en registros operativos

## Si afecta pantalla

Combinar con `avicore-ui` y actualizar `references/pantallas-flujos.md` en ese skill.

## Si afecta esquema

Combinar con `avicore-modelo-datos` y actualizar `references/esquema-bd.md`.
