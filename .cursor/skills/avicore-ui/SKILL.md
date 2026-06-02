---
name: avicore-ui
description: Diseña o modifica UI AviCore — pantallas web administrativas (login, dashboard, CRUDs) o vista móvil del operario (carga en campo). Usar con modo web u operario según la pantalla.
disable-model-invocation: true
---

# AviCore — UI

El usuario indica **Modo: web** o **Modo: operario**.

## Documentación común

`docs/02-pantallas-y-flujos.md` · `docs/03-guia-visual-ui.md` · `docs/06-roles-y-permisos.md` · `docs/reference/sistema-diseno.md`

Cambios transversales de tokens/layouts: skill interno `avicore-design-system`.

## Modo web

- Identidad verde/agro; Tailwind; sin inline; componentes reutilizables; responsive.
- Auth (login, cambio de contraseña): layout público — split escritorio, bottom sheet móvil (`docs/02` § Login); `x-ui.logo` / `x-ui.input` / `x-ui.icon`; recuperación MVP con `x-auth.support-contact-dialog` + `config/avicore.php` / `SupportContactService`.
- Si persiste datos: validaciones, permisos, `empresa_id`, auditoría si crítico.
- Datos reales (no hardcode salvo demo).

## Modo operario

- Vista móvil simplificada; galpón visible y cambiable.
- Sin fecha/hora manual (`created_at` automático).
- Carga huevos, muertes, alimento o combinada; al menos un dato para guardar.
- Botones grandes, confirmación al guardar, últimas cargas del día.
- Reglas: `docs/05-reglas-de-negocio.md`.
- Verificar móvil, permisos, Livewire/Reverb intactos.

## Entrada

**Modo**, **Pantalla/flujo**, **Objetivo**.
