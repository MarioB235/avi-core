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
- Estados explícitos en botones/inputs (default, hover, focus-visible, active, disabled, error)
- No adoptar temas oscuros, neón, glassmorphism ni paletas moradas/teal de otros skills

## Componentes

`button`, `input`, `card`, `alert`, `badge`, `logo`, `nav-link` — extender variantes aquí antes de duplicar clases en vistas.

## Entrada

**Alcance** (token / componente / layout / pantalla), **modo** (web | operario), **objetivo**.
