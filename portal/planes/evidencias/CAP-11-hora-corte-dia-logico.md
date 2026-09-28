# CAP-11 — Hora de corte y día lógico

**ID:** CAP-11  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-27  
**Rama:** `feature/cap-est-operacion-estructura`

## Objetivo

Unificar el **día lógico** operativo según `configuracion.zona_horaria` de la empresa (medianoche local), de modo que historial, totales del home/resumen y anulación del operario usen el mismo criterio.

## Implementación

| Área | Cambio |
|------|--------|
| Dominio | `DiaOperativoEmpresa` — rango UTC `[inicio, fin)` desde medianoche en zona empresa |
| Modelos | `RegistroOperativo` / `Vacunacion`: `delDia($empresaId)` y `enFecha($fecha, $empresaId)` |
| Anulación | `AuthorizesOperarioAnulacion` usa día lógico actual (no `isToday()` UTC) |
| Servicios | `OperarioGalponResumenService`, `AdminResumenService` (KPIs, pulso y `posturaSemanal` con `delDia`), `OperarioGalponService` |
| Historial | Validación de fecha máxima = día lógico actual de la empresa |

## Prueba de cierre

```bash
php artisan test tests/Unit/Support/DiaOperativoEmpresaTest.php
php artisan test tests/Feature/Operario/OperarioDiaOperativoCap11Test.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28, post-auditoría)

- `DiaOperativoEmpresaTest`: 6/6 OK (scopes + TZ)
- `OperarioDiaOperativoCap11Test`: 4/4 OK
- `AdminResumenServiceTest::test_postura_semanal_uses_logical_day_in_empresa_timezone`: OK
- Suite completa: **813/813** OK · Pint OK · `check:agent-docs` OK

## Contrato

- `reglas.md` §8.9
- `pantallas-flujos.md` — nota CAP-11
