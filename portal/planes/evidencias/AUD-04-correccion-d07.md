# AUD-04 — Corrección con D07

**ID:** AUD-04  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-28  
**Rama:** `feature/aud-historial-operario`

## Objetivo

Corrección trazable de registros operativos: antes/después, motivo, actor, fecha efectiva y vínculo al original; saldo y totales cambian una sola vez.

## Implementación

| Área | Cambio |
|------|--------|
| BD | `correcciones_registro_operativo` — JSON antes/después, motivo, actor, fecha efectiva |
| Modelo | `CorreccionRegistroOperativo`, relación `RegistroOperativo::correcciones()` |
| Negocio | `CorregirRegistroOperativoAction` — lock, delta aves en muertes/descarte, registro activo actualizado |
| Soporte | `RegistroOperativoCorreccion` — snapshot, validación por tipo |
| Permisos | `RegistroOperativoPolicy::corregir` (dueño/administrativo/encargado, activo, misma empresa) |
| UI | Historial supervisor — botón «Corregir registro», formulario y historial de correcciones en detalle |

## Prueba de cierre

```bash
php artisan test tests/Feature/Admin/AdminHistorialOperativoCorreccionTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-28)

- `AdminHistorialOperativoCorreccionTest`: **5/5** OK
- Suite completa: **825/825** OK · 2897 aserciones · Pint OK

## Contrato

- `esquema-bd.md` — tabla correcciones
- `reglas.md` §10 — D07 / AUD-04
- `permisos.md` — `corregir`
- `pantallas-flujos.md` §3.5

## Siguiente ID

AUD-05
