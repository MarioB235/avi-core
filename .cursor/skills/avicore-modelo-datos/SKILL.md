---
name: avicore-modelo-datos
description: Diseña migraciones, modelos y relaciones PostgreSQL para AviCore respetando multiempresa y anulación lógica. Usar para nuevas tablas, campos, seeders o cambios en el modelo de datos.
disable-model-invocation: true
---

# AviCore — Modelo de datos

## Documentación

- `docs/reference/estructura-base-datos.md` (actualizar primero si cambia esquema)
- `docs/04-modelo-de-datos.md`
- `docs/05-reglas-de-negocio.md`
- `docs/06-roles-y-permisos.md`
- Registrar en `docs/CHANGELOG.md`

## Validar antes de migrar

- ¿Necesita `empresa_id`?
- Relaciones e índices
- Anulación lógica (no delete físico en registros operativos)
- Impacto en auditoría, reportes y tiempo real

## Implementar

Migración → modelo → relaciones → factories/seeders si aplica → validaciones → actualizar doc maestra si cambia el contrato.

## Comandos

```bash
php artisan migrate
php artisan test
```

## Entrada del usuario

Descripción del cambio (tabla, campos, relaciones).
