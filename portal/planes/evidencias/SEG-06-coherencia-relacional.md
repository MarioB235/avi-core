# SEG-06 — Coherencia relacional

**ID:** SEG-06  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-26  
**Rama:** `feature/admin-resumen-galpon-select`

## Objetivo

Garantizar que padre, hijo y actor pertenezcan a la misma empresa y jerarquía (granja→galpón→lote). Un ID existente pero incoherente debe rechazarse en la Action, no persistirse.

## Implementación

| Pieza | Rol |
|---|---|
| `EmpresaRelationalGuard` | Punto canónico: `assertGranjaOfEmpresa`, `assertGranjaMatchesGalponEmpresa`, `assertGalponOfActor`, `assertLoteOfActor`, `assertLoteBelongsToGalpon` |
| Actions estructura/lotes | `CreateGalponAction`, `UpdateGalponAction`, `RegistrarLoteAction` |
| Actions operación | Cargas (huevos, muertes, alimento, descarte), `RegistrarVacunacionAction` |
| Operario | `OperarioGalponService::seleccionarGalpon` valida actor↔galpón |

SEG-05 cubre **aislamiento entre empresas** (scope en queries). SEG-06 cubre **coherencia dentro de la jerarquía** (misma empresa, padre correcto).

## Prueba de cierre

```bash
php artisan test --compact tests/Unit/Services/EmpresaRelationalGuardTest.php tests/Feature/Auth/RelationalCoherenceTest.php
php artisan test --compact
vendor/bin/pint --test
pnpm run check:agent-docs
```

**Resultado 2026-09-26:** 459/459 OK · Pint OK · `check:agent-docs` OK.

## Casos cubiertos (nuevos)

- Granja ajena al actualizar galpón (`UpdateGalponAction`)
- Lote de otro galpón en vacunación, misma empresa (`RegistrarVacunacionAction`)
- Alta de lote con galpón de otra empresa vía Estructura Livewire (`assertNotFound`)
- Unit: granja ajena, lote↔galpón incoherente, galpón ajeno al actor

## Docs

- `permisos.md` §7 — párrafo SEG-06
- `arquitectura.md` — bullet coherencia padre/hijo
- `portal/CHANGELOG.md`

## Siguiente

**SEG-07** (cerrar D02 y matriz permisos) o **ORQ-05** (tooling).
