# Elevación y superficies — Refined Agro

## Escala de elevación AviCore

| Nivel | Clase / token | Uso |
|-------|---------------|-----|
| 0 | `bg-avicore-surface` | Fondo de app |
| 1 | `bg-avicore-card border border-avicore-border` | Card KPI, formularios |
| 2 | `shadow-sm border border-avicore-border/80` | Cards auth, paneles elevados |
| 3 | `shadow-md` | Modal panel, dropdown |
| 4 | Dock operario integrado | `.avicore-operario-tab-bar` — `rounded-t-[1.75rem]`, sombra hacia arriba; ítem activo con círculo `avicore-primary` sobresaliente (`operario.css`) |

## Bordes

- Default: `border-avicore-border`
- Sutil en chrome flotante: `border-avicore-border/40`
- Fuerte en inputs focus: `border-avicore-border-strong` o ring `focus-visible`

## backdrop-blur

**Solo permitido en:**

- Telón de modal (opcional blur ligero en backdrop — ya en dialog)

**No** en: fondo de página, cards KPI, sidebar admin completo.

## Cards (inspiración Soft UI, paleta AviCore)

```html
<div class="rounded-xl border border-avicore-border/80 bg-avicore-card p-5 shadow-sm
            md:transition-shadow md:duration-200 md:hover:shadow-md">
```

- Radio: `rounded-xl` cards operario/admin; `rounded-2xl` dock nav.
- Sin gradientes en superficie de card.

## Separación contenido / chrome

- **Chrome:** header, sidebar, bottom nav — fijo o sticky, elevación mayor.
- **Contenido:** scroll en `main`; cards dentro con elevación menor.

## Auth (excepciones documentadas)

- Tarjeta `.avicore-auth-card` sobre foto: `shadow-sm`/`shadow-md`
- Hero admin: imagen + degradado inferior — excepción al «sin capas extra»
