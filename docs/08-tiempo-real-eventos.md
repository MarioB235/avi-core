# 08 — Tiempo real y eventos

> **Estado:** *Planificado — Bloque 6* ([`12-plan-de-desarrollo.md`](12-plan-de-desarrollo.md) §10 y §13). Reverb y Echo aún no están instalados. Al implementar, expandir este doc con eventos, payloads y canales concretos en el mismo PR.

## Reglas que ya aplican

1. Todo evento en tiempo real debe respetar `empresa_id` — un usuario no recibe datos de otra empresa.
2. Usar **canales privados**; validar usuario autenticado, pertenencia a empresa y permiso del canal.
3. No usar WebSockets en CRUD simple sin valor (ver skill `avicore-tiempo-real`).

## Stack previsto

Laravel Reverb · Laravel Echo · Events de Laravel · canales privados por empresa.

## Próximo paso al implementar

1. Instalar y configurar Reverb + Echo (`07-arquitectura-tecnica.md`).
2. Primeros eventos sugeridos en el plan: carga operativa creada, registro anulado, alerta generada.
3. Documentar aquí cada evento (nombre, cuándo se dispara, payload mínimo, qué UI actualiza).
