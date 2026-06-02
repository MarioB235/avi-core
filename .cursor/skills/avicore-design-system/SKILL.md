---
name: avicore-design-system
description: Tokens, componentes y layouts UI AviCore — basado en awesome-design-skills "clean" + patrones enterprise, paleta verde/agro propia.
disable-model-invocation: true
---

# AviCore — Sistema de diseño

Usar junto con `avicore-ui` cuando el cambio sea transversal (tokens, componentes base, layouts).

## Fuentes

- **Registro externo:** [awesome-design-skills](https://github.com/bergside/awesome-design-skills) — skill base **clean**, patrones **enterprise**
- **Marca AviCore:** `docs/03-guia-visual-ui.md` (paleta verde/agro — prioridad sobre colores de skills genéricos)
- **Referencia técnica:** `docs/reference/sistema-diseno.md`
- **Implementación:** `resources/css/app.css`, `resources/views/components/ui/*`, `resources/views/components/layouts/*`

## Reglas no negociables

- Tokens `avicore-*` en Tailwind; sin inline styles
- WCAG 2.2 AA: `focus-visible`, contraste, touch ≥ 44px en operario
- Estados explícitos en botones/inputs (default, hover, focus-visible, disabled, error)
- **Clean:** whitespace, paleta limitada, sin motion decorativo, sin gradientes/sombras fuertes
- No adoptar temas oscuros, neón, glassmorphism, bento grids ni paletas de otros skills

## Componentes

`button`, `input`, `card`, `alert`, `badge`, `logo`, `icon`, `dialog`, `kpi-card`, `nav-link` — extender variantes aquí antes de duplicar clases en vistas.

**Auth:** `x-auth.support-contact-dialog` (sobre `x-ui.dialog`); iconos Lucide vía `IconSvg` + `resources/images/icons/` (color con `currentColor` + clases Tailwind, no SVG precoloreado).

Layouts reutilizan partials en `components/layouts/partials/` (`auth-brand-panel`, `admin-sidebar-inner`, `admin-nav`). Assets de marca: `public/images/brand/`; fondos desde `resources/images/brand/` + `scripts/optimize-brand-assets.py`. Config de soporte: `config/avicore.php`.

## Entrada

**Alcance** (token / componente / layout / pantalla), **modo** (web | operario), **objetivo**.
