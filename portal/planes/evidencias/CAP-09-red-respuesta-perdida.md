# CAP-09 — Red y respuesta perdida

**Estado:** VERIFICADA  
**Fecha:** 2026-09-27  
**Rama:** `feature/cap-est-operacion-estructura`

## Objetivo

Mantener formulario y distinguir pendiente/error/confirmado. Reintento usa misma clave; éxito solo después de persistir.

## Implementación

| Pieza | Detalle |
|-------|---------|
| `ManagesCargaGuardada` | `cargaEnvioError`, `ejecutarEnvioCarga()`, mapeo de validación vs red |
| `Manages*Form` | Cinco capturas delegan envío resiliente; reset limpia error |
| `carga-envio-feedback.blade.php` | Banner de error; botón «Reintentar» cuando falló |
| Idempotencia | Misma clave hasta éxito o cierre (CAP-07) |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Operario/OperarioCargaEnvioRedCap09Test.php
php artisan test --compact
vendor/bin/pint --dirty
```

**Resultado (2026-09-28, post-auditoría):** 6/6 CAP-09 OK (huevos mock + provider alimento/muertes/descarte/vacunación + validación sin banner) · suite **813/813** OK.

## Siguiente

**CAP-10** — cero y ausencia con D03.
