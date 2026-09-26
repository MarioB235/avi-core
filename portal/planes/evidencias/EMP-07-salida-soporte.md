# EMP-07 — Salida de soporte

| Campo | Valor |
|---|---|
| ID | EMP-07 |
| Estado | VERIFICADA |
| Fecha | 2026-09-26 |
| Rama | `feature/emp-01-alta-empresa` |

## Objetivo

Al salir del modo soporte limpiar el override de empresa, validar el destino de redirección y registrar acciones de la sesión. El contexto de la empresa A no debe contaminar B al cambiar cliente.

## Implementación

| Pieza | Detalle |
|---|---|
| BD | `soporte_sesiones.acciones` (JSON): `inicio`, `consulta_resumen`, `fin` (+ `reason`) |
| Servicio | `SoporteEmpresaService::recordAccion()`, `resolveExitRoute()`, `entryRouteName()` |
| Salida | `POST avicore/soporte/finalizar` (destino opcional validado contra `config/avicore.php`); logout registra `fin` con `logout` |
| Cambio empresa | `closeOpenSessionsForActor` cierra sesión previa con `replaced` antes de abrir otra |
| UI | Resumen admin registra `consulta_resumen` al montar con soporte activo |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Admin/AdminSoporteEmpresaTest.php
php artisan test --compact
```

Resultado: **9/9** soporte + **623/623** suite (2026-09-26).

## Siguiente

**EMP-08** (datos personales) u **ORQ-05** (tooling).
