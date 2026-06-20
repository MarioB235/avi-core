# AviCore

Plataforma de **gestión operativa** para avícolas de gallinas ponedoras (MVP).

Permite registrar y consultar producción diaria por galpón: huevos, mortalidad, alimento, lotes, movimientos, reportes y dashboard, con trazabilidad y actualización en tiempo real cuando corresponde.

## Estado del repositorio

| Fase | Contenido |
|------|-----------|
| **Hecho** | Bloque 1: Laravel 13, Livewire 4, Tailwind 4, layouts, UI base, PostgreSQL con migraciones iniciales |
| **Siguiente** | Login + cambio obligatorio de contraseña — [`docs/12-plan-de-desarrollo.md`](docs/12-plan-de-desarrollo.md) |

## Stack

Laravel 13 · PostgreSQL · Livewire 4 · Tailwind CSS 4 · Alpine.js (Livewire) · PWA · Reverb · Echo *(últimos tres en fases posteriores)*

## Arranque local

Guía completa: [`docs/reference/arranque-local.md`](docs/reference/arranque-local.md)

```bash
composer install
cp .env.example .env   # si no tenés .env
php artisan key:generate
npm install && npm run build
```

**PostgreSQL:** crear base `avicore` en pgAdmin. En `.env`, completar `DB_PASSWORD` del usuario `postgres` (pgAdmin puede guardar la clave; Laravel necesita la misma en `.env`).

```bash
php artisan migrate
php artisan serve
npm run dev   # otra terminal, opcional
```

URL: `http://localhost:8000` · previews: `/dev/admin-layout`, `/dev/operario-layout`

## Empezar aquí

| Qué necesitás | Archivo |
|---------------|---------|
| Contexto del proyecto | [`docs/00-contexto.md`](docs/00-contexto.md) |
| Índice de documentación | [`docs/README.md`](docs/README.md) |
| Entorno local (PG, migrate) | [`docs/reference/arranque-local.md`](docs/reference/arranque-local.md) |
| Agente / Cursor | [`docs/cursor/01-indice-agente.md`](docs/cursor/01-indice-agente.md) |
| Comando del arquitecto | `/avicore-architect-direct` → [`.cursor/commands/avicore-architect-direct.md`](.cursor/commands/avicore-architect-direct.md) |
| Instrucciones para el agente | [`AGENTS.md`](AGENTS.md) |

## Flujo operativo (MVP)

```text
Seleccionar galpón → cargar dato operativo → guardar → dashboard / reportes
```

## Estructura principal

```text
app/            Actions, Services, Livewire, Policies, Events
resources/      Layouts, componentes UI, assets (Vite + Tailwind)
docs/           Documentación de producto (01–12) y referencias
.cursor/        Reglas, skills y comando del arquitecto AviCore
```

## Desarrollo con Cursor

1. Abrí este workspace en Cursor.
2. Usá `/avicore-architect-direct` (primera línea del chat), pegá la plantilla del mensaje que corresponda y completá el bloque final — ver [`docs/cursor/02-avicore-mensajes-reutilizables.html`](docs/cursor/02-avicore-mensajes-reutilizables.html).

## Licencia

Por definir.

## Autor

[MarioB235](https://github.com/MarioB235)
