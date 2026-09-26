# SEG-11 — Separar demo

**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/admin-resumen-galpon-select`

## Objetivo

Aislar el login demo de producción y de entornos con datos reales; evitar que el selector comparta un único usuario mutando roles entre demostradores.

## Cambios

| Área | Detalle |
|------|---------|
| `DemoLoginService` | `isEnabled()` exige flag + empresa `DEMO` + no-production; `resolveUser()` mapea rol→documento sin `save()` |
| `config/avicore.php` | `role_documentos` por rol; `empresa_codigo` |
| `AppServiceProvider` | fuerza `enabled_flag=false` en production |
| Seeders | Admin Avicore demo (`900000000`); operario María con `ultimo_galpon_id` |
| Docs | `demo.md` § 4, `reglas.md` §13, `pantallas-flujos.md`, `arranque-local.md` |

## Guards verificados

1. `APP_ENV=production` + flag `true` → selector oculto.
2. Flag `true` sin empresa `DEMO` → selector oculto (staging con datos reales).
3. Usuario con documento demo pero empresa distinta de `DEMO` → rechazado en `resolveUser`.
4. Login como Encargado no altera rol del Dueño (`000000000`).

## Prueba de cierre

```bash
php artisan test --compact --filter=DemoLogin
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

**Resultado:** 564/564 OK · Pint OK · `check:agent-docs` OK.

## Siguiente

**SEG-12** (superficies técnicas) o **ORQ-05** (tooling).
