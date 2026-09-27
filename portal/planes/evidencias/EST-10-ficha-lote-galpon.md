# EST-10 — Ficha de lote/galpón

**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/emp-01-alta-empresa`

## Objetivo

Mostrar ficha de lectura en Estructura admin con población, estado, ubicación e historia; métricas por lote solo cuando son atribuibles (un solo lote activo en el galpón).

## Implementación

| Pieza | Detalle |
|-------|---------|
| `EstructuraFichaService` | Arma datos de ficha galpón y lote; reutiliza `OperarioGalponResumenService` |
| `Index` Livewire | `abrirFichaGalpon` / `abrirFichaLote` + diálogos readonly |
| UI | Botón **Ver ficha** en tablas galpones y lotes (visible para Encargado y Administrativo) |
| Métricas lote | Solo si hay un único lote activo/en producción; aviso si hay varios |
| Saldo | Nota explícita: población inicial del lote vs saldo vivo del galpón |

## Prueba de cierre

```bash
php artisan test --compact tests/Unit/Services/EstructuraFichaServiceTest.php tests/Feature/Admin/AdminEstructuraTest.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

**Resultado:** 702/702 tests OK.

## Siguiente

**CAP-01** — selector robusto en operario móvil.
