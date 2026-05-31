# 00 — Contexto del proyecto AviCore

**Archivo de contexto principal.** Contrato extendido (prohibiciones, flujo por módulo, comandos): secciones finales de este archivo.  
Índice agente: `docs/cursor/01-indice-agente.md` · Skills: `docs/cursor/03-skills-avicore.md` · Plantillas (HTML): `docs/cursor/02-avicore-mensajes-reutilizables.html`

---

## Qué es AviCore

Plataforma de **gestión operativa** para avícolas de gallinas ponedoras (MVP).

```text
Seleccionar galpón → cargar dato operativo → guardar → dashboard/reportes (tiempo real si aplica)
```

Stack: **Laravel, PostgreSQL, Livewire, Tailwind, Alpine.js, PWA, Laravel Reverb, Echo**.

---

## Referencias estructurales

| Necesidad | Archivo |
|-----------|---------|
| Esquema BD | [`reference/estructura-base-datos.md`](reference/estructura-base-datos.md) |
| Árbol Laravel | [`reference/estructura-proyecto.md`](reference/estructura-proyecto.md) |
| Estándares código | [`reference/estandares-codigo.md`](reference/estandares-codigo.md) |
| Gobernanza docs | [`README.md`](README.md) · [`CHANGELOG.md`](CHANGELOG.md) |

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

## Comandos locales (cuando exista código)

```bash
composer install && npm install && php artisan migrate --seed
npm run dev && php artisan serve && php artisan reverb:start
php artisan test && npm run build
```

---

## Prohibiciones

No implementar sin leer docs del módulo · lógica crítica en Blade · saltar multiempresa · delete físico en operativos · Reverb en CRUD innecesario · módulos fuera del MVP sin justificación · cambiar reglas sin actualizar doc · UI fuera de guía · ignorar vista operario.

---

## Tooling Cursor

`/avicore-architect-direct` — el usuario escribe en lenguaje natural; el arquitecto elige skills internos.  
Plantillas: `docs/cursor/02-avicore-mensajes-reutilizables.html`

**MCP:** Context7 (`user-context7`) para dudas de API/stack tras docs del proyecto; GitHub (`user-github`) solo mensaje 5 / `avicore-git-pr`.

**Chat:** respuestas en prosa natural, lenguaje llano (`docs/cursor/06-modo-respuesta-clara.md`).

---

## Cierre de tarea (agente)

Último párrafo del chat: qué quedó listo, documentación/tooling si cambió, siguiente paso concreto. Sin bloque etiquetado `Resumen:`/`Archivos:` salvo que el usuario pida informe estructurado.
