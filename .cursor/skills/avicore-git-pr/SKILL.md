---
name: avicore-git-pr
description: Commit, push y PR de AviCore con verificaciones, Conventional Commits y PR vía MCP GitHub, gh o token Git alineado al remoto. Solo con autorización explícita (mensaje 5).
disable-model-invocation: true
---

# AviCore — Git y PR

**Requiere autorización explícita** (mensaje 5 del usuario).

Mensaje usuario: `docs/cursor/02-avicore-mensajes-reutilizables.html` (mensaje 5) · catálogo: `docs/cursor/03-skills-avicore.md` · auth: `docs/cursor/00-configuracion-cursor.md`.

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

### C — Pull Request

**Redacción del cuerpo:** basar resumen y objetivos en el **chat actual** (mensajes 2–4, diff y conversación). Los `@rutas` del cierre van solo en el mensaje 2; no pedir re-adjuntar en el 5. Si el usuario completó «Resumen adicional» en el mensaje 5, integrarlo sin contradecir el diff ni este chat.

**Antes de crear la PR:** `gh auth status` y comparar la cuenta activa con el `owner` del remoto (`git remote get-url origin`). El push puede funcionar con credenciales de Git distintas a las de `gh` — si no coinciden, `gh pr create` fallará aunque el push haya salido bien.

**Orden de intentos:**

1. **MCP `user-github`** → `create_pull_request` (`owner`, `repo`, `head`, `base`, `title`, `body` con plantilla abajo).
2. **`gh pr create --repo owner/repo`** con `--body-file` (plantilla completa).
3. **Si falla por permisos** («must be a collaborator», PAT sin scope, cuenta distinta): usar token del dueño del repo desde el credential helper de Git **solo en la sesión del comando** (no imprimir ni commitear el token):

   ```bash
   # Bash — obtener token sin mostrarlo en el log
   GH_TOKEN=$(printf 'protocol=https\nhost=github.com\n' | git credential fill | awk -F= '/^password=/{print $2}')
   export GH_TOKEN
   gh pr create --repo OWNER/REPO --base main --head RAMA --title "..." --body-file pr-body.md
   unset GH_TOKEN
   ```

   En PowerShell: mismo flujo con `git credential fill` y `$env:GH_TOKEN`; no echo del password.

4. Si todo falla: devolver al usuario la URL de compare de GitHub  
   `https://github.com/OWNER/REPO/compare/main...RAMA?expand=1`  
   y el cuerpo de la PR listo para pegar.

**Seguridad:** nunca incluir tokens en commits, PRs ni chat. Si un token se expuso en logs, recomendar rotarlo en GitHub → Settings → Developer settings. Conviene alinear `gh auth login` con la cuenta del remoto para evitar el paso 3.

**Salida al usuario:** URL de la PR · rama final · hash corto del commit · verificaciones · estado pendiente (merge a cargo del usuario) · nota si hubo workaround de auth.

Plantilla en mensaje 5 del HTML = versión **condensada**; esta sección = versión **completa** para el cuerpo de la PR.

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
