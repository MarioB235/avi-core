# AUD-03 — Historial supervisor

**ID:** AUD-03  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/aud-historial-operario`

## Objetivo

Historial de supervisión con filtros empresa/granja/galpón/usuario/tipo/estado/período, independiente del historial móvil del operario.

## Implementación

| Área | Cambio |
|------|--------|
| Servicio | `AdminHistorialOperativoService` — union registros + vacunaciones, filtros, paginación 25 |
| DTO | `SupervisorHistorialItem`, `AdminHistorialOperativoFiltros` |
| UI | `Livewire/Admin/HistorialOperativo/Index` + vista admin con filtros y detalle readonly |
| Permisos | Gate `admin.viewHistorialOperativo` (mismo alcance que Resumen) |
| Nav | Pestaña **Historial** en `AdminNav` |
| Ruta | `/{dueno\|administrativo\|encargado}/historial-operativo` |

## Prueba de cierre

```bash
php artisan test tests/Feature/Admin/AdminHistorialOperativoTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- `AdminHistorialOperativoTest`: **5/5** OK
- Suite completa: **820/820** OK · 2872 aserciones · Pint OK · `check:agent-docs` OK

## Contrato

- `pantallas-flujos.md` §3.5
- `permisos.md` matriz
- `reglas.md` §9

## Siguiente ID

AUD-04
