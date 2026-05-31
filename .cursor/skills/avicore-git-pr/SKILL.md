---
name: avicore-git-pr
description: Commit, push y PR de AviCore con verificaciones, Conventional Commits y creación de PR vía MCP GitHub (o gh). Solo con autorización explícita del usuario (mensaje 5).
disable-model-invocation: true
---

# AviCore — Git y PR

**Requiere autorización explícita** (mensaje 5 del usuario).

## Flujo completo

### A — Revisión local

1. `git status` · `git branch` · `git diff` (y `git diff --staged` si hay staged).
2. Confirmar que **no** se incluyen `.env`, credenciales ni archivos ignorados por error.
3. Verificaciones si existe código Laravel:
   - `php artisan test`
   - `npm run build`

### B — Commit y push (terminal)

4. Si hace falta, crear/usar rama: `[tipo]/[nombre-descriptivo]` (no commitear en `main` sin acuerdo).
5. `git add` solo archivos relevantes.
6. Commit Conventional Commits: `feat(scope): descripción` · tipos: feat, fix, refactor, chore, docs, test, style.
7. `git push -u origin [rama]`

### C — Pull Request (preferir MCP GitHub)

8. Crear PR con **MCP `user-github`** → herramienta `create_pull_request`:
   - `owner`, `repo` (del remoto o preguntar si falta)
   - `head`: rama con los cambios
   - `base`: `main` o rama por defecto del equipo
   - `title`: mismo estilo que el commit, claro y breve
   - `body`: plantilla abajo (markdown completo)

9. Si MCP GitHub no está disponible o falla: `gh pr create` con el mismo título y cuerpo.

10. Devolver al usuario: **URL de la PR**, rama, commit hash, estado de checks si se conocen.

## Plantilla del cuerpo de la PR

```markdown
## Resumen
[Qué se hizo y por qué — 2–3 oraciones]

## Tipo de cambio
- [ ] feat · [ ] fix · [ ] refactor · [ ] docs · [ ] chore

## Objetivos cumplidos
- [ ] ...

## Cambios técnicos
### Archivos principales
- `ruta` — [motivo]

## Criterios AviCore
- [ ] Multiempresa (`empresa_id`)
- [ ] Permisos (Policies/Gates)
- [ ] Validaciones
- [ ] Auditoría en acciones críticas (si aplica)
- [ ] UI responsiva / móvil operario (si aplica)
- [ ] Tiempo real solo donde corresponde (si aplica)
- [ ] Documentación verificada (mensaje 4 o N/A)

## Verificación
- [ ] `php artisan test`
- [ ] `npm run build`
- [ ] Prueba manual PC
- [ ] Prueba manual móvil (si aplica)
- [ ] Tiempo real probado (si aplica)

## Documentación
Verifiqué documentación y actualicé [lista o «no aplica»] según el cambio.

## Notas para revisores
[opcional]
```

## MCP recomendados

| Acción | MCP |
|--------|-----|
| Crear PR | `user-github` → `create_pull_request` |
| Estado PR / checks | `user-github` → `get_pull_request`, `get_pull_request_status` |
| Git local | terminal (`git`, `gh` si existe) |

No usar push --force a `main`/`master` salvo pedido explícito del usuario.

## Salida al usuario

Rama · último commit · URL PR · resumen de verificaciones · nota si algo quedó pendiente.
