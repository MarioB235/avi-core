# RES-02 — Métricas canónicas Inicio/Resumen

**ID:** RES-02  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness` (sin commit)

## Objetivo

Documentar fuente, unidad, período, población, exclusiones y ausencia de cada KPI de supervisor, con casos verificables en tests.

## Implementación

| Área | Cambio |
|------|--------|
| Catálogo PHP | `App\Support\ResumenMetricasCatalog` |
| Contrato humano | `avicore-negocio/references/metricas-resumen.md` |
| Reglas | `reglas.md` §18 |
| Servicio | Umbral mortalidad enlazado al catálogo |
| Tests | `ResumenMetricasCatalogTest`, `AdminResumenMetricasContractTest` + suite `AdminResumenServiceTest` |

## Verificación

```bash
php artisan test tests/Unit/Support/ResumenMetricasCatalogTest.php tests/Feature/Services/AdminResumenMetricasContractTest.php tests/Feature/Services/AdminResumenServiceTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

| Comando | Resultado |
|---------|-----------|
| Catálogo + contrato + AdminResumenServiceTest | 27/27 OK |
| Suite completa | **951/951** OK · 3426 aserciones |
| Pint / `check:agent-docs` | OK |

## Siguiente

**RES-03** — Completitud diaria (D03).
