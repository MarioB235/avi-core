# RES-05 — Mortalidad y ventana

**ID:** RES-05  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness`

## Objetivo

Mortalidad acumulada coherente con movimientos y cierres: no tasa por lote con varios activos; al cerrar ciclo no se pierde historia ni el denominador sin aviso.

## Cambios

| Pieza | Detalle |
|-------|---------|
| `MortalidadVentanaGalpon` | Ciclo = lotes activos o último cierre; muertes desde `fecha_ingreso` mínima |
| `AdminResumenService` | % y alertas desde el servicio; flags `mortalidad_solo_galpon`, `mortalidad_incluye_cerrados` |
| UI Resumen | Texto aclaratorio en tarjeta de galpón |

## Verificación

```bash
php artisan test tests/Feature/Services/AdminResumenMortalidadVentanaTest.php
php artisan test
```

## Siguiente

RES-06 — umbrales y etiquetado de referencia 1,1 %.
