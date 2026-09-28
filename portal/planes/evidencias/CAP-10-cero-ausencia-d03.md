# CAP-10 — Cero confirmado vs omisión (D03)

**ID:** CAP-10  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-27  
**Rama:** `feature/cap-est-operacion-estructura`

## Objetivo

Diferenciar **sin registro** (omisión) de **cero confirmado** en huevos, muertes y descarte de aves, sin implicar cierre diario obligatorio. Alimento excluido.

## Implementación

| Área | Cambio |
|------|--------|
| BD | `registros_operativos.cero_confirmado` (boolean, default false) |
| Dominio | `CapturaCeroEstado` — estados `omision`, `cero_confirmado`, `registrado` |
| Actions | `RegistrarCargaHuevos/Muertes/DescarteAction` — parámetro `$ceroConfirmado`; muertes/descarte no decrementan aves |
| Resumen | `OperarioGalponResumenService` expone `*_estado_hoy` por tipo |
| Livewire | `confirmarCeroHuevos/Muertes/Descarte` + partial `carga-cero-confirmar` |
| Home | KPIs muestran «Sin registro hoy» vs «0 confirmado hoy» |

## Prueba de cierre

```bash
php artisan migrate --env=testing --force
php artisan test tests/Feature/Operario/OperarioCargaCeroConfirmadoCap10Test.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28, post-auditoría)

- `CapturaCeroEstadoTest`: 6/6 OK
- `OperarioCargaCeroConfirmadoCap10Test`: 9/9 OK (Actions + Livewire huevos/muertes/descarte)
- `OperarioHistorialTest`: detalle «0 muertes (confirmado)» OK
- Suite completa: **813/813** OK · Pint OK · `check:agent-docs` OK

## Contrato

- `reglas.md` §8.8
- `esquema-bd.md` — `cero_confirmado`
- `pantallas-flujos.md` — nota CAP-10
