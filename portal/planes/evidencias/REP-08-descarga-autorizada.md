# REP-08 — Generación y descarga autorizadas

| Campo | Valor |
|--------|--------|
| ID | REP-08 |
| Estado | VERIFICADA |
| Fecha | 2026-09-29 |

## Objetivo

Cada descarga revalida permiso y empresa; parámetros `granja`/`galpon`/`lote` ajenos no entregan archivo.

## Implementación

`ReporteAutorizacionFiltrosService` + integración en `ReporteConsultaService` + `Gate` en controladores.

## Verificación

`ReporteAutorizacionDescargaTest` + suite completa.

## Siguiente

REP-09 — contenido seguro.
