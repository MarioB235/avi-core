# SEG-02 — Vigencia por request

**Estado:** VERIFICADA · **Fecha:** 2026-09-26

## Objetivo

Usuario desactivado o empresa suspendida deben bloquear sesión existente en cada request (GET y Livewire), sin mutar datos.

## Cambios

- `AccountAccessService::mayUseApplication()` — fuente única de vigencia (activo + empresa).
- `EnsureAccountVigente` — middleware en grupo `web` (incluye `POST /livewire/update`).
- `AttemptLoginAction` — reutiliza `AccountAccessService` en login.

## Pruebas

| Comando | Resultado |
|---------|-----------|
| `php artisan test tests/Feature/Auth/SessionVigenciaTest.php` | 3 OK |
| `php artisan test tests/Unit/Services/Auth/AccountAccessServiceTest.php` | 4 OK |
| `php artisan test --compact` | 434 OK |
| `vendor/bin/pint --test` | OK |

Nota: `Livewire::test()` desactiva middleware a propósito; la prueba Livewire usa HTTP real (`postJson` al endpoint update) para validar el comportamiento de producción.

## Referencia canónica

- `avicore-negocio/references/permisos.md` (vigencia por request)
- `avicore-contexto/references/arquitectura.md` §5

## Siguiente ID

**SEG-03** (cambios de rol y reset de clave).
