# 09 — Reportes y exportaciones

> **Estado:** catálogo v1 **confirmado** (REP-01, 2026-09-29). Implementación PDF/Excel: REP-02 en adelante.  
> **Canónico en código:** `App\Support\ReportesCatalogoV1` + `ReportesCatalogoV1Test`.

## Reglas generales (MVP)

1. Los reportes se generan **manualmente** (no programados).
2. PDF formal; Excel limpio para análisis.
3. Filtros por fecha, granja y galpón cuando aplique.
4. Observaciones del operario **no** van en el PDF principal; quedan en detalle operativo.
5. Logo de la empresa cliente + marca discreta AviCore; si no hay logo, usar logo AviCore.
6. Columnas de **alimento**: **kg entregados** (remito); no consumo ni conversión alimenticia (RES-11, `AlimentoEntregaSemantica`).
7. Totales de producción deben coincidir con Resumen/Historial en el mismo scope (RES-04) cuando el reporte use capturas diarias.

## Catálogo operativo v1 (REP-01)

Cada fila responde una **necesidad concreta**; validación humana del cliente queda en **REP-14**.

| ID | Título | Destinatarios | Necesidad (resumen) | Implementación |
|----|--------|---------------|---------------------|----------------|
| `produccion_diaria` | Producción diaria | Dueño, Administrativo, Encargado | Exportar lo mismo que consulta diaria sin rearmar Excel | REP-02+ |
| `movimientos_existencias` | Movimientos y existencias | Dueño, Administrativo, Encargado | Conciliar saldos y movimientos de aves | REP-05 |
| `historia_lote` | Historia de lote | Dueño, Administrativo, Encargado | Trazabilidad de un lote sin mezclar todo el galpón | REP-06 |
| `sanidad_basica` | Sanidad básica | Dueño, Administrativo, Encargado | Listado de vacunaciones y anulaciones | REP-06 |
| `auditoria_operativa` | Auditoría operativa | Dueño, Administrativo | Cambios con actor y motivo (permiso restringido) | REP-08 |

Detalle campo a campo (filtros, fuentes de consulta, D05): `ReportesCatalogoV1::definiciones()`.

### Decisión D05 (plan maestro)

- **v1:** reportes **internos** de la tabla anterior.
- **Oficial MGAP / etiqueta normativa:** fuera de v1 hasta REP-12 (investigación) y REP-13 (implementación condicional). Ver `exclusionesNormativasD05()`.

## Fuera del catálogo v1 (referencia)

| Reporte | Estado REP-01 | Notas |
|---------|---------------|-------|
| Anexo Nº 2 ponedoras | Bloqueado REP-12 | `mercado-uruguay.md` §4 |
| Anexo Nº 2 reproductoras | Bloqueado REP-12 | Misma plantilla |
| Control sanitario GBPEA completo | Post-MVP | Vacunas + ATB |
| Reportes comerciales | Etapa 2 | Fuera de alcance v1 |

## Consulta compartida (REP-02)

- **Servicio:** `App\Services\ReporteConsultaService` — única entrada de agregados para export «producción diaria».
- **Agregados:** delega en `TotalesCapturaDiaService` (misma regla que Resumen, Inicio e Historial — RES-04).
- **Filtros:** `ReporteFiltroProduccion` (granja, galpón, rango ≤ 93 días).
- **Métodos:** `produccionDiaria`, `produccionPorGalponEnDia`, `totalesVistaHoy`.
- **Tests:** `ReporteConsultaServiceTest`, `AdminResumenTotalesConciliacionTest`.

## Excel producción diaria (REP-03)

- **Librería:** `openspout/openspout` (PhpSpreadsheet bloqueado por advisories de Composer en este entorno).
- **Exportador:** `ReporteProduccionDiariaExcelExporter` — fechas y números nativos; cabecera con empresa/período/filtros; fila total.
- **Ruta:** `GET /{rol}/reportes/produccion-diaria.xlsx` (`admin.viewResumen`); enlace en Resumen.
- **Seguridad texto:** `ExcelExportSeguro` (prefijo `'` en celdas que empiezan como fórmula).
- **Tests:** `ReporteProduccionDiariaExcelTest`, `ExcelExportSeguroTest`.

## PDF producción diaria (REP-04)

- **Librería:** `setasign/fpdf` (dompdf bloqueado por advisories de Composer en este entorno).
- **Exportador:** `ReporteProduccionDiariaPdfExporter` + `AvicoreReporteFpdf` (A4, logo empresa si PNG/JPG, pie AviCore, numeración).
- **Ruta:** `GET /{rol}/reportes/produccion-diaria.pdf` (`admin.viewResumen`).
- **Tests:** `ReporteProduccionDiariaPdfTest`.

## Movimientos y existencias (REP-05)

- **Consulta:** `ReporteConsultaService::movimientosExistencias` — conciliación MOV-09 + ledger por galpón (`MovimientoAvesConciliacionService`).
- **Excel/PDF:** `ReporteMovimientosExistenciasExcelExporter`, `ReporteMovimientosExistenciasPdfExporter`.
- **Ruta:** `GET /{rol}/reportes/movimientos-existencias.{xlsx|pdf}` (`admin.viewMovimientos`); enlaces en Movimientos.
- **Tests:** `ReporteMovimientosExistenciasTest`.

## Historia de lote y sanidad (REP-06)

- **Consulta:** `historiaLote` (EST-10/RES-05: huevos solo si un lote activo) y `sanidadBasica`.
- **Excel:** `ReporteHistoriaLoteExcelExporter`, `ReporteSanidadBasicaExcelExporter`.
- **Rutas:** `historia-lote.xlsx` (requiere `lote`), `sanidad-basica.xlsx`; enlaces en Estructura / ficha lote.
- **Tests:** `ReporteHistoriaLoteSanidadTest`.

## Vacíos y extremos (REP-07)

- **Estados:** `ReporteEstadoConsulta` (`ok`, `sin_datos`, `no_disponible`) en `ReporteConsultaService`.
- **Puerta export:** `ReporteExportGuard::assertDescargable` — consulta inválida → HTTP **422** (no Excel “vacío” engañoso).
- **Mensajes:** `ReporteExportGuard::filaMensajeSinDatos` por tipo de reporte.
- **PDF largo:** `PdfTexto::latin1Recortado` para textos extensos.
- **Tests:** `ReporteVaciosExtremosTest`, `ReporteExportGuardTest` (unit).

## Descarga autorizada (REP-08)

- Cada `GET` de reporte exige sesión + `Gate` (`admin.viewResumen` o `admin.viewMovimientos`).
- **Filtros:** `ReporteAutorizacionFiltrosService` valida granja/galpón/lote en la empresa del actor; ID ajeno → `no_disponible` y HTTP **422** (no Excel vacío).
- No hay URLs firmadas en v1: la autorización se reevalúa en cada descarga.
- **Tests:** `ReporteAutorizacionDescargaTest`.

## Contenido seguro (REP-09)

- **Excel:** `ExcelExportSeguro::texto` en cabeceras, filtros, mensajes sin datos y columnas de texto (prefijo `'` si la celda empieza como fórmula: `=`, `+`, `-`, `@`, `|`, tab, salto de línea).
- **PDF:** `PdfTexto::usuario` en textos de usuario (galpón, tipo, efecto, cabecera empresa); `latin1` en etiquetas fijas.
- **Tests:** `ReporteContenidoSeguroTest`, `ExcelExportSeguroTest`, `PdfTextoTest`.

## Próximo paso técnico (REP-10)

Volumen — medir export pesados; cola solo si hace falta.

### Planillas MGAP (investigación — no v1)

| Reporte | Fuente normativa | Prioridad | Columnas clave desde AviCore |
|---------|------------------|-----------|------------------------------|
| Registro productivo **ponedoras** | Anexo Nº 2 DGSG | REP-12/13 | DICOSE, lote SMA, mortalidad grilla, huevos aptos + descarte, alimento entregado, vacunas |
| Registro productivo **reproductoras** | Mismo Anexo 2 | REP-12/13 | Misma estructura |
| Control sanitario | GBPEA §7.10 | Post-MVP | Vacunas + ATB |

## Verificación REP-01

```bash
php artisan test --compact tests/Unit/Support/ReportesCatalogoV1Test.php
pnpm run check:agent-docs
```
