# SEG-01 — Enum Reparto completo

**Estado:** VERIFICADA · **Fecha:** 2026-09-26

## Objetivo

Completar ramas de `UserRole` para `Reparto` sin `UnhandledMatchError` ni ampliar permisos (fuera de v1).

## Cambios

`app/Enums/UserRole.php`:

- `canAccessOperarioMobile()` → `false` para Reparto
- `canViewResumen()` → `false` para Reparto
- `assignableRoles()` → `[]` para Reparto (como Operario/Encargado sin gestión)

## Pruebas

| Comando | Resultado |
|---------|-----------|
| `php artisan test --filter=UserRoleTest` | 7 OK |
| `php artisan test tests/Feature/Auth/RolePanelRoutesTest.php` | 6 OK |

Regresión HTTP: Reparto en `/operario` → redirect `reparto.home`; `/dueno/resumen` → redirect `reparto.home`.

## Referencia canónica

`avicore-negocio/references/permisos.md` §7 y §10.

## Siguiente ID

**SEG-02** (revocación de sesión por request).
