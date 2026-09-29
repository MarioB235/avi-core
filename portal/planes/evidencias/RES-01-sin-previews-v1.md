# RES-01 — Sin previews ficticios en v1

**ID:** RES-01  
**Fecha:** 2026-09-28  
**Rama:** `fix/demo-login-seed-readiness` (sin commit)

## Objetivo

Que la experiencia productiva del dueño/admin no mezcle KPIs de galpón reales con stock, demanda, mapa o ventas inventadas.

## Implementación

| Área | Cambio |
|------|--------|
| Inicio | `stockPreviewFor()` siempre `show: false` — sin sección «Stock y demanda» |
| Comercial | `UserRole::canViewComercial()` → false; sin tab en `AdminNav`; ruta 403 |
| Servicio | `comercialPreviewItems()` / `comercialClientMap()` vacíos; datos demo eliminados |
| Vista comercial | Empty state etapa 2 (no mapa ni montos) |

## Verificación

```bash
php artisan test tests/Feature/Services/AdminHomeServiceTest.php tests/Feature/Ui/AdminHomeViewTest.php tests/Feature/Admin/AdminComercialTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

| Comando | Resultado |
|---------|-----------|
| Suite completa | **938/938** OK · 3228 aserciones |
| Pint / `check:agent-docs` | OK |

## Contrato

- `reglas.md` §17 · `pantallas-flujos.md` §3 / §3.1.2 · `estado-capacidades.md`

## Siguiente

**RES-02** — Definir métricas canónicas de Resumen.
