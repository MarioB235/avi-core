# SEG-08 — Administración segura de usuarios

**ID:** SEG-08  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/admin-resumen-galpon-select`

## Objetivo

Evitar dejar una empresa sin gestión de oficina y bloquear escalada de roles no permitida al crear o editar usuarios.

## Reglas implementadas

| Regla | Dónde |
|-------|--------|
| No auto-desactivarse | `UpdateUserAction` (ya existía) |
| Rol solo si está en `assignableRoles()` | `CreateUserAction`, `UpdateUserAction` |
| No convertir usuario empresa ↔ Admin AviCore | `UpdateUserAction` |
| No quedar sin administrativo activo | `UserManagementGuard` → `UpdateUserAction` |

**Administrativo activo** = usuario con `empresa_id` y `rol->canManageUsers()` (hoy: rol `administrativo`).

## Prueba de cierre

```bash
php artisan test --compact tests/Unit/Services/UserManagementGuardTest.php tests/Feature/Admin/AdminUsuariosTest.php
php artisan test --compact
vendor/bin/pint --test
pnpm run check:agent-docs
```

**Resultado 2026-09-26:** 546/546 OK · Pint OK · `check:agent-docs` OK.

## Casos nuevos

- Desactivar último administrativo (vía Admin Avicore) → rechazado
- Degradar último administrativo a encargado (Livewire) → error en `rol`
- Desactivar administrativo cuando hay otro activo → OK
- Escalar operario a dueño en update → rechazado

## Siguiente

**SEG-09** (login multiempresa) o **ORQ-05** (tooling).
