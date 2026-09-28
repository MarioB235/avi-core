# AUD-01 — Historial operario

**ID:** AUD-01  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/aud-historial-operario`

## Objetivo

Historial operario con paginación, filtro por fecha, detalle (galpón/usuario/estado), vacunaciones integradas, orden estable y datos autorizados por empresa.

## Implementación (revalidación)

| Criterio | Evidencia |
|----------|-----------|
| Paginación 20 ítems | `OperarioGalponService::historialPaginado` + `test_historial_paginates_results` |
| Filtro fecha + validación | `x-ui.date-picker`, `DiaOperativoEmpresa`; `test_historial_filters_by_selected_date`, `test_historial_rejects_invalid_and_future_dates` |
| Detalle galpón/usuario/estado | `lineasDetalle()` en `RegistroOperativo`/`Vacunacion` incluye Galpón, Registrado por, Estado/Motivo si anulado; `test_historial_detalle_muestra_galpon_usuario_y_estado_anulado` |
| Vacunación integrada | `UNION ALL` registros + vacunaciones; `test_historial_lists_vacunaciones_mixed_with_registros_newest_first` |
| Orden descendente estable | `test_historial_lists_all_types_and_dates_newest_first` |
| Aislamiento empresa | `test_historial_does_not_show_records_from_other_company` |

## Prueba de cierre

```bash
php artisan test tests/Feature/Operario/OperarioHistorialTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- `OperarioHistorialTest`: **20/20** OK
- Suite completa: **815/815** OK · 2842 aserciones · Pint OK · `check:agent-docs` OK

## Contrato

- `patrones-mobile-operario.md` — Historial operario
- `reglas.md` §6.5, §9

## Siguiente ID

AUD-02
