# EMP-04 — Unidades consistentes por empresa

**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/emp-01-alta-empresa`

## Objetivo

Que Inicio admin, Resumen y operario calculen maples/cajas/sobrantes con la configuración de la empresa, sin perder huevos sueltos.

## Cambios

| Área | Implementación |
|---|---|
| Resolver | `HuevosUnidad::para($empresa)` → `EmpresaHuevosUnidad` |
| Pantallas | `AdminHomeService`, `Admin/Resumen`, `OperarioGalponResumenService` |
| Sobrantes | Etiquetas y hints muestran huevos sueltos cuando no completan maple |
| Export | Sin módulo REP aún; contrato: usar mismo resolver al implementar |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Services/EmpresaUnidadesConsistenciaTest.php tests/Unit/Support/HuevosUnidadTest.php
php artisan test --compact
```

**Resultado:** 610/610 OK.

## Siguiente

**EMP-05** (onboarding corto) u **ORQ-05** (tooling).
