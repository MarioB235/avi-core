# AUD-07 — Consulta autorizada de auditoría

**ID:** AUD-07  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/aud-historial-operario`

## Objetivo

Pantalla de consulta de la bitácora crítica con filtros y detalle, sin edición ni borrado; operario sin acceso y datos aislados por empresa.

## Implementación

| Área | Cambio |
|------|--------|
| Gate | `admin.viewAuditoria` — dueño, administrativo, encargado; soporte AviCore con sesión activa |
| Servicio | `AdminAuditoriaConsultaService` — paginación, filtros, `forEmpresa` |
| UI | `Livewire\Admin\Auditoria\Index` + vistas; pestaña Auditoría en `AdminNav` |
| Ruta | `/{rol}/auditoria` |
| Presentación | `AuditoriaPresentacion` — títulos y líneas de detalle |

## Prueba de cierre

```bash
php artisan test tests/Feature/Admin/AdminAuditoriaConsultaTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- `AdminAuditoriaConsultaTest` + `AuditoriaPresentacionTest`: **5/5** OK
- Suite completa: **841/841** OK · 2960 aserciones · Pint OK · `check:agent-docs` OK

## Contrato

- `reglas.md` §10 · `pantallas-flujos.md` §3.6

## Siguiente ID

**AUD-08** — Retención/documentos (D07).
