# AviCore

Plataforma de **gestión operativa** para avícolas de gallinas ponedoras (MVP).

Permite registrar y consultar producción diaria por galpón: huevos, mortalidad, alimento, lotes, movimientos, reportes y dashboard, con trazabilidad y actualización en tiempo real cuando corresponde.

## Estado del repositorio

| Fase | Contenido |
|------|-----------|
| **Actual** | Documentación de producto, referencias (BD, arquitectura, estándares) y configuración del agente en Cursor |
| **Siguiente** | Código Laravel según [`docs/12-plan-de-desarrollo.md`](docs/12-plan-de-desarrollo.md) |

## Stack previsto

Laravel · PostgreSQL · Livewire · Tailwind CSS · Alpine.js · PWA · Laravel Reverb · Echo

## Empezar aquí

| Qué necesitás | Archivo |
|---------------|---------|
| Contexto del proyecto | [`docs/00-contexto.md`](docs/00-contexto.md) |
| Índice de documentación | [`docs/README.md`](docs/README.md) |
| Agente / Cursor | [`docs/cursor/01-indice-agente.md`](docs/cursor/01-indice-agente.md) |
| Comando del arquitecto | `/avicore-architect-direct` → [`.cursor/commands/avicore-architect-direct.md`](.cursor/commands/avicore-architect-direct.md) |
| Instrucciones para el agente | [`AGENTS.md`](AGENTS.md) |

## Flujo operativo (MVP)

```text
Seleccionar galpón → cargar dato operativo → guardar → dashboard / reportes
```

## Estructura principal

```text
docs/           Documentación de producto (01–12) y referencias
.cursor/        Reglas, skills y comando del arquitecto AviCore
AGENTS.md       Entrada rápida para el agente
```

## Desarrollo con Cursor

1. Abrí este workspace en Cursor.
2. Usá `/avicore-architect-direct` y las plantillas en [`docs/cursor/02-avicore-mensajes-reutilizables.html`](docs/cursor/02-avicore-mensajes-reutilizables.html).
3. Cuando exista código Laravel: `composer install`, `npm install`, `php artisan migrate --seed` (ver `docs/00-contexto.md`).

## Licencia

Por definir.

## Autor

[MarioB235](https://github.com/MarioB235)
