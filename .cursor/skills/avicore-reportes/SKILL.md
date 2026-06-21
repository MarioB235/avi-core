---
name: avicore-reportes
description: Implementa reportes PDF o Excel manuales en AviCore con filtros, permisos y logo. Usar para reportes diarios, semanales, por galpón o lote y exportaciones MVP.
---

# AviCore — Reportes

## Leer primero

| Referencia | Contenido |
|------------|-----------|
| [`references/reportes.md`](references/reportes.md) | Reportes MVP |
| `avicore-negocio/references/` | Reglas y permisos |
| `avicore-design-system/references/` | UI de exportación |

## Reglas MVP

- Generación **manual** (no automática programada)
- PDF formal; Excel limpio; logo empresa + AviCore
- Respetar `empresa_id` y permisos

## Implementar

Consulta → `ReporteService` → vista PDF / export Excel → validaciones → permisos.
