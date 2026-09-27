# EST-05 — Validación en Action

| Campo | Valor |
|---|---|
| ID | EST-05 |
| Estado | VERIFICADA |
| Fecha | 2026-09-26 |
| Rama | `feature/emp-01-alta-empresa` |

## Objetivo

Centralizar en `LoteValidacion` + `RegistrarLoteAction` las reglas de fechas, tipos, cantidades y concurrencia; que una llamada directa a la Action no evite validaciones ni duplique lógica de Livewire.

## Implementación

| Pieza | Detalle |
|---|---|
| `LoteValidacion` | `assertFechaIngreso`, `assertFechasCoherentes`, cantidades enteras con tope `CANTIDAD_MAXIMA` |
| `RegistrarLoteAction` | Aplica validaciones antes de transacción; revalida galpón bajo `lockForUpdate` con `granja` cargada |
| Tests | `LoteValidacionTest` + `OperarioCargaLoteTest` (fechas futuras, nacimiento > ingreso, tipo inválido, códigos únicos consecutivos) |

## Prueba de cierre

```bash
php artisan test --compact tests/Unit/Support/LoteValidacionTest.php tests/Feature/Operario/OperarioCargaLoteTest.php
php artisan test --compact
```

Resultado: **669/669** suite (2026-09-26).

## Siguiente

**EST-06** (separar edición/transición de estado en `UpdateLoteAction`).
