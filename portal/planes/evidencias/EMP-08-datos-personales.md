# EMP-08 — Datos personales

| Campo | Valor |
|---|---|
| ID | EMP-08 |
| Estado | VERIFICADA |
| Fecha | 2026-09-26 |
| Rama | `feature/emp-01-alta-empresa` |

## Objetivo

Inventario y política operativa verificable; minimizar documento en vistas de solo lectura y preparar exportaciones futuras. Sin afirmar cumplimiento legal.

## Implementación

| Pieza | Detalle |
|---|---|
| Política | `.cursor/skills/avicore-negocio/references/datos-personales.md` |
| Helper | `App\Support\DatosPersonales` (`maskDocumento`, `canViewFullDocumento`, `documentoParaVista`) |
| UI | `x-ui.documento-label` en Equipo (enmascarado) y Usuarios (completo si gestiona usuarios) |
| Config | `config/avicore.php` → `datos_personales` (dígitos visibles, retención acordada) |

## Prueba de cierre

```bash
php artisan test --compact tests/Unit/Support/DatosPersonalesTest.php tests/Feature/Admin/AdminEquipoTest.php
php artisan test --compact
```

## Siguiente

Bloque **EMP** cerrado → **EST-01** (granjas completas) u **ORQ-05**.
