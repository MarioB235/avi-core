# AUD-02 — Anulación propia del día

**ID:** AUD-02  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/aud-historial-operario`

## Objetivo

Anulación propia del día con motivo obligatorio, retención del motivo en BD y exclusión de totales; segunda anulación no restaura aves dos veces.

## Implementación (revalidación)

| Criterio | Evidencia |
|----------|-----------|
| Motivo obligatorio | `AnularRegistroOperativoAction` + `test_historial_rechaza_anulacion_sin_motivo` |
| Retención motivo/estado | `motivo_anulacion`, `anulado_at`, `anulado_por`; detalle y lista muestran «Anulado» |
| Exclusión de totales | `RegistroOperativo::scopeActivos()` en `OperarioGalponResumenService`; `test_historial_anulacion_excluye_totales_y_rechaza_segunda_anulacion` |
| Restauración aves (muertes/descarte) | `AnularRegistroOperativoAction` + tests muertes/descarte/vacunación |
| Segunda anulación rechazada | Policy + `ValidationException`; aves no cambian tras reintento |
| Solo día lógico actual | `test_historial_rechaza_anular_registro_de_otro_dia` |

## Prueba de cierre

```bash
php artisan test tests/Feature/Operario/OperarioHistorialTest.php
php artisan test tests/Feature/Operario/OperarioCargaDescarteCap04Test.php --filter=anulacion
php artisan test
```

## Resultado (2026-09-28)

- `OperarioHistorialTest`: **20/20** OK (incluye flujo UI → Home sin totales + doble anulación)
- Suite completa: **815/815** OK

## Contrato

- `reglas.md` §9
- `permisos.md` — `RegistroOperativoPolicy::anular`

## Siguiente ID

AUD-03
