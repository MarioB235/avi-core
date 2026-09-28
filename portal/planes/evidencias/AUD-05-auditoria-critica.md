# AUD-05 — Auditoría crítica

**ID:** AUD-05  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/aud-historial-operario`

## Objetivo

Registrar acciones críticas con quién, qué, cuándo y por qué, sin exponer contraseñas ni tokens en metadata.

## Implementación

| Área | Cambio |
|------|--------|
| BD | Tabla `auditorias` — actor, categoría, acción, entidad, motivo, metadata, `occurred_at` |
| Servicio | `RegistrarAuditoriaAction` + `AuditoriaMetadataSanitizer` |
| Integración | Usuarios (alta/actualización/reset), empresas (alta/estado/config), soporte (inicio/fin), lotes (alta/actualización/transición), operación (anulación), corrección |
| Pendiente MOV | Categorías `movimiento` y `ajuste` reservadas; sin tabla `movimientos_aves` aún |

## Prueba de cierre

```bash
php artisan test tests/Feature/Auditoria/AuditoriaCriticaTest.php tests/Unit/Support/AuditoriaMetadataSanitizerTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- `AuditoriaCriticaTest` + `AuditoriaMetadataSanitizerTest`: **7/7** OK
- Suite completa: **832/832** OK · 2922 aserciones · Pint OK

## Contrato

- `esquema-bd.md` — tabla auditorias
- `criterios-modelo.md`
- `reglas.md` §10

## Siguiente ID

AUD-06
