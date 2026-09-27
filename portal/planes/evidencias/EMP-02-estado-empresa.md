# EMP-02 — Activar / suspender / reactivar empresa

**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/emp-01-alta-empresa`

## Objetivo

Permitir a Admin AviCore cambiar el estado de una empresa con motivo auditado y efecto inmediato sobre sesiones, sin borrar historia operativa.

## Cambios

| Pieza | Implementación |
|---|---|
| Acción | `UpdateEmpresaEstadoAction` — motivo obligatorio, historial JSON, invalidación sesiones |
| Sesiones | `UserSessionService::invalidateAllForEmpresa` cuando estado ≠ activa |
| Vigencia | Reutiliza SEG-02 (`AccountAccessService` + `EnsureAccountVigente`) |
| UI | Diálogo «Cambiar estado» en `/avicore/empresas` |
| Protección | Empresa `DEMO` no modificable |

## Prueba de cierre

```bash
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

**Resultado:** 602/602 OK · Pint OK · `check:agent-docs` OK.

## Resultado observable

- Suspender empresa → usuarios con sesión abierta pierden acceso en el siguiente request.
- Reactivar con motivo → historial con actor y fecha en `configuracion`.
- Granjas/registros existentes no se eliminan.

## Siguiente

**EMP-03** (configuración mínima: logo, zona horaria, unidades).
