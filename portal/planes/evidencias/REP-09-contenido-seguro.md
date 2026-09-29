# REP-09 — Contenido seguro en export

| Campo | Valor |
|--------|--------|
| ID | REP-09 |
| Estado | VERIFICADA |
| Fecha | 2026-09-29 |

## Objetivo

Nombres y observaciones en Excel no deben ejecutarse como fórmulas al abrir; PDF con texto de usuario sin bytes de control.

## Piezas

- `ExcelExportSeguro` — prefijos `= + - @ |` y whitespace inicial
- `PdfTexto::usuario` — control chars + `latin1Recortado`
- Exportadores Excel/PDF alineados; cabecera empresa en `AvicoreReporteFpdf`

## Verificación

```text
php artisan test --compact tests/Feature/Reportes/ReporteContenidoSeguroTest.php tests/Unit/Support/ExcelExportSeguroTest.php tests/Unit/Support/PdfTextoTest.php tests/Feature/Reportes/
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

- Celda «Empresa» con `=CMD("calc")` → valor `'=CMD("calc")` en xlsx
- Suite **1042/1042**; `check:agent-docs` OK

## Siguiente

REP-10 — volumen / cola si aplica.
