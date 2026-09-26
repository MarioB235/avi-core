# EMP-03 — Configuración mínima de empresa

**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/emp-01-alta-empresa`

## Objetivo

Permitir configurar nombre, logo, zona horaria y unidades por empresa sin tabla adicional, reutilizando `empresas.configuracion`.

## Cambios

| Pieza | Implementación |
|---|---|
| Config | `EmpresaConfiguracion` — defaults y lectura JSON |
| Acción | `UpdateEmpresaConfiguracionAction` |
| Logo | `EmpresaLogoStorageService` + `EmpresaLogoPathGuard` |
| Unidades | `EmpresaHuevosUnidad` (base EMP-04) |
| UI | Diálogo «Configurar» en `/avicore/empresas` |

## Esquema `configuracion`

```json
{
  "zona_horaria": "America/Montevideo",
  "unidades": {
    "huevos_por_maple": 30,
    "maples_por_cajon": 12
  },
  "estado_historial": []
}
```

## Prueba de cierre

```bash
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

**Resultado:** 607/607 OK · Pint OK · `check:agent-docs` OK.

## Siguiente

**EMP-04** (usar unidades en pantallas y exportaciones) u **EMP-05** (onboarding).
