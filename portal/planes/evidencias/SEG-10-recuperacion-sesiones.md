# SEG-10 — Recuperación y sesiones

**ID:** SEG-10  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/admin-resumen-galpon-select`

## Objetivo

Reset autorizado de clave, entrega segura al usuario y cierre de sesiones revocadas sin registrar secretos.

## Implementación

| Pieza | Rol |
|-------|-----|
| `UserSessionService` | Borra sesiones `database` del usuario (todas u otras) |
| `ResetUserPasswordAction` | Clave temporal + `must_change_password` + invalidar todas las sesiones |
| `ChangePasswordAction` | Cierra otras sesiones, mantiene la actual |
| `UpdateUserAction` | Al desactivar usuario, invalida sus sesiones |
| `EnsureAccountVigente` / `EnsurePasswordChanged` | Ya cubrían vigencia y cambio obligatorio (SEG-02/03) |

Clave temporal: solo en diálogo Livewire (`plainPassword`), sin `Log::`.

## Prueba de cierre

```bash
php artisan test --compact tests/Unit/Services/Auth/UserSessionServiceTest.php tests/Feature/Auth/SessionRecoveryTest.php
php artisan test --compact
vendor/bin/pint --test
pnpm run check:agent-docs
```

**Resultado 2026-09-26:** 560/560 OK · Pint OK · `check:agent-docs` OK.

## Casos cubiertos

- Reset admin elimina sesiones del usuario objetivo
- Cambio voluntario elimina sesiones de otros dispositivos
- Desactivar usuario elimina sesiones en BD
- Logout redirige a login y deja guest
- Reset no escribe clave en logs

## Siguiente

**SEG-11** (separar demo) o **ORQ-05** (tooling).
