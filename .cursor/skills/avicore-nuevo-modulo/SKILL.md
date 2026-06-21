---
name: avicore-nuevo-modulo
description: Crea o extiende un módulo AviCore completo (migración, Livewire, permisos, pruebas). Usar cuando el usuario pide un módulo nuevo o CRUD de punta a punta.
---

# AviCore — Nuevo módulo

**Cuándo usar:** módulo o CRUD **completo**. Si solo pantalla → `avicore-ui`. Si solo BD → `avicore-modelo-datos`.

## Leer primero

| Referencia | Contenido |
|------------|-----------|
| [`references/checklist.md`](references/checklist.md) | Definición de módulo terminado |
| `avicore-ui/references/pantallas-flujos.md` | Pantallas y flujos |
| `avicore-negocio/references/` | Reglas y permisos |
| `avicore-modelo-datos/references/` | Modelo y esquema |

## Orden de implementación

1. Migración → modelo → relaciones
2. Livewire + vista
3. Validaciones → Policies/Gates
4. Action/Service si hay regla de negocio
5. Auditoría y eventos si corresponde
6. Probar PC y móvil

No avanzar al siguiente módulo hasta que este flujo funcione completo.
