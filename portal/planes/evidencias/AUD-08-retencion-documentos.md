# AUD-08 — Retención/documentos (D07)

**ID:** AUD-08  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/aud-historial-operario`

## Objetivo

Aplicar D07 con plazos operativos acordados (sin inventar exigencias legales) y conservar versiones emitidas de reportes como copias inmutables.

## Implementación

| Área | Cambio |
|------|--------|
| Config | `avicore.retencion.d07` — plazos en meses (default 60), purga deshabilitada |
| Política | `PoliticaRetencionD07` + `retencion-d07.md` |
| BD | `documentos_emitidos` — checksum, filtros, fecha de corte |
| Acción | `RegistrarDocumentoEmitidoAction` — almacena PDF/XLSX sin sobrescribir |
| Inmutabilidad | `PreventsHardDelete` en registros, vacunaciones, correcciones, auditorías y documentos emitidos |
| Ops | `php artisan avicore:retencion-d07` — resumen verificable |

## Prueba de cierre

```bash
php artisan test tests/Feature/Auditoria/RetencionD07Test.php tests/Unit/Support/PoliticaRetencionD07Test.php
php artisan test
php artisan avicore:retencion-d07
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- `RetencionD07Test` + `PoliticaRetencionD07Test`: **5/5** OK
- Suite completa: **846/846** OK · 2972 aserciones · Pint OK · `check:agent-docs` OK

## Contrato

- `retencion-d07.md` · `reglas.md` §10 · `esquema-bd.md` · `datos-personales.md` §3

## Siguiente ID

**AUD-09** — Tipos históricos (`combinado`) — ver [AUD-09-tipos-historicos-combinado.md](AUD-09-tipos-historicos-combinado.md).
