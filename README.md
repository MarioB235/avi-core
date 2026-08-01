# AviCore

Plataforma de **gestión operativa** para avícolas de gallinas ponedoras (MVP).

Permite registrar y consultar producción diaria por galpón: huevos, mortalidad, alimento, lotes, movimientos, reportes y dashboard, con trazabilidad y actualización en tiempo real cuando corresponde.

## Estado del repositorio

| Fase | Contenido |
|------|-----------|
| **Hecho** | Bloque 1–2 + operario + admin usuarios: estructura avícola, carga huevos/muertes/vacunación, alta lote, historial, `/admin/usuarios` |
| **Siguiente** | Ver [`avicore-contexto/references/plan-desarrollo.md`](.cursor/skills/avicore-contexto/references/plan-desarrollo.md) |

## Stack

Laravel 13 · PostgreSQL · Livewire 4 · Tailwind CSS 4 · Alpine.js (Livewire) · PWA · Reverb · Echo *(últimos tres en fases posteriores)*

## Arranque local

Guía completa: [`.cursor/skills/avicore-contexto/references/arranque-local.md`](.cursor/skills/avicore-contexto/references/arranque-local.md)

```bash
composer setup       # install, .env, key, migrate, seed, pnpm install + build
# o paso a paso:
composer install
cp .env.example .env   # si no tenés .env
php artisan key:generate
pnpm install
php artisan migrate
php artisan db:seed
pnpm run build
composer dev           # atajo: serve + queue + vite
```

URL: `http://localhost:8000` · previews: `/dev/admin-layout`, `/dev/operario-layout`

## Empezar aquí

| Qué necesitás | Archivo |
|---------------|---------|
| Contexto del proyecto | [`docs/00-contexto.md`](docs/00-contexto.md) |
| Detalle de producto | [`.cursor/skills/README.md`](.cursor/skills/README.md) |
| Entorno local | [`avicore-contexto/references/arranque-local.md`](.cursor/skills/avicore-contexto/references/arranque-local.md) |
| Config Cursor | [`.cursor/README.md`](.cursor/README.md) |
| Comando del arquitecto | `/avicore-architect-direct` → [`.cursor/commands/avicore-architect-direct.md`](.cursor/commands/avicore-architect-direct.md) |
| Plantillas mensajes 1–5 | [`docs/02-avicore-mensajes-reutilizables.html`](docs/02-avicore-mensajes-reutilizables.html) |
| Instrucciones para el agente | [`AGENTS.md`](AGENTS.md) |

## Estructura principal

```text
app/            Actions, Services, Livewire, Policies, Events
resources/      Layouts, componentes UI, assets (Vite + Tailwind)
docs/           Contexto mínimo (00-contexto, CHANGELOG, plantillas HTML)
.cursor/        Skills, reglas, comando del arquitecto AviCore
```

## Desarrollo con Cursor

1. Abrí este workspace en Cursor.
2. Usá `/avicore-architect-direct` (primera línea del chat), pegá la plantilla del mensaje que corresponda y completá el bloque final — ver [`docs/02-avicore-mensajes-reutilizables.html`](docs/02-avicore-mensajes-reutilizables.html).

## Licencia

MIT (ver `composer.json`).

## Autor

[MarioB235](https://github.com/MarioB235)
