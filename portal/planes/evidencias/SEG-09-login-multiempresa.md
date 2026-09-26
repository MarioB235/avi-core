# SEG-09 — Login multiempresa

**ID:** SEG-09  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/admin-resumen-galpon-select`

## Objetivo

Evitar ingreso a empresa incorrecta y no revelar cuentas en login con documento repetido entre empresas.

## Decisión MVP

Sin selector de empresa en pantalla: la **contraseña** desambigua cuando hay un solo match elegible; si hay varios elegibles con la misma clave → rechazo genérico.

## Implementación

| Pieza | Rol |
|-------|-----|
| `LoginCandidateResolver` | Busca activos por documento → filtra contraseña → filtra vigencia (`AccountAccessService`) |
| `AttemptLoginAction` | Delega resolución; mantiene rate limit y demo |

**Mejora clave:** duplicado con empresa suspendida + activa y misma clave → ingresa solo a la vigente (antes se rechazaba como ambiguo).

## Prueba de cierre

```bash
php artisan test --compact tests/Unit/Services/Auth/LoginCandidateResolverTest.php tests/Feature/Auth/MultiEmpresaLoginTest.php tests/Feature/Auth/LoginFlowTest.php
php artisan test --compact
vendor/bin/pint --test
pnpm run check:agent-docs
```

**Resultado 2026-09-26:** 553/553 OK · Pint OK · `check:agent-docs` OK.

## Casos cubiertos

- Mismo documento, claves distintas → empresa correcta
- Mismo documento y clave en dos empresas activas → rechazo sin nombres de empresa
- Duplicado bloqueado (suspendida) + elegible → login OK
- Mensaje genérico ante contraseña incorrecta

## Siguiente

**SEG-10** (recuperación y sesiones) o **ORQ-05** (tooling).
