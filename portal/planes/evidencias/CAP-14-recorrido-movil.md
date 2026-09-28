# CAP-14 — Recorrido móvil

**ID:** CAP-14  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/cap-est-operacion-estructura`

## Objetivo

Un operario completa en móvil, sin asistencia técnica: login → galpón → capturas → historial → anular con motivo.

## Implementación

| Área | Cambio |
|------|--------|
| E2E | `OperarioRecorridoMovilCap14Test` encadena login Livewire, selector galpón, huevos + muertes, historial y anulación |
| Shell móvil | Asserts `viewport-fit=cover`, dock inferior, `wire:navigate`, snackbar host en Inicio/Cargar/Historial |
| UX campo | Teclado numérico (`inputmode="numeric"`) verificado con diálogo huevos abierto |
| Negocio | Anulación restaura `aves_actuales`; huevos del día permanece tras anular muertes |

## Prueba de cierre

```bash
php artisan test tests/Feature/Operario/OperarioRecorridoMovilCap14Test.php
php artisan test tests/Feature/Ui/OperarioBottomNavTest.php
php artisan test tests/Feature/Operario/OperarioHistorialTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- `OperarioRecorridoMovilCap14Test`: 2/2 OK
- Suite completa: **794/794** OK · 2782 aserciones · Pint OK · `check:agent-docs` OK

## Contrato

- `reglas.md` §8.12
- `pantallas-flujos.md` — nota CAP-14
- `patrones-mobile-operario.md` — tests CAP-14
