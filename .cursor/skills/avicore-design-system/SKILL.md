---
name: avicore-design-system
description: Tokens, componentes y layouts UI AviCore — paleta verde/agro, Refined Agro (motion/elevación controlados), Tailwind 4, componentes Blade x-ui.*. Usar al cambiar tokens, componentes base, layouts transversales o identidad visual.
---

# AviCore — Sistema de diseño

Usar junto con `avicore-ui` cuando el cambio sea transversal (tokens, componentes base, layouts).

## Leer primero

| Referencia | Contenido |
|------------|-----------|
| [`references/refined-agro-principios.md`](references/refined-agro-principios.md) | Contrato Refined Agro (lista blanca/negra) |
| [`references/motion-y-feedback.md`](references/motion-y-feedback.md) | Duraciones, mobile vs admin, reduced-motion |
| [`references/elevacion-y-superficies.md`](references/elevacion-y-superficies.md) | Sombras, blur, cards |
| [`references/guia-visual.md`](references/guia-visual.md) | Identidad y paleta |
| [`references/tokens-componentes.md`](references/tokens-componentes.md) | Tokens, componentes, quality gates |
| [`references/ejemplos-snippet.md`](references/ejemplos-snippet.md) | Snippets Blade+Tailwind copiables |
| [`references/INDICE-TALL-REFERENCIA.md`](references/INDICE-TALL-REFERENCIA.md) | Patrones TALL (solo inspiración) |

## Implementación

`resources/css/app.css`, `resources/views/components/ui/*`, `resources/views/components/layouts/*`

## Reglas no negociables

- Tokens `avicore-*` en Tailwind; sin inline styles
- WCAG 2.2 AA: `focus-visible`, contraste, touch ≥ 44px en operario
- **Refined Agro:** motion con propósito; hover solo `md:`+ en admin; blur solo en chrome fijo

## Entrada

**Alcance** (token / componente / layout), **modo** (web | operario), **objetivo**.
