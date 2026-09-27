# CAP-04 — Descarte de aves

**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/emp-01-alta-empresa`

## Objetivo

Integridad de saldo, etiqueta diferenciada y anulación; no contabiliza muerte ni huevo descartado.

## Implementación

| Pieza | Detalle |
|-------|---------|
| `RegistrarCargaDescarteAction` | `lockForUpdate` + `idempotencia_clave` (mismo patrón que muertes) |
| `ManagesDescarteForm` | UUID por apertura; `ValidationException` conserva diálogo y valor |
| UI `carga-descarte-form` | Saldo vivo, descarte hoy, confirmación, aviso «no es mortalidad ni huevo descartado» |
| Anulación | `AnularRegistroOperativoAction` restaura aves; resumen solo cuenta activos |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Operario/OperarioCargaDescarteCap04Test.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

**Resultado:** 725/725 tests OK.

## Siguiente

**CAP-05** — alimento entregado.
