# Ejemplos snippet — Refined Agro (tokens AviCore)

Copiar y adaptar; usar `x-ui.*` cuando exista equivalente.

## Botón primario (móvil + desktop)

```html
<x-ui.button variant="primary" class="w-full sm:w-auto">
    Guardar carga
</x-ui.button>
```

Equivalente manual:

```html
<button type="submit"
    class="inline-flex min-h-11 items-center justify-center rounded-lg bg-avicore-primary px-5 py-2.5
           text-sm font-medium text-white shadow-sm
           transition-colors duration-200
           hover:bg-avicore-secondary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-avicore-primary
           active:scale-[0.98] disabled:opacity-50">
    Guardar
</button>
```

## Card KPI (admin)

```html
<x-ui.kpi-card label="Producción hoy" value="12.450" hint="vs ayer +3%" />
```

Manual:

```html
<div class="rounded-xl border border-avicore-border bg-avicore-card p-5 shadow-sm
            md:transition-shadow md:duration-200 md:hover:shadow-md">
    <p class="text-xs font-medium uppercase tracking-wide text-avicore-muted">Producción hoy</p>
    <p class="mt-1 text-2xl font-semibold text-avicore-text">12.450</p>
    <p class="mt-1 text-sm text-avicore-muted">vs ayer +3%</p>
</div>
```

## Ítem lista operario (táctil)

```html
<a href="{{ route('operario.carga.huevos') }}" wire:navigate
   class="flex min-h-[3.25rem] items-center gap-3 rounded-xl border border-avicore-border bg-avicore-card px-4 py-3
          transition-colors duration-200 active:scale-[0.98] active:bg-avicore-soft">
    <x-ui.icon name="clipboard-list" class="size-5 text-avicore-primary" />
    <span class="font-medium text-avicore-text">Carga de huevos</span>
</a>
```

## Nav item activo (bottom bar — usar componente)

Preferir `<x-operario.bottom-nav />`. Celda activa:

```html
class="avicore-operario-tab-bar__item avicore-operario-tab-bar__item--active"
```

## Fila tabla admin (hover solo desktop)

```html
<tr class="border-b border-avicore-border transition-colors md:hover:bg-avicore-soft/60">
    <td class="px-4 py-3 text-sm text-avicore-text">Galpón 1</td>
</tr>
```

## Nav link sidebar (admin)

```html
<x-ui.nav-link href="{{ route('admin.home') }}" icon="home" :active="request()->routeIs('admin.home')">
    Inicio
</x-ui.nav-link>
```

## Drawer trigger (admin móvil, Alpine)

```html
<button type="button" @click="sidebarOpen = true"
    class="flex min-h-11 min-w-11 items-center justify-center rounded-lg text-avicore-text
           md:hidden focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-avicore-primary"
    aria-label="Abrir menú">
    <x-ui.icon name="menu" class="size-6" />
</button>
```

## Empty state

```html
<x-ui.empty-state
    icon="clipboard-list"
    title="Sin cargas hoy"
    description="Registrá la primera carga desde Cargar."
/>
```

## Input con error

```html
<x-ui.input name="cantidad" label="Cantidad" type="number" :error="$errors->first('cantidad')" />
```
