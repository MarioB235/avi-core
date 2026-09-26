# SEG-05 — Aislamiento transversal multiempresa

**Estado:** VERIFICADA · **Fecha:** 2026-09-26

## Objetivo

IDs ajenos en consulta, filtro, alta, edición y anulación no deben filtrar ni mutar datos de otra empresa.

## Cambios

- `EmpresaScopeService` — `constrainQuery()` y `findForActor()` reutilizados en Usuarios y Estructura (`findScoped*`).
- `AdminResumenService::for()` — retorna vacío si el rol no puede ver resumen (defensa en profundidad).
- Suite `EmpresaIsolationTest` + unit `EmpresaScopeServiceTest`.

## Pruebas

| Comando | Resultado |
|---------|-----------|
| `php artisan test tests/Feature/Auth/EmpresaIsolationTest.php` | 7 OK |
| `php artisan test tests/Unit/Services/EmpresaScopeServiceTest.php` | 1 OK |
| `php artisan test --compact` | 452 OK |
| `vendor/bin/pint --test` | OK |

Escenarios: filtro Resumen, carga huevos (Action + Livewire), edición usuario/granja ajena (404), anulación historial ajena sin mutar, alta galpón en granja ajena.

## Referencia canónica

- `avicore-negocio/references/permisos.md` · `reglas.md` §2
- `avicore-contexto/references/arquitectura.md` §5

## Siguiente ID

**SEG-06** (coherencia relacional padre/hijo) o **ORQ-05** (tooling).
