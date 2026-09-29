# RES-03 — Completitud diaria D03

**ID:** RES-03  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness`

## Objetivo (plan maestro)

D03: registro por tipo, cero y omisión. **Éxito:** alimento solo no dispara «todas las cargas al día».

## Cambios

| Área | Detalle |
|------|---------|
| Dominio | `App\Support\CompletitudDiariaD03` — omisión si falta huevos, muertes o descarte (`CapturaCeroEstado::OMISION`) |
| Pulso | `AdminResumenService::pulsoFor` filtra `galpones_sin_carga` por D03 (ya no «cualquier registro del día») |
| UI | Inicio: «capturas productivas pendientes»; hints del pulso alineados |
| Contrato | `metricas-resumen.md`, `reglas.md` §19, `ResumenMetricasCatalog` |

## Verificación

```bash
php artisan test tests/Unit/Support/CompletitudDiariaD03Test.php tests/Feature/Services/AdminResumenServiceTest.php
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

Criterios:

- Galpón con solo alimento → `estado` `atencion`, aparece en `galpones_sin_carga`.
- Galpón con huevos pero sin muertes/descarte → sigue en lista (omisión parcial).
- Huevos + muertes + descarte (o cero confirmado) → `estado` `ok`, lista vacía.

## Siguiente

RES-04 — conciliar totales Inicio / Resumen / Historial.
