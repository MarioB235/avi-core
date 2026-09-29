# REP-01 — Confirmar catálogo de reportes v1

| Campo | Valor |
|--------|--------|
| ID | REP-01 |
| Estado | VERIFICADA |
| Fecha | 2026-09-29 |

## Objetivo

Cada salida v1 tiene destinatario, necesidad concreta y posición D05 (interno vs normativo bloqueado).

## Resultado

- 5 reportes operativos en `ReportesCatalogoV1`.
- Exclusiones MGAP/GBPEA documentadas (`exclusionesNormativasD05`).
- Matriz de roles en `permisos.md`.

## Verificación

```bash
php artisan test --compact tests/Unit/Support/ReportesCatalogoV1Test.php
pnpm run check:agent-docs
```

Resultado 2026-09-29: **1014/1014** tests, Pint OK, `check:agent-docs` OK.

## Pendiente humano

REP-14 — validación con cliente del responsable de producto.

## Siguiente

REP-02 — consulta compartida para Excel/PDF.
