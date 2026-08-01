---
name: avicore-ui
description: Diseña o modifica UI AviCore — pantallas web administrativas (login, dashboard, CRUDs) o vista móvil del operario (carga en campo). Refined Agro con patrones mobile vs web. Usar al crear o cambiar pantallas, layouts, flujos UX o módulo operario móvil.
---

# AviCore — UI

El usuario indica **Modo: web** o **Modo: operario**.

## Leer primero

| Referencia | Contenido |
|------------|-----------|
| [`references/pantallas-flujos.md`](references/pantallas-flujos.md) | Pantallas, campos, flujos |
| [`references/patrones-mobile-operario.md`](references/patrones-mobile-operario.md) | **Modo operario** — thumb zone, bottom nav, sin hover |
| [`references/patrones-desktop-operario.md`](references/patrones-desktop-operario.md) | **Operario escritorio** — sidebar, contenido ancho, grillas |
| [`references/patrones-web-admin.md`](references/patrones-web-admin.md) | **Modo web** — shell tipo operario + tabs/contenido Dueño |
| [`references/checklist-ui-por-pantalla.md`](references/checklist-ui-por-pantalla.md) | Checklist antes de cerrar UI |
| `avicore-design-system` | `refined-agro-principios`, `tokens-componentes`, `ejemplos-snippet` |
| `avicore-negocio` | Permisos y reglas si afectan UI |

## Modo web (admin)

- Leer `patrones-web-admin.md`.
- Identidad verde/agro; Tailwind; sin inline; componentes `x-ui.*`; responsive.
- `hover:` solo desde `md:` en filas, nav y cards.
- Auth: layout público — split escritorio, bottom sheet móvil.
- Si persiste datos: validaciones, permisos, `empresa_id`, auditoría si crítico.

## Modo operario

- Leer `patrones-mobile-operario.md` (< 1024px).
- Leer `patrones-desktop-operario.md` (≥ 1024px).
- Vista móvil simplificada; galpón visible; `wire:navigate` en nav.
- Sin `hover` como feedback principal; botones ≥ 44px; `active:` táctil.
- Confirmación al guardar; últimas cargas del día.
- Reglas: `avicore-negocio/references/reglas.md`.

## Cierre

Antes de terminar: `references/checklist-ui-por-pantalla.md`.

## Entrada

**Modo**, **Pantalla/flujo**, **Objetivo**.
