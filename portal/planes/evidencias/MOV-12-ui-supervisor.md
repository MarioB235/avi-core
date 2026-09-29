# MOV-12 — UI supervisor (movimientos)

**ID:** MOV-12  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness` (sin commit)

## Objetivo

Pantalla de supervisor con **vista previa del efecto** y **motivo obligatorio** antes de confirmar traslado, entrada, ajuste o cierre; operario sin acceso.

## Implementación

| Área | Cambio |
|------|--------|
| Servicio | `MovimientoAvesVistaPreviaService` |
| Livewire | `Admin\Movimientos\Index` + vista `livewire/admin/movimientos/index.blade.php` |
| Gate | `admin.viewMovimientos` en `AdminModulePolicy` / `AppServiceProvider` |
| Ruta | `/{rol}/movimientos` |
| Nav | Pestaña Movimientos en `AdminNav` (roles con `canManageLotes`) |
| Tests nav | Actualizados `NavTabBarItemsTest`, `RolePanelModulesTest`, `AdminShellTest`, `OperarioPerfilTest` |

## Verificación

```bash
php artisan test tests/Feature/Admin/AdminMovimientosSupervisorTest.php tests/Unit/Services/MovimientoAvesVistaPreviaServiceTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

| Comando | Resultado |
|---------|-----------|
| Tests MOV-12 | 5/5 OK |
| Suite completa | **934/934** OK |
| Pint / `check:agent-docs` | OK |

## Contrato

- `movimientos-aves.md` § MOV-12 · `reglas.md` §11.2m · `permisos.md` (gate) · `pantallas-flujos.md` §3.7 · `estado-capacidades.md`

## Siguiente

**MOV-13** — Faena como salida operativa.
