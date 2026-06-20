# 00 — Contexto del proyecto AviCore

**Archivo de contexto principal.** Contrato extendido (prohibiciones, flujo por módulo, comandos): secciones finales de este archivo.  
Índice agente: `docs/cursor/01-indice-agente.md` · Skills: `docs/cursor/03-skills-avicore.md` · Plantillas (HTML): `docs/cursor/02-avicore-mensajes-reutilizables.html`

---

## Qué es AviCore

Plataforma de **gestión operativa** para avícolas de gallinas ponedoras (MVP).

```text
Seleccionar galpón → cargar dato operativo → guardar → dashboard/reportes (tiempo real si aplica)
```

Stack: **Laravel 13, PostgreSQL, Livewire 4, Tailwind 4, Alpine.js (Livewire), PWA, Reverb, Echo** *(últimos tres en fases posteriores)*.

**Estado código:** Bloque 1 completado. Bloque 2 (login + cambio obligatorio) en curso. Slice operario mínimo (estructura avícola seed + carga huevos) en rama `feature/operario-carga-minima` — [`12-plan-de-desarrollo.md`](12-plan-de-desarrollo.md).

---

## Referencias estructurales

| Necesidad | Archivo |
|-----------|---------|
| Esquema BD | [`reference/estructura-base-datos.md`](reference/estructura-base-datos.md) |
| Árbol Laravel | [`reference/estructura-proyecto.md`](reference/estructura-proyecto.md) |
| Arranque local | [`reference/arranque-local.md`](reference/arranque-local.md) |
| Estándares código | [`reference/estandares-codigo.md`](reference/estandares-codigo.md) |
| Gobernanza docs | [`README.md`](README.md) · [`CHANGELOG.md`](CHANGELOG.md) |

**Docs incrementales:** `reference/` y pantallas en `02` documentan solo lo implementado; roadmap y módulos sin código → [`12-plan-de-desarrollo.md`](12-plan-de-desarrollo.md).

---

## Mapa de lectura por tarea

| Tarea | Docs |
|-------|------|
| Cualquier implementación | `05`, `06`, (`reference/estructura-base-datos` si datos), `02` si UI, `07` |
| Módulo / CRUD | + `04`, `11` |
| Operario móvil | + `03` |
| Tiempo real | + `08` |
| Reportes | + `09` |
| Demo | + `10` |
| Alcance | `01`, `12` |

---

## Contrato de ejecución

Ejecutar de punta a punta sin “¿procedo?”. Preguntar solo si: dato crítico faltante, operación destructiva no autorizada, conflicto entre docs, tarea contra reglas del proyecto, o riesgo en seguridad/datos reales sin contexto.

Filosofía: `Entender → Contextualizar → Planificar → Implementar → Verificar → Documentar → Cerrar`

Clasificación: `feature` | `fix` | `refactor` | `docs` | `style` | `chore` | `hotfix`

---

## Principios no negociables

Multiempresa (`empresa_id`) · Policies/Gates · negocio en Services/Actions · anulación con motivo · auditoría crítica · tiempo real selectivo · UI verde/agro sin inline · un cambio conceptual → una fuente maestra + `CHANGELOG.md` · mantener skills/reglas al día (`docs/cursor/05-evolucion-skills-y-docs.md`)

Detalle de reglas: `05-reglas-de-negocio.md` (no repetir aquí).

---

## Flujo por módulo

```text
1. Docs del módulo  2. Pantalla mínima  3. Migración/modelo  4. Livewire
5. Action/Service  6. Permisos  7. Validaciones  8. Auditoría/eventos si aplica
9. Probar PC y móvil  10. Actualizar doc maestra si cambió contrato
```

---

## Verificación antes de cerrar

Pantalla OK · datos persisten · validaciones · permisos · sin fuga entre empresas · móvil OK · auditoría · dashboard/tiempo real si aplica · docs alineadas.

---

## Comandos locales

Ver [`reference/arranque-local.md`](reference/arranque-local.md). Resumen:

```bash
composer install && npm install && php artisan migrate
npm run dev    # terminal 1 de assets
php artisan serve
php artisan test && npm run build
```

Reverb (cuando aplique): `php artisan reverb:start`

---

## Prohibiciones

No implementar sin leer docs del módulo · lógica crítica en Blade · saltar multiempresa · delete físico en operativos · Reverb en CRUD innecesario · módulos fuera del MVP sin justificación · cambiar reglas sin actualizar doc · UI fuera de guía · ignorar vista operario.

---

## Tooling Cursor

Flujo slash (7 pasos): [`.cursor/commands/avicore-architect-direct.md`](../.cursor/commands/avicore-architect-direct.md) · plantillas: `docs/cursor/02-avicore-mensajes-reutilizables.html` · catálogo skills: `docs/cursor/03-skills-avicore.md`

**MCP:** Context7 (`user-context7`) para dudas de API/stack tras docs del proyecto; GitHub (`user-github`) solo mensaje 5 / `avicore-git-pr`.

**Chat:** `docs/cursor/06-modo-respuesta-clara.md` (regla `.mdc` always-apply).

---

## Cierre de tarea (agente)

Último párrafo del chat: qué quedó listo, documentación/tooling si cambió, siguiente paso concreto. Sin bloque etiquetado `Resumen:`/`Archivos:` salvo que el usuario pida informe estructurado.
