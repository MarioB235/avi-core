# CAP-13 — Formularios obsoletos

**ID:** CAP-13  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/cap-est-operacion-estructura`

## Objetivo

Cambios de galpón o rol con formulario de captura abierto deben revalidarse: cierre + reset del diálogo, sin datos cruzados ni falsa confirmación.

## Implementación

| Área | Cambio |
|------|--------|
| Contexto | `ManagesCapturaContexto` registra `ultimo_galpon_id` + rol al abrir diálogo |
| Galpón | `afterSeleccionarGalpon` en `CargarHub` invalida formularios si cambió el galpón |
| Hydrate | Revalidación en cada request Livewire; mensajes según causa (galpón, rol, indisponible) |
| Guardado | `abortarSiCapturaObsoleta` en `resolveGalponParaGuardar` / `guardarLote` |
| Selector | Si no queda galpón tras invalidar, abre `selectorGalponAbierto` (regresión CAP-01) |

## Prueba de cierre

```bash
php artisan test tests/Feature/Operario/OperarioFormulariosObsoletosCap13Test.php
php artisan test tests/Feature/Operario/OperarioGalponSelectorTest.php
php artisan test tests/Feature/Operario/OperarioCargaHuevosTest.php --filter=guardar_huevos_abre_selector
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- `OperarioFormulariosObsoletosCap13Test`: 4/4 OK
- Regresión selector + guardar galpón no disponible: OK
- Suite completa: **792/792** OK · Pint OK · `check:agent-docs` OK
- Estabilización nocturna: timestamps en `OperarioHistorialTest` alineados a `DiaOperativoEmpresa` (CAP-11)

## Contrato

- `reglas.md` §8.11
- `pantallas-flujos.md` — nota CAP-13
