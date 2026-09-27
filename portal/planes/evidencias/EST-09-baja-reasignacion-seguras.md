# EST-09 — Baja y reasignación seguras

| Campo | Valor |
|---|---|
| ID | EST-09 |
| Estado | VERIFICADA |
| Fecha | 2026-09-26 |
| Rama | `feature/emp-01-alta-empresa` |

## Objetivo

Conservar trazabilidad: bajas lógicas en estructura, sin borrado físico ni cambios de empresa; reasignación de galpón solo sin historial operativo.

## Implementación

| Pieza | Detalle |
|---|---|
| `PreventsHardDelete` | Trait en `Granja`, `Galpon`, `Lote` — rechaza `delete()` |
| `EstructuraValidacion` | `assertSinCambioEmpresa`, `assertReasignacionGranjaGalponSegura`, `assertSinReasignacionPadre` |
| `Galpon::tieneHistorialTrazable()` | Lotes, registros operativos o vacunaciones |
| Actions | `UpdateGalponAction`, `UpdateGranjaAction`, `UpdateLoteAction` |
| UI | Diálogo galpón: granja lectura si hay historial |

## Prueba de cierre

```bash
php artisan test --compact tests/Unit/Support/EstructuraValidacionTest.php tests/Feature/Admin/AdminEstructuraTest.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

Resultado: **696/696** suite (2026-09-26).

## Siguiente

**EST-10** (ficha de lote/galpón).
