# 09 — Reportes y exportaciones

> **Estado:** *Planificado — Bloque 7 / fase 19* ([`plan-desarrollo.md`](../../avicore-contexto/references/plan-desarrollo.md) §11 y §13). Sin módulo de reportes en código aún. Al implementar, expandir este doc con cada reporte (filtros, secciones, columnas) en el mismo PR.

## Reglas generales (MVP)

1. Los reportes se generan **manualmente** (no programados).
2. PDF formal; Excel limpio para análisis.
3. Filtros por fecha, granja y galpón cuando aplique.
4. Observaciones del operario **no** van en el PDF principal; quedan en detalle operativo.
5. Logo de la empresa cliente + marca discreta AviCore; si no hay logo, usar logo AviCore.

## Próximo paso al implementar

Usar skill `avicore-reportes`: consulta → `ReporteService` → PDF/Excel → permisos por rol. Documentar aquí cada tipo de reporte (diario, semanal, mensual, por galpón, por lote) al crearlo.
