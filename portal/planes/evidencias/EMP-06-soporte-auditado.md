# EMP-06 — Soporte auditado

| Campo | Valor |
|---|---|
| ID | EMP-06 |
| Estado | VERIFICADA |
| Fecha | 2026-09-26 |
| Rama | `feature/emp-01-alta-empresa` |

## Objetivo

Admin AviCore accede a datos operativos de un cliente solo con sesión de soporte auditada (empresa, motivo, actor, inicio/fin, caducidad), banner visible y permisos mínimos (lectura operativa; sin mutaciones productivas).

## Implementación

| Pieza | Detalle |
|---|---|
| BD | Tabla `soporte_sesiones` (empresa, actor, motivo, `started_at`, `expires_at`, `ended_at`, `end_reason`) |
| Servicios | `SoporteEmpresaService` (sesión `avicore.soporte_sesion_id`, caducidad, banner); `EmpresaContextService::empresaIdFor()` |
| Actions | `StartSoporteEmpresaAction`, `EndSoporteEmpresaAction` |
| Scope | Admin sin soporte: `EmpresaScopeService` devuelve `1=0` en datos operativos |
| Policies | `EmpresaPolicy::enterSupport`; `AdminModulePolicy` + `LotePolicy` bloquean resumen/mutaciones sin soporte |
| UI | Empresas → «Soporte» (motivo ≥10 chars); banner `x-admin.support-banner`; tab Resumen solo con soporte activo |
| Rutas | `POST avicore/soporte/finalizar`; logout cierra sesión de soporte (`end_reason=logout`) |
| Config | `config/avicore.php` → `soporte.duracion_minutos`, `motivo_min_caracteres` |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Admin/AdminSoporteEmpresaTest.php
php artisan test --compact
```

Resultado: **6/6** soporte + **620/620** suite (2026-09-26).

## Siguiente

**EMP-07** — ver [EMP-07-salida-soporte.md](EMP-07-salida-soporte.md).
