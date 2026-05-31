# Sistema de diseño AviCore

Referencia técnica para implementar UI con Tailwind 4 y componentes Blade en `resources/views/components/ui/`.

## Origen y elección de guía externa

Registro evaluado: [awesome-design-skills](https://github.com/bergside/awesome-design-skills) (Type UI).

| Skill evaluado | Motivo de descarte o uso |
|----------------|--------------------------|
| **clean** | **Base adoptada** — rejilla 8pt, whitespace, estados explícitos, WCAG 2.2 AA |
| **enterprise** | **Patrones de layout** — jerarquía y flujos orientados a datos (sin copiar su paleta teal/naranja) |
| dashboard | Tema oscuro; no encaja con identidad agro clara |
| application | Morado / top-bar only; no encaja con sidebar administrativo |
| glassmorphism / neon / etc. | Decorativo; contradice `docs/03-guia-visual-ui.md` |

**Paleta de marca:** siempre `docs/03-guia-visual-ui.md` (verde/agro). Los skills genéricos no reemplazan colores AviCore.

## Tokens (fuente: `resources/css/app.css`)

| Token | Uso |
|-------|-----|
| `avicore-primary` / `secondary` / `soft` | Marca y fondos de acento |
| `avicore-surface` / `card` | Fondo app y tarjetas |
| `avicore-text` / `muted` | Texto principal y secundario |
| `avicore-border` / `border-strong` | Bordes de inputs y paneles |
| `avicore-success` / `warning` / `danger` / `info` | Estados semánticos |

Clases de utilidad en capa `components`: `.avicore-page-title`, `.avicore-nav-link`, `.avicore-kpi-value`, etc.

## Componentes UI

| Componente | Variantes / notas |
|------------|-------------------|
| `x-ui.button` | `primary`, `secondary`, `danger`, `ghost` — min-h 44px, `focus-visible` |
| `x-ui.input` | `aria-invalid`, estados error, `hint` opcional |
| `x-ui.card` | `padding`: `default`, `compact`, `none` |
| `x-ui.alert` | `info`, `success`, `warning`, `danger` |
| `x-ui.badge` | `neutral`, `primary`, `success`, `warning`, `danger`, `info` |
| `x-ui.nav-link` | `active`, `disabled`, `href` |
| `x-ui.logo` | Marca en login y sidebar |

## Layouts

| Layout | Archivo | Uso |
|--------|---------|-----|
| Público | `components/layouts/public.blade.php` | Login, cambio de contraseña |
| Admin | `components/layouts/admin.blade.php` | Panel web con sidebar |
| Operario | `components/layouts/operario-mobile.blade.php` | Vista móvil en campo |

## Reglas de implementación (quality gates)

1. Preferir tokens semánticos (`avicore-*`) sobre valores hex sueltos en Blade.
2. Todo control interactivo: estados `hover`, `focus-visible`, `active`, `disabled` (y `loading` con Livewire cuando aplique).
3. Contraste texto/fondo ≥ WCAG AA; no texto `muted` sobre fondos de bajo contraste.
4. Touch targets ≥ 44px en operario (`min-h-11` en botones e inputs).
5. Sin estilos inline; sin `!important` en Tailwind.
6. `prefers-reduced-motion` respetado en `app.css`.

## Actualizar el sistema

Si se incorpora otro skill del registro:

1. Verificar compatibilidad con verde/agro en `03-guia-visual-ui.md`.
2. Fusionar solo patrones (espaciado, accesibilidad, anatomía de componentes).
3. Actualizar esta referencia + línea en `CHANGELOG.md`.
4. Skill interno: `.cursor/skills/avicore-design-system/SKILL.md`.

## Enlaces

- Guía de producto (identidad): [`../03-guia-visual-ui.md`](../03-guia-visual-ui.md)
- Pantallas y flujos: [`../02-pantallas-y-flujos.md`](../02-pantallas-y-flujos.md)
- Estándares código: [`estandares-codigo.md`](estandares-codigo.md)
