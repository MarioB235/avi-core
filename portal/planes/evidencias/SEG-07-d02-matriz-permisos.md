# SEG-07 — Cerrar D02 y matriz Dueño/Administrativo

**ID:** SEG-07  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/admin-resumen-galpon-select`

## Objetivo

Cerrar la decisión **D02** (facultades Dueño vs Administrativo) y verificar que el enum `UserRole`, la documentación y el acceso HTTP coinciden.

## Decisión D02 (cerrada)

| Rol | Panel admin | Móvil |
|-----|-------------|-------|
| **Dueño** | Resumen, Equipo, Comercial | Sí (lote + cargas) |
| **Administrativo** | Resumen, Estructura, Usuarios | Sí (lote + cargas) |

Roles **complementarios**: el Administrativo cubre oficina si el Dueño no está; el Dueño no duplica CRUD de estructura/usuarios.

## Implementación

| Pieza | Rol |
|---|---|
| `tests/Support/RoleCapabilitiesMatrix.php` | Matriz canónica (fuente para tests) |
| `RoleCapabilitiesMatrixTest` | 66 casos parametrizados + cobertura completa + D02 complementario |
| `DuenoAdministrativoAccessTest` | 10 rutas panel + móvil compartido |
| `permisos.md` §2 | Bloque «Decisión D02» |
| Plan maestro §4 | D02 marcada cerrada |

## Prueba de cierre

```bash
php artisan test --compact tests/Unit/Enums/RoleCapabilitiesMatrixTest.php tests/Feature/Auth/DuenoAdministrativoAccessTest.php
php artisan test --compact
vendor/bin/pint --test
pnpm run check:agent-docs
```

**Resultado 2026-09-26:** 539/539 OK · Pint OK · `check:agent-docs` OK.

## Siguiente

**SEG-08** (administración segura: auto-desactivación, escalada de rol, último administrador) o **ORQ-05** (tooling).
