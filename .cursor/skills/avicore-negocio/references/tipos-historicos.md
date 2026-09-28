# Tipos históricos de registro (AUD-09)

## `combinado` (legado)

| Aspecto | Decisión MVP |
|---------|----------------|
| Estado | Existe en BD y enum; **no** se crea en capturas nuevas |
| Lectura | Historial operario/supervisor muestra resumen multi-campo |
| Totales | `SUM(huevos/muertes/descarte_aves/alimento_kg)` incluye filas `combinado` activas |
| Anulación | Misma restauración de `aves_actuales` que muertes+descarte (`RegistroOperativoImpactoAves`) |
| Corrección | **No** — mensaje: anular y recargar por tipo separado |
| Migración a tipos separados | Opcional post-MVP (comando dedicado); no requerida para AUD-09 |

## Verificación

```bash
php artisan test tests/Feature/Auditoria/TipoCombinadoLegadoTest.php tests/Unit/Support/RegistroOperativoImpactoAvesTest.php
```
