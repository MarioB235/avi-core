# SEG-04 — Autorizar cada acción en servidor

**Estado:** VERIFICADA · **Fecha:** 2026-09-26

## Objetivo

No depender solo de `mount`, URL o botones ocultos: cada acción Livewire sensible debe rechazar llamadas manipuladas en servidor (403).

## Cambios

- Trait `App\Livewire\Concerns\RequiresRoleAbility` — `hydrate()` + `mount` en Resumen, Equipo y Comercial.
- `Admin\Usuarios\Index` y `Admin\Estructura\Index` — `hydrate()` con la misma puerta que `mount`; `authorize()` explícito en `guardar`, `resetearPassword`.
- `ManagesLoteForm` — `guardarLote` / `abrirFormularioLote` lanzan `AuthorizationException` si el rol no puede crear lote.

**Post-auditoría msg 3 (2026-09-26):** `RequiresRoleAbility` reemplazado por `AdminModulePolicy` + Gates + `RequiresAdminModuleAccess`; lote operario vía `LotePolicy::create`.

## Pruebas

| Comando | Resultado |
|---------|-----------|
| `php artisan test tests/Feature/Auth/LivewireActionAuthorizationTest.php` | 6 OK |
| `php artisan test --compact` | 444 OK |
| `vendor/bin/pint --test` | OK |

Incluye degradación de rol entre requests Livewire (`set` tras cambio en BD → 403).

## Referencia canónica

- `avicore-negocio/references/permisos.md`
- `avicore-contexto/references/arquitectura.md` §5

## Siguiente ID

**SEG-05** (aislamiento transversal multiempresa).
