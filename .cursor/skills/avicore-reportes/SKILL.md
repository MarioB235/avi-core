---
name: avicore-reportes
description: Implementa reportes PDF o Excel manuales en AviCore con filtros, permisos y logo. Usar para reportes diarios, semanales, por galpón o lote y exportaciones MVP.
disable-model-invocation: true
---

# AviCore — Reportes

## Documentación

- `docs/09-reportes-exportaciones.md`
- `docs/05-reglas-de-negocio.md`
- `docs/06-roles-y-permisos.md`
- `docs/03-guia-visual-ui.md`

## Reglas MVP

- Generación **manual** (no automática programada)
- Filtros por fecha/granja/galpón si aplica
- PDF formal; Excel limpio para análisis
- Logo empresa + AviCore; observaciones fuera del PDF principal
- Respetar `empresa_id` y permisos

## Implementar

Consulta → `ReporteService` → vista PDF / export Excel → validaciones → permisos.

## Entrada del usuario

Tipo de reporte (diario/semanal/mensual/galpón/lote) y detalle.
