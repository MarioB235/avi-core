---
name: avicore-nuevo-modulo
description: Crea o extiende un módulo AviCore (empresas, granjas, galpones, lotes, usuarios, reportes, auditoría) con migración, Livewire, permisos y pruebas. Usar cuando el usuario pide un módulo nuevo o CRUD completo.
disable-model-invocation: true
---

# AviCore — Nuevo módulo

## Documentación obligatoria

- `docs/02-pantallas-y-flujos.md`
- `docs/04-modelo-de-datos.md`
- `docs/05-reglas-de-negocio.md`
- `docs/06-roles-y-permisos.md`
- `docs/03-guia-visual-ui.md`
- `docs/11-checklist-modulos.md`

## Definir antes de codificar

- Pantalla principal, campos, botones
- Validaciones y permisos por rol
- Tablas e índices; `empresa_id` si aplica
- Auditoría y tiempo real (solo si aporta valor)

## Orden de implementación

1. Migración → modelo → relaciones
2. Componente Livewire + vista
3. Validaciones → Policies/Gates
4. Action/Service si hay regla de negocio
5. Auditoría y eventos si corresponde
6. Probar en PC y móvil

No avanzar al siguiente módulo hasta que este flujo funcione completo.

## Entrada del usuario

**Módulo**, **Objetivo** y detalle de la tarea.
