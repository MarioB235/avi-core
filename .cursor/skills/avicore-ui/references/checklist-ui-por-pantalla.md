# Checklist UI por pantalla

Antes de cerrar una pantalla (PR o mensaje 1), verificar:

## Jerarquía

- [ ] Título de página claro (H1 o equivalente en header)
- [ ] Una acción primaria evidente
- [ ] Texto secundario en `text-avicore-muted`

## Modo correcto

- [ ] **Operario:** sin `hover:` como feedback principal; touch ≥44px; bottom nav si aplica
- [ ] **Admin:** `hover:` solo con `md:`+; sidebar/drawer coherente

## Tokens y componentes

- [ ] Colores `avicore-*`; sin hex sueltos
- [ ] Reutiliza `x-ui.*` antes de HTML crudo
- [ ] Sin estilos inline

## Estados

- [ ] `hover`, `focus-visible`, `disabled`, `aria-*` en controles
- [ ] Errores de validación visibles en inputs
- [ ] Empty state si lista vacía

## Motion (Refined Agro)

- [ ] Duraciones ≤300ms
- [ ] `prefers-reduced-motion` si hay `scale`/`transform`
- [ ] Sin animación de entrada en cada ítem de lista
- [ ] `backdrop-blur` solo en chrome fijo

## Accesibilidad

- [ ] Contraste WCAG AA
- [ ] Labels en formularios; iconos decorativos con `aria-hidden`
- [ ] Foco visible en teclado

## Livewire

- [ ] `wire:navigate` en navegación interna donde corresponda
- [ ] Loading states en acciones lentas (no spinners decorativos)

## Documentación

- [ ] Si cambia flujo visible: actualizar `02-pantallas-y-flujos.md`
- [ ] Si cambia contrato visual: `tokens-componentes.md` o `guia-visual.md`
