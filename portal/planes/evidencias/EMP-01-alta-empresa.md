# EMP-01 — Alta de empresa real

**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/emp-01-alta-empresa`

## Objetivo

Permitir alta de empresa cliente real (nombre, identificador, estado) con Dueño inicial en transacción, sin depender del seed demo.

## Cambios

| Pieza | Implementación |
|---|---|
| Acción | `CreateEmpresaAction` — transacción `empresas` + `users` (rol Dueño, clave temporal) |
| Autorización | `EmpresaPolicy` — solo Admin AviCore (`UserRole::canManageEmpresas`) |
| UI | `Livewire/Admin/Empresas/Index` en `/avicore/empresas` |
| Navegación | Tab «Empresas» en `AdminNav` para Admin AviCore |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Admin/AdminEmpresasTest.php tests/Feature/Ui/AdminShellTest.php
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

**Resultado:** 598/598 OK · Pint OK · `check:agent-docs` OK.

## Resultado observable

- Admin AviCore crea empresa con código único y Dueño inicial.
- Dueño puede iniciar sesión y ver panel de empresa vacía (sin granjas).
- Roles distintos de Admin AviCore reciben 403 en `/dueno/empresas`.

## Referencia canónica

- `avicore-ui/references/pantallas-flujos.md` § 3.1.5
- `avicore-negocio/references/reglas.md` § 1.5
- `avicore-contexto/references/estado-capacidades.md`

## Siguiente

**EMP-02** (activar/suspender/reactivar empresa) u **EMP-03** (configuración mínima).
