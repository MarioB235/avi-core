# 00 — Contexto del proyecto AviCore

**Punto de entrada humano y agente.** Contrato mínimo; el detalle vive en `.cursor/skills/*/references/`.

Plantillas usuario: [`docs/02-avicore-mensajes-reutilizables.html`](02-avicore-mensajes-reutilizables.html) (índice) · secciones en [`docs/plantillas/`](plantillas/) (desarrollo, ChatGPT pantallas)  
Catálogo skills: [`.cursor/skills/README.md`](../.cursor/skills/README.md)  
Flujo slash: [`.cursor/commands/avicore-architect-direct.md`](../.cursor/commands/avicore-architect-direct.md)

---

## Qué es AviCore

Plataforma de **gestión operativa** para avícolas de gallinas ponedoras (MVP).

```text
Seleccionar galpón → cargar dato operativo → guardar → dashboard/reportes (tiempo real si aplica)
```

Stack: **Laravel 13, PostgreSQL, Livewire 4, Tailwind 4, Alpine.js (Livewire), PWA, Reverb, Echo** *(últimos tres en fases posteriores)*.

---

## Mapa de lectura (skills)

| Tarea | Skill | Referencia principal |
|-------|-------|----------------------|
| Orientación / alcance | `avicore-contexto` | `references/producto.md`, `references/plan-desarrollo.md` |
| Cualquier implementación | `avicore-negocio` | `references/reglas.md`, `references/permisos.md` |
| Pantallas / UI | `avicore-ui` | `references/pantallas-flujos.md` |
| UI operario móvil | `avicore-ui` | `references/patrones-mobile-operario.md` |
| UI admin web | `avicore-ui` | `references/patrones-web-admin.md` |
| Tokens / componentes | `avicore-design-system` | `references/tokens-componentes.md` |
| Refined Agro (motion, elevación) | `avicore-design-system` | `references/refined-agro-principios.md`, `motion-y-feedback.md`, `elevacion-y-superficies.md` |
| Patrones TALL (índice upstream) | `avicore-design-system` | `references/INDICE-TALL-REFERENCIA.md` |
| BD / migraciones | `avicore-modelo-datos` | `references/esquema-bd.md` |
| Módulo completo | `avicore-nuevo-modulo` | `references/checklist.md` |
| Arranque local | `avicore-contexto` | `references/arranque-local.md` |
| Despliegue Laravel Cloud | `avicore-contexto` | `references/deploy-laravel-cloud.md` |
| Árbol Laravel | `avicore-contexto` | `references/arbol-proyecto.md` |

Ruta base: `.cursor/skills/<skill>/references/`

---

## Contrato de ejecución

Ejecutar de punta a punta sin «¿procedo?». Preguntar solo si: dato crítico faltante, operación destructiva no autorizada, conflicto entre docs, tarea contra reglas del proyecto.

Clasificación: `feature` | `fix` | `refactor` | `docs` | `style` | `chore` | `hotfix`

---

## Principios no negociables

Multiempresa (`empresa_id`) · Policies/Gates · negocio en Services/Actions · anulación con motivo · auditoría crítica · UI verde/agro sin inline · un cambio conceptual → `references/` del skill dueño + línea en [`CHANGELOG.md`](CHANGELOG.md)

Detalle: `avicore-negocio/references/reglas.md`

---

## Flujo por módulo

```text
1. Skill + references del módulo  2. Pantalla mínima  3. Migración/modelo  4. Livewire
5. Action/Service  6. Permisos  7. Validaciones  8. Auditoría/eventos si aplica
9. Probar PC y móvil  10. Actualizar reference del skill dueño si cambió contrato
```

---

## Comandos locales

Ver `avicore-contexto/references/arranque-local.md`. Atajo: `composer dev` → abrir **http://localhost:8000**

---

## Prohibiciones

No implementar sin leer references del skill · lógica crítica en Blade · saltar multiempresa · delete físico en operativos · UI fuera de guía · ignorar vista operario.

---

## Tooling Cursor

- **Slash:** `/avicore-architect-direct`
- **Config:** [`.cursor/README.md`](../.cursor/README.md)
- **Gobernanza:** `avicore-evolucion-tooling/references/GOBERNANZA.md`
- **MCP:** Context7 (`user-context7`) para API/stack; GitHub (`user-github`) solo mensaje 5

---

## Regla de una sola fuente maestra

| Si cambia… | Editar |
|------------|--------|
| Tabla, campo, FK | `avicore-modelo-datos/references/esquema-bd.md` |
| Regla de negocio | `avicore-negocio/references/reglas.md` |
| Pantalla o flujo | `avicore-ui/references/pantallas-flujos.md` |
| Patrón UI mobile / admin | `avicore-ui/references/patrones-mobile-operario.md` o `patrones-web-admin.md` |
| Permiso por rol | `avicore-negocio/references/permisos.md` |
| Token / componente / motion UI | `avicore-design-system/references/` (`tokens-componentes.md`, `refined-agro-principios.md`, …) |
| Carpeta/clase estándar | `avicore-contexto/references/arbol-proyecto.md` |
| Tooling agente | `.cursor/skills/README.md` + `GOBERNANZA.md` |

Siempre: línea en `CHANGELOG.md`.
