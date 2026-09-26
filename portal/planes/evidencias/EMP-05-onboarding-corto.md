# EMP-05 — Onboarding corto

| Campo | Valor |
|---|---|
| ID | EMP-05 |
| Estado | VERIFICADA |
| Fecha | 2026-09-26 |
| Rama | `feature/emp-01-alta-empresa` |

## Objetivo

Checklist en Inicio admin: empresa → administrador → granja → galpón → lote/saldo → operario. Faltantes con enlace según permisos del rol.

## Implementación

| Pieza | Detalle |
|---|---|
| Servicio | `EmpresaOnboardingService::panelFor()` |
| Integración | `AdminHomeService` → `AdminHomeViewData::$onboarding` |
| UI | `x-ui.setup-checklist` en `pages/admin/home` (sección «Primeros pasos») |
| Visibilidad | Dueño, Administrativo, Encargado con empresa; oculta al completar |
| Enlaces | Estructura/Usuarios si el rol puede; lote → `operario/cargar?form=lote` para dueño/encargado |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Services/EmpresaOnboardingServiceTest.php tests/Feature/Ui/AdminHomeViewTest.php
```

Resultado: **9/9 OK** (2026-09-26).

## Siguiente

**EMP-06** (soporte auditado) u **ORQ-05** (tooling).
