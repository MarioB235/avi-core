# SEG-03 — Cambios de rol y reset de sesión

**Estado:** VERIFICADA · **Fecha:** 2026-09-26

## Objetivo

Tras cambio de rol o reset de contraseña, la sesión existente debe perder capacidades previas y exigir cambio de clave temporal; un snapshot Livewire abierto no debe evitar restricciones.

## Cambios

- `EnsureAccountVigente` (SEG-02) ya refresca usuario en cada request → GET respeta rol y `must_change_password` actualizados.
- `AppServiceProvider`: middleware persistente Livewire para `EnsurePasswordChanged`, `EnsureOperarioAccess` y `EnsureRolePanelAccess`.
- `ResetUserPasswordAction` ya marca `must_change_password = true` (sin cambio de código).

## Pruebas

| Comando | Resultado |
|---------|-----------|
| `php artisan test tests/Feature/Auth/SessionRolResetTest.php` | 4 OK |
| `php artisan test tests/Feature/Auth/SessionVigenciaTest.php` | 3 OK (regresión SEG-02) |
| `php artisan test --compact` | 438 OK |
| `vendor/bin/pint --test` | OK |

Escenarios HTTP: encargado degradado a operario no accede a `/encargado/*`; reset fuerza `/password/change`; Livewire update con snapshot previo redirige sin persistir registros.

## Referencia canónica

- `avicore-negocio/references/permisos.md` (vigencia, rol, clave)
- `avicore-contexto/references/arquitectura.md` §5

## Siguiente ID

**SEG-04** (autorizar cada acción en servidor).
