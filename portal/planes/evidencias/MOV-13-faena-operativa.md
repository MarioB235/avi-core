# MOV-13 — Faena como salida operativa

**ID:** MOV-13  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness` (sin commit)

## Objetivo

Registrar salidas a faena en el ledger con destino, motivo y referencias internas opcionales, sin integración SMA automática (D05 fuera de MVP).

## Implementación

| Área | Cambio |
|------|--------|
| Action | `RegistrarFaenaAction` — tipo `faena`, metadata `destino_faena`, referencias, `trazabilidad_interna` |
| Origen ledger | `MovimientoAvesOrigen::FaenaOperativa` |
| UI | Tipo «Salida a faena» en `Admin\Movimientos\Index` + vista previa con aviso SMA |
| Reapertura | `ReabrirLoteExcepcionalAction` incluye cierre de ciclo por faena |

## Verificación

```bash
php artisan test tests/Feature/Movimientos/MovimientoAvesFaenaTest.php
php artisan test tests/Feature/Movimientos/
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

| Comando | Resultado |
|---------|-----------|
| `MovimientoAvesFaenaTest` | 5/5 OK |
| `tests/Feature/Movimientos/` | 64/64 OK |
| Suite completa | **939/939** OK · 3266 aserciones |
| Pint / `check:agent-docs` | OK |

## Contrato

- `movimientos-aves.md` § MOV-13 · `reglas.md` §11.2n · `pantallas-flujos.md` §3.7

## Siguiente

**RES-01** — Quitar previews ficticios de v1 productiva (bloque Resumen).
