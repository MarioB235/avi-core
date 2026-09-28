# AUD-06 — Atomicidad auditoría + mutación

**ID:** AUD-06  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/aud-historial-operario`

## Objetivo

Si falla el registro de auditoría requerido, la mutación de negocio se revierte por completo (sin saldo parcial ni estado inconsistente).

## Implementación

| Área | Cambio |
|------|--------|
| Excepción | `AuditoriaCriticaException` al fallar `RegistrarAuditoriaAction` |
| Patrón | Cada Action envuelve mutación + auditoría en `DB::transaction` (sin helper intermedio) |
| Actions | Usuarios, empresas, lotes, soporte, movimientos (entrada externa), anulación y corrección |
| UI admin | Filtros de fecha compartidos vía `AdminFiltroFechasOperativas` + `DiaOperativoEmpresa` |

## Prueba de cierre

```bash
php artisan test tests/Feature/Auditoria/AuditoriaAtomicidadTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- `AuditoriaAtomicidadTest`: **3/3** OK
- Post-auditoría msg 3: eliminado `MutacionConAuditoriaCritica` (YAGNI; patrón directo en Actions)
- Suite completa: **878/878** OK · Pint OK

## Contrato

- `reglas.md` §10 (AUD-06)

## Siguiente ID

AUD-07
