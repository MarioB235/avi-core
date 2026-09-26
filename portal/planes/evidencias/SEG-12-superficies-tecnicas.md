# SEG-12 — Superficies técnicas

**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/admin-resumen-galpon-select`

## Objetivo

Cerrar superficies técnicas de seguridad: CSRF, escape, archivos/logo, HTTPS/cookies y dependencias.

## Cambios

| Superficie | Implementación |
|---|---|
| CSRF | Ruta `logout` en grupo `web` + `auth`; meta `csrf-token` en layouts; formulario logout con `@csrf` |
| Escape / assets | `SafeAssetName` en `IconSvg` e `IllustrationSvg` (bloquea traversal en nombres) |
| Logo (futuro EMP-03) | `EmpresaLogoPathGuard` — solo `empresas/logos/`, sin URL ni `..` |
| HTTPS/cookies | `ProductionSecurityConfig` fuerza `session.secure` y `same_site=lax` en `production` |
| Dependencias | `composer audit --locked` (CI) + `pnpm run check:security` |

## Prueba de cierre

```bash
php artisan test --compact tests/Feature/Security tests/Unit/Support tests/Unit/Services/EmpresaLogoPathGuardTest.php
php artisan test --compact
pnpm run check:security
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

**Resultado:** 588/588 OK · Pint OK · `check:agent-docs` OK.

## Siguiente

Bloque **SEG** cerrado. Continuar con **EMP-01** (alta empresa real) u **ORQ-05** (tooling auditoría).
