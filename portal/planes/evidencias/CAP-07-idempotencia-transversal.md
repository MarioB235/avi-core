# CAP-07 — Idempotencia transversal

**Estado:** VERIFICADA  
**Fecha:** 2026-09-27  
**Rama:** `feature/cap-est-operacion-estructura`

## Objetivo

Clave por intención/empresa y resultado persistido. Doble toque o timeout crea una operación; nueva intención con igual cantidad crea otra.

## Implementación

| Pieza | Detalle |
|-------|---------|
| `IdempotenciaCaptura` | `generarClave()`, `normalizarClave()`, `resolverRegistroOperativo()`, `resolverVacunacion()` |
| Actions | `RegistrarCargaHuevos/Muertes/Descarte/AlimentoAction`, `RegistrarVacunacionAction` delegan resolución |
| Livewire | `Manages*Form` usa `IdempotenciaCaptura::generarClave()` al abrir/resetear diálogo |
| Tests | `OperarioCargaIdempotenciaCap07Test` — retry, muertes sin doble decremento, scope empresa, Livewire |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Operario/OperarioCargaIdempotenciaCap07Test.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

**Resultado:** 11/11 CAP-07 OK · suite **769/769** OK (2026-09-27).

## Siguiente

**CAP-08** — revalidación bajo lock (verificada en la misma sesión).
