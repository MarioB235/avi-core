# RES-10 — Equipo real de solo lectura

| Campo | Valor |
|--------|--------|
| ID | RES-10 |
| Estado | VERIFICADA |
| Fecha | 2026-09-29 |

## Objetivo

Dueño ve equipo en solo lectura: rol, área, documento enmascarado y estado de acceso; sin correo ni métricas de productividad laboral.

## Implementación

| Pieza | Detalle |
|--------|---------|
| `EquipoLectura` | DTO de fila + aviso `AVISO_SIN_PRODUCTIVIDAD` |
| `AdminHomeService::teamList` | Filas planas; `must_change_password` en query |
| UI | `livewire/admin/equipo/index` — aviso, tabla desktop, lista móvil con badge de estado |
| Copy KPI preview | Hints sin “rendimiento” |

## Verificación

```bash
php artisan test --compact tests/Feature/Admin/AdminEquipoTest.php tests/Feature/Services/AdminHomeServiceTest.php
php artisan test --compact
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

Resultado 2026-09-29: **1005/1005** tests, Pint OK, `check:agent-docs` OK.

## Contrato

- `reglas.md` §26
- `pantallas-flujos.md` §3.1.1
- `portal/CHANGELOG.md` 2026-09-29

## Siguiente

RES-11 — (plan maestro).
