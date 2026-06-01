# Changelog — Contrato documental AviCore

Solo cambios que alteran **reglas, esquema, pantallas, permisos o arquitectura acordada**.  
Formato: `YYYY-MM-DD — [área] descripción breve — archivos tocados`

---

## 2026-06-01

- **[docs]** Pasada cierre (mensaje 4): wordmark/logo y auth en `03`, presentación cambio de contraseña en `02`, árbol `tests/Feature/Ui` y criterio tests UI en `estandares-codigo.md`. — `docs/02`, `docs/03`, `docs/reference/`
- **[ui]** Login móvil tipo bottom sheet: logo apilado sobre `background-mobile.jpg`, tarjeta anclada abajo; `x-ui.logo` con `stacked` y tamaño `auth-mobile`; padding de inputs con icono sin conflicto Tailwind; autofill Chrome unificado. — `resources/views/`, `resources/css/app.css`, `docs/02-pantallas-y-flujos.md`, `docs/reference/sistema-diseno.md`
- **[ui]** Nuevo fondo móvil de marca (`background-mobile`) — fuente PNG en `resources/images/brand/`, JPEG optimizado en `public/` vía `optimize-brand-assets.py`. — `resources/images/brand/`, `public/images/brand/`
- **[cursor]** Auditoría ampliada: dimensiones Tests y Arquitectura; mensaje 3 maximiza cumplimiento y exige test/build en verde antes de PR. — `02-avicore-mensajes-reutilizables.html`, skill auditoria, `docs/reference/estandares-codigo.md`
- **[cursor]** Mensajes 2–4: flujo de cierre (auditoría → corrección → docs/skills → PR); alcance, tests y tabla de alineación. — `docs/cursor/02-avicore-mensajes-reutilizables.html`, `.cursor/commands/avicore-architect-direct.md`, skills auditoria/cierre-tarea, `03-skills-avicore.md`
- **[cursor]** Mensaje 1: una sola plantilla (eliminada versión corta duplicada). — `docs/cursor/02-avicore-mensajes-reutilizables.html`
- **[cursor]** Mensajes HTML: versión corta del 1 (con slash), referencias acotadas en 2–5, ejemplo @ruta en 4, paleta alineada a tokens. — `docs/cursor/02-avicore-mensajes-reutilizables.html`
- **[docs]** Alineación post-sesión UI: login (presentación MVP), árbol `resources/` y `scripts/`, componente `x-ui.icon`, partials de layout y logo `#1F5E3B` en guía visual. — `docs/02`, `docs/03`, `docs/reference/estructura-proyecto.md`, `docs/reference/sistema-diseno.md`, skill design-system
- **[ui]** Logo SVG actualizado (verde marca `#1F5E3B` / paleta AviCore) en `public/images/brand/logo-avicore.svg`. — `public/images/brand/logo-avicore.svg`
- **[ui]** Logo de marca unificado: `public/images/brand/logo-avicore.svg` (fondo transparente) para `x-ui.logo`; se deja de usar PNG derivado. — `public/images/brand/`, `resources/views/components/ui/logo.blade.php`, `docs/reference/sistema-diseno.md`
- **[fix/ui]** Correcciones post-auditoría: un solo toggle de contraseña (CSS Chromium + Alpine), checkbox con `focus-visible`, login sin enlace falso de recuperación, fondos JPEG comprimidos, `.gitignore` para `fonts-manifest.dev.json`, tests de rate limit y redirect desde login. — `resources/`, `tests/`, `.gitignore`, `scripts/optimize-brand-assets.py`, `docs/reference/sistema-diseno.md`
- **[chore]** Metadatos `composer.json` (`avicore/app`) y script de optimización de assets de marca. — `composer.json`, `scripts/`
- **[ui]** Entrada `/` redirige a login (eliminada pantalla intermedia de bienvenida). — `routes/web.php`, `docs/02-pantallas-y-flujos.md`, `docs/reference/arranque-local.md`
- **[ui]** Logo de marca en SVG: `public/images/brand/logo-avicore.svg` para `x-ui.logo`. — `public/images/brand/`, `docs/reference/sistema-diseno.md`
- **[ui]** Pulido visual: nav móvil con drawer Alpine en layout admin, iconos SVG, componente `kpi-card`, utilidades tabla/sección en `app.css`, sidebar con sesión y logout, páginas demo refinadas. — `resources/views/`, `resources/css/app.css`, `docs/reference/sistema-diseno.md`
- **[ui]** Login alineado a maqueta: layout split (marca + tarjeta), inputs con iconos, toggle contraseña, copy de bienvenida. — `resources/views/`, `resources/css/app.css`
- **[chore]** `composer dev` compatible con Windows: omite Pail si no hay ext-pcntl; script `scripts/dev.php`. — `composer.json`, `docs/reference/arranque-local.md`
- **[chore]** Vite en Windows: `server.host: 127.0.0.1` para que `public/hot` no use `[::1]` y el CSS cargue al abrir `:8000`. — `vite.config.js`, `docs/reference/arranque-local.md`

## 2026-05-31

- **[chore]** `.gitignore`: carpeta caché `/.vite/` (Vite en desarrollo). — `.gitignore`
- **[cursor]** Mensajes HTML: acordeón «Comandos locales» (setup, migrate, test, build, composer dev, Pint, caché). — `docs/cursor/02-avicore-mensajes-reutilizables.html`
- **[ui]** Sistema de diseño: tokens ampliados en `app.css`, componentes UI con estados WCAG, layouts admin/público/operario refinados; guía `reference/sistema-diseno.md` (base awesome-design-skills **clean** + enterprise, paleta verde/agro). — `resources/`, `docs/03`, `docs/reference/`, `.cursor/skills/avicore-design-system/`
- **[cursor]** Eliminado índice duplicado `02-avicore-mensajes-reutilizables.md`; tabla docs por mensaje en `03-skills-avicore.md`; HTML como única plantilla usuario. — `docs/cursor/`, skills auditoria, cierre-tarea, git-pr
- **[referencia]** Tests PHPUnit con PostgreSQL (`avicore_test`) en lugar de SQLite; plantilla `.env.testing.example` y pasos en arranque local. — `phpunit.xml`, `.env.testing.example`, `docs/reference/arranque-local.md`
- **[docs]** Alineación post Bloque 2 auth: usuarios demo en `10`, flujos login/cambio en `02`, reglas throttling/contraseña en `05`, rutas por rol en `06`, middleware y `EmpresaContextService` en `07`/`estructura-proyecto`, criterio documento admin e índice parcial en `04`/`estructura-base-datos`. — `docs/02`, `04`, `05`, `06`, `07`, `10`, `reference/`
- **[cursor]** Mensajes 1–5: bloque «Referencias agente» (skills, docs, CHANGELOG, evolución tooling); índice `02-avicore-mensajes-reutilizables.md`. — `docs/cursor/`, skills auditoria, cierre-tarea, git-pr
- **[cursor]** Mensajes 2–3 auditoría: tabla clasificadora con brechas en fila; mensaje 3 corrige según tabla (sin lista de 5 mejoras). — `02-avicore-mensajes-reutilizables.html`, skill `avicore-auditoria`
- **[cursor]** Mensajes reutilizables: sin referencias cruzadas «pegar en mensaje 5» en textos copiables; flujo en hint del HTML. — `02-avicore-mensajes-reutilizables.html`
- **[cursor]** Mensajes 3–5: cierre con resumen breve para PR; sin «pegá @rutas abajo»; mensaje 5 recibe resúmenes pegados. — `02-avicore-mensajes-reutilizables.html`, skills cierre-tarea, auditoria, git-pr
- **[cursor]** Mensaje 5 reutilizable: PR desde chat actual + bloque opcional resumen de otros chats. — `02-avicore-mensajes-reutilizables.html`, skill `avicore-git-pr`
- **[cursor]** Mensaje 4 reutilizable: alinear docs según @rutas adjuntas de la sesión actual. — `02-avicore-mensajes-reutilizables.html`, skill `avicore-cierre-tarea`
- **[cursor]** Mensajes reutilizables HTML: botón Copiar lee `textContent` del `<pre>` (no atributo vacío); fallback portapapeles. — `docs/cursor/02-avicore-mensajes-reutilizables.html`
- **[cursor]** Mensajes reutilizables HTML: sin placeholders `-` en listas de archivos; mensaje 3 sin bloque de hallazgos. — `docs/cursor/02-avicore-mensajes-reutilizables.html`
- **[cursor]** Acordeón usuarios demo (login local) en mensajes reutilizables HTML; operario `Actual2026!`. — `docs/cursor/02-avicore-mensajes-reutilizables.html`, `docs/reference/arranque-local.md`
- **[referencia]** Arranque local: aclarar puerto 8000 (Laravel) vs 5173 (Vite, no abrir en el navegador). — `docs/reference/arranque-local.md`
- **[pantalla]** Login y cambio obligatorio de contraseña (Livewire, layout público, redirección por rol). — `app/Livewire/Auth/`, `app/Actions/Auth/`, `routes/web.php`
- **[bd]** Tabla `empresas` y campos AviCore en `users` (documento, rol, activo, must_change_password). — `database/migrations/2026_05_31_*`, `app/Models/`
- **[auth]** Endurecimiento post-auditoría: throttling login, contraseña segura, índice único documento admin, contexto empresa, rutas `/dev` solo local, seeder Administrativo/Encargado, tests ampliados. — `app/Actions/Auth/`, `app/Services/EmpresaContextService.php`, `database/migrations/2026_05_31_100002_*`, `tests/Feature/Auth/`
- **[código]** Bloque 1 — base Laravel: Livewire 4, Tailwind 4 (tema AviCore), layouts público/admin/operario, componentes UI base, PostgreSQL en `.env.example`. — `app/`, `resources/`, `routes/web.php`, `.env.example`, `docs/reference/estructura-proyecto.md`
- **[arquitectura]** Bloque 1 cerrado en entorno local: PostgreSQL `avicore`, migraciones Laravel OK; versiones stack en `07`; estado de bloques en `12`. — `docs/07-arquitectura-tecnica.md`, `docs/12-plan-de-desarrollo.md`, `docs/00-contexto.md`, `README.md`
- **[referencia]** Nueva fuente maestra entorno local (PostgreSQL, pgAdmin, `.env`, migrate, serve). — `docs/reference/arranque-local.md`, `docs/README.md`

## 2026-05-30

- **[estructura]** Reorganización documental: contexto (`00-contexto.md`), referencias BD/proyecto, README, CHANGELOG. — `docs/`, `AGENTS.md`, `.cursor/rules/`
- **[cursor]** Skills 15→11 (fusionados ui, auditoria, cierre-tarea, architect). `00-contexto` unifica agente. `02-mensajes` índice conciso → skills. `01-agente` reducido a índice. — `.cursor/skills/`, `docs/cursor/`
- **[cursor]** Revisión de estructura: README sin duplicado, flujo en `00-configuracion-cursor`, alineación nombres skills. — `docs/README.md`, `docs/cursor/`
- **[cursor]** `01-avicore-agente-permanente.md` → `01-indice-agente.md`; comando y skill `avicore-architect` con flujo obligatorio en 5 pasos. — `docs/cursor/`, `.cursor/commands/`, `.cursor/skills/avicore-architect/`
- **[cursor]** Plantillas en `02-avicore-mensajes-reutilizables.html` (acordeones, copiar, expandir/colapsar). `.md` como índice. — `docs/cursor/`
- **[cursor]** Mensajes en lenguaje natural; arquitecto enruta skills internos (sin `@skill` del usuario). Prep Git en tarea nueva. — `02-*.html`, comando architect, `03-skills`
- **[cursor]** Mensajes reducidos a 5: tarea general, auditoría, correcciones, documentación, commit/PR. — `02-avicore-mensajes-reutilizables.*`
- **[cursor]** Mensajes 2–4: base de reglas/docs explícita + sección para adjuntar archivos (@rutas). — `02-*.html`, skills auditoria y cierre-tarea
- **[referencia]** `estandares-codigo.md` + regla `.mdc`. Auditoría mensaje 2 solo lectura con tabla %. PR mensaje 5 con MCP GitHub y plantilla. — `docs/reference/`, skills git-pr y auditoria
- **[cursor]** Modo COMPRESIÓN CAVERMAN opcional (`avicore-modo-caverman.mdc`, `04-modo-respuesta-caverman.md`, `.cursorrules` índice). — `.cursor/rules/`, architect
- **[cursor]** Gobernanza evolución skills/docs (`05-evolucion-skills-y-docs.md`, skill `avicore-evolucion-tooling`). Architect pasos 5–7: docs producto + tooling + cierre. — `.cursor/`, `docs/cursor/`
- **[cursor]** Context7 en paso 2 del architect-direct; fila actualizar comando en paso 6; MCP ordenado en `00-configuracion-cursor`. — architect, agente-permanente, `00-contexto`
- **[cursor]** Modo respuesta clara en chat (`avicore-modo-respuesta-clara.mdc`, `06-modo-respuesta-clara.md`); architect-direct obliga lenguaje llano; Caverman opcional de nuevo (`alwaysApply: false`). — `.cursor/rules/`, architect
- **[cursor]** Respuesta natural: prosa en párrafos, sin tablas/listas de resumen ni bloque `Resumen:`/`Archivos:`; ejemplo antes/después en `06`; cierre integrado en architect, agente-permanente, AGENTS. — `.cursor/`, `docs/cursor/`, `docs/00-contexto.md`
- **[cursor]** Mensajes reutilizables HTML/MD reescritos en lenguaje natural; títulos y placeholders alineados con modo respuesta clara. — `02-avicore-mensajes-reutilizables.*`
- **[cursor]** Optimización flujo agente: jerarquía de fuentes (contrato en 00-contexto + comando; agente-permanente/AGENTS punteros); mermaid sin @skill; inventario 7 reglas y docs 00–06; enrutamiento skills paso 3; mensaje 4 vs paso 5; mapa doc maestra solo en docs/README; architect skill reducido a puntero. — `.cursor/`, `docs/cursor/`, `docs/README.md`
- **[cursor]** Skill `avicore-git-pr`: verificación `gh auth` vs remoto, orden MCP → gh → token Git credential; notas de seguridad. — `.cursor/skills/avicore-git-pr/`, `00-configuracion-cursor.md`

---

## Plantilla (copiar al registrar)

```text
## YYYY-MM-DD

- **[negocio|bd|pantalla|permiso|arquitectura|tiempo-real|reporte|demo]** Qué cambió y por qué. — `archivo1`, `archivo2`
```
