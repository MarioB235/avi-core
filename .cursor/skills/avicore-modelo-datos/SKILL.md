---
name: avicore-modelo-datos
description: Diseña migraciones, modelos y relaciones PostgreSQL para AviCore respetando multiempresa y anulación lógica. Usar para nuevas tablas, campos, seeders o cambios en el modelo de datos.
---

# AviCore — Modelo de datos

## Leer primero

| Referencia | Contenido |
|------------|-----------|
| [`references/esquema-bd.md`](references/esquema-bd.md) | Esquema implementado (actualizar primero) |
| [`references/criterios-modelo.md`](references/criterios-modelo.md) | Criterios narrativos del modelo |
| `avicore-negocio/references/` | Reglas y permisos |

Registrar cambios de contrato en `docs/CHANGELOG.md`.

## Validar antes de migrar

- ¿Necesita `empresa_id`?
- Relaciones e índices
- Anulación lógica (no delete físico en operativos)
- Impacto en auditoría, reportes y tiempo real

## Implementar

Migración → modelo → relaciones → factories/seeders → validaciones → actualizar `references/esquema-bd.md`.

## Comandos

```bash
php artisan migrate
php artisan test
```
