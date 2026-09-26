# EST-03 — Jerarquía de estados granja → galpones

| Campo | Valor |
|---|---|
| ID | EST-03 |
| Estado | VERIFICADA |
| Fecha | 2026-09-26 |
| Rama | `feature/emp-01-alta-empresa` |

## Objetivo

Definir el efecto de una granja inactiva sobre sus galpones: sin carga operativa ni bypass por ID directo; historial conservado.

## Implementación

| Pieza | Detalle |
|---|---|
| Modelo | `Galpon::disponibleParaCargaOperativa()`; scope `disponiblesParaCarga` exige granja activa |
| Validación | `GalponValidacion::assertDisponibleParaCarga()` centraliza rechazo en Actions |
| Cascada | `UpdateGranjaAction` pone `activo = false` en galpones al desactivar granja |
| Servicios | `OperarioGalponService`, `AdminHomeService`, `AdminResumenService` alineados al scope |
| UI | Aviso al desactivar granja; badge «Granja inactiva» en listado de galpones |

## Prueba de cierre

```bash
php artisan test --compact tests/Unit/Support/GalponValidacionTest.php tests/Feature/Admin/AdminEstructuraTest.php tests/Feature/Services/OperarioGalponServiceTest.php tests/Feature/Services/AdminResumenServiceTest.php tests/Feature/Operario/OperarioCargaLoteTest.php
php artisan test --compact
```

Resultado: **643/643** suite (2026-09-26).

## Siguiente

**EST-04** (alta de lote autorizada).
