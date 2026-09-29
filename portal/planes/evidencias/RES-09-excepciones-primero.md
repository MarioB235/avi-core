# RES-09 — Excepciones primero en Inicio

**ID:** RES-09 · **Estado:** VERIFICADA  
**Fecha:** 2026-09-29 · **Rama:** `fix/demo-login-seed-readiness`

## Objetivo

Dueño/encargado ve qué galpón revisar primero, con enlace directo a Resumen filtrado, sin depender solo de tarjetas KPI.

## Resultado observable

- Bloque «Qué revisar primero» antes del pulso cuando hay alertas o capturas pendientes.
- Enlaces `Ver galpón en Resumen →` con query `galpon={id}`.

## Archivos

- `app/Support/InicioExcepcionesPulso.php`
- `resources/views/components/ui/pulse-exceptions.blade.php`
- `resources/views/pages/admin/home.blade.php`
- `app/Services/AdminHomeService.php`

## Verificación

```bash
php artisan test tests/Unit/Support/InicioExcepcionesPulsoTest.php tests/Feature/Services/AdminHomeServiceTest.php tests/Feature/Ui/AdminHomeViewTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

**Resultado 2026-09-29:** 1004/1004 · Pint OK · `check:agent-docs` OK.

## Siguiente ID

RES-10 — equipo real de solo lectura.
