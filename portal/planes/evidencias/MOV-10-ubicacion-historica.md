# MOV-10 — Ubicación histórica

**ID:** MOV-10  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness` (sin commit)

## Objetivo

Consultar en qué galpón estaba el lote en un instante y que un traslado posterior no reasigne producción ya cargada al galpón destino.

## Implementación

| Área | Cambio |
|------|--------|
| Servicio | `LoteUbicacionHistoricaService` — `galponEnMomento`, `segmentosUbicacion`, `galponDelHechoOperativo`, `huevosAptosPorGalponEnPeriodo` |
| Traslado | `RegistrarTrasladoAvesAction` — remanente total → `lote.galpon_id` destino + `metadata.cambio_ubicacion_expediente` |
| Tests | `MovimientoAvesUbicacionHistoricaTest` (3 casos) |

## Verificación

```bash
php artisan test tests/Feature/Movimientos/MovimientoAvesUbicacionHistoricaTest.php
php artisan test tests/Feature/Movimientos/
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

| Comando | Resultado |
|---------|-----------|
| `MovimientoAvesUbicacionHistoricaTest` | 3/3 OK |
| `tests/Feature/Movimientos/` | 55/55 OK |
| Suite completa | **925/925** OK · 3214 aserciones |
| Pint / `check:agent-docs` | OK |

## Contrato

- `movimientos-aves.md` § MOV-04 metadata · § MOV-10
- `reglas.md` §11.2k

## Siguiente

**MOV-11** — Concurrencia real (dos conexiones PostgreSQL).
