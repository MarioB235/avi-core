# EST-08 — Vacío, mantenimiento y ciclos

| Campo | Valor |
|---|---|
| ID | EST-08 |
| Estado | VERIFICADA |
| Fecha | 2026-09-26 |
| Rama | `feature/emp-01-alta-empresa` |

## Objetivo

Acordar y aplicar reglas para galpón sin lote, carga excepcional y vacío sanitario sin automatismo normativo; evitar mezclar ciclos.

## Reglas acordadas

| Situación | Carga productiva (huevos/muertes/descarte) | Vacunación | Alimento | Nuevo lote |
|-----------|------------------------------------------|------------|----------|------------|
| Galpón `activo` con lote activo/en producción | Sí | Sí | Sí | No (ciclo abierto) |
| Galpón `activo` sin lote (entre ciclos) | No | No (UI) | Sí (excepcional) | Sí si `aves_actuales = 0` |
| `en_mantenimiento` / `vacio_sanitario` / inactivo | No | No | No | No |

POES / checklist de vacío sanitario: **post-MVP** (sin flujo inventado).

## Implementación

| Pieza | Detalle |
|---|---|
| `GalponValidacion` | `assertLoteActivoParaCargaProductiva`, `assertCicloCerradoParaNuevoLote`, `assertGalponVacioParaEstadoNoOperativo` |
| Actions | Huevos/muertes/descarte + `RegistrarLoteAction` + `UpdateGalponAction` |
| Factory test | `GalponFactory::conLoteActivo()` |

## Prueba de cierre

```bash
php artisan test --compact tests/Unit/Support/GalponValidacionTest.php tests/Feature/Operario/OperarioCargaAlimentoTest.php tests/Feature/Operario/OperarioCargaHuevosTest.php tests/Feature/Admin/AdminEstructuraTest.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Siguiente

**EST-09** (baja y reasignación seguras).
