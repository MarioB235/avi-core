# CAP-08 — Estado actual bajo lock

**Estado:** VERIFICADA  
**Fecha:** 2026-09-27  
**Rama:** `feature/cap-est-operacion-estructura`

## Objetivo

Revalidar disponibilidad y saldo dentro de mutación crítica. Inactivación o cierre concurrente no acepta carga prohibida.

## Implementación

| Pieza | Detalle |
|-------|---------|
| `GalponValidacion` | `bloquearParaMutacion()`, `revalidarParaCargaBajoLock()` |
| Actions captura | Transacción + lock antes de persistir; muertes/descarte revalidan saldo fresco |
| `RegistrarVacunacionAction` | Lock de galpón y lote; rechazo si lote cerrado bajo lock |
| Tests | `OperarioCargaEstadoBajoLockCap08Test` — modelo stale vs BD, lote cerrado, saldo insuficiente |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Operario/OperarioCargaEstadoBajoLockCap08Test.php
php artisan test --compact
vendor/bin/pint --dirty
```

**Resultado:** 7/7 CAP-08 OK · suite **769/769** OK (2026-09-27).

## Siguiente

**CAP-09** — red y respuesta perdida (formulario, estados pendiente/error/confirmado).
