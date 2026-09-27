# EST-04 — Alta de lote autorizada

| Campo | Valor |
|---|---|
| ID | EST-04 |
| Estado | VERIFICADA |
| Fecha | 2026-09-26 |
| Rama | `feature/emp-01-alta-empresa` |

## Objetivo

Alta de lotes con código único generado en servidor, SMA opcional, fechas y población inicial; solo perfiles autorizados; múltiples tipos generan un lote por tipo.

## Implementación

| Pieza | Detalle |
|---|---|
| Validación | `LoteValidacion` (SMA, fecha nacimiento, cantidades/tipos) |
| Action | `RegistrarLoteAction` + `LotePolicy::create` + `GalponValidacion` |
| UI operario | Hub Cargar — dos tipos en un registro (`ManagesLoteForm`) |
| UI admin | Estructura → Lotes — un tipo; errores mapeados a `lote*` |
| Permisos | Operario sin alta; dueño/administrativo/encargado sí |

## Prueba de cierre

```bash
php artisan test --compact tests/Unit/Support/LoteValidacionTest.php tests/Feature/Operario/OperarioCargaLoteTest.php tests/Feature/Admin/AdminEstructuraTest.php
php artisan test --compact
```

Resultado: **653/653** suite (2026-09-26).

## Siguiente

**EST-05** (validación en Action — fechas, concurrencia).
