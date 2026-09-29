# Estado de capacidades AviCore

> **Fuente única de verdad** para «hecho / parcial / preview / pendiente».  
> **Corte:** 2026-09-26 · Evidencia: rutas en `routes/web.php`, Livewire en `app/Livewire/`, tests en `tests/`.  
> **Entrega v1:** [`portal/planes/PLAN-MAESTRO-ENTREGA-AVICORE.md`](../../../../portal/planes/PLAN-MAESTRO-ENTREGA-AVICORE.md) (tareas verificables).  
> **Roadmap histórico:** [`plan-desarrollo.md`](plan-desarrollo.md).

## Leyenda

| Estado | Significado |
|--------|-------------|
| **Implementado** | Flujo usable con datos reales; entra en el denominador v1 cuando el plan lo exige |
| **Parcial** | Código y pantallas existen; falta cerrar reglas, permisos, métricas o entrega según plan |
| **Preview** | UI o datos de muestra; **no cuenta como módulo entregado** (etapa 2 o demo explícita) |
| **Pendiente** | Sin módulo equivalente o solo `.gitkeep` / doc |

**Regla:** una capacidad no puede figurar como implementada y pendiente a la vez. Los previews se etiquetan y se excluyen de progreso v1 (plan RES-01).

## Primera entrega vs segunda etapa

| Alcance | Contenido |
|---------|-----------|
| **v1 (acordado 2026-09-26)** | Operación avícola completa: captura, supervisión, movimientos, historia, exportaciones internas |
| **Etapa 2** | Comercial, stock de huevos, pedidos, reparto, venta operativa (previews actuales no demuestran persistencia) |

---

## Matriz por capacidad

| Capacidad | Estado | Rutas / código | Notas de cierre v1 |
|-----------|--------|----------------|-------------------|
| Login y cambio de clave | Implementado | `/login`, `/password/change` | SEG-02–04 vigencia/rol/Livewire; SEG-09 multiempresa; SEG-10 reset/sesiones OK |
| Perfil propio | Implementado | `/perfil`, `/operario/perfil` | — |
| Multiempresa (sesión y scope) | Parcial | `EmpresaContextService`, `EmpresaScopeService`, policies | SEG-05 OK; EMP-01–08 pendiente |
| CRUD usuarios admin | Implementado | `/{rol}/usuarios` | SEG-08 OK (último admin, escalada, auto-desactivación) |
| Estructura granjas/galpones/lotes | Parcial | `/admin/estructura` | EST-01–10; ciclo de lote incompleto |
| Operario — capturas | Implementado | `/operario/cargar`, `/operario/carga/*` | CAP-01–14 |
| Operario — historial y anulación | Parcial | `/operario/historial` | AUD-01–03 ✓; corrección general pendiente |
| Admin — historial operativo | Implementado | `/{rol}/historial-operativo` | AUD-03; supervisión equipo |
| PWA instalable (online) | Implementado | `vite.config.js`, `pwa.md` | ACT-05–08; sin offline completo |
| Admin — Inicio (pulso/KPIs) | Parcial | `/admin` | Pulso real; completitud D03 en lista pendientes (RES-03 ✓); sin preview stock (RES-01 ✓) |
| Admin — Resumen | Parcial | `/admin/resumen` | Métricas RES-02 ✓; totales RES-04 ✓; mortalidad RES-05 ✓; umbrales referencia RES-06 ✓; RES-07–09 pendiente |
| Admin — Equipo | Parcial | `/admin/equipo` | Solo lectura; datos demo posibles |
| Admin — Comercial | **Etapa 2** | ruta existe; gate off v1 | RES-01 ✓ — sin KPIs/mapa demo en producto |
| Panel Reparto | **Preview** | `/reparto` | **Fuera v1**; rol sin enum completo (SEG-01) |
| Movimientos / cierre de lote | Implementado (MVP ledger) | `movimientos_aves`, Actions MOV-01–13, conciliación/ubicación/concurrencia, UI supervisor `/{rol}/movimientos` | MOV-01 ✓ … MOV-13 ✓; bloque RES pendiente |
| Corrección operativa (D07) | Parcial | `CorregirRegistroOperativoAction`, `correcciones_registro_operativo`, UI historial supervisor | AUD-04 ✓ |
| Auditoría crítica transversal | Implementado (MVP) | `auditorias`, `documentos_emitidos`, `PoliticaRetencionD07`, UI `/{rol}/auditoria`, inmutabilidad D07, tipo legado `combinado` | AUD-01 ✓ … AUD-09 ✓; bloque MOV pendiente |
| Reportes PDF / Excel | Pendiente | `Livewire/Reportes/.gitkeep` | REP-01–14 |
| Empresas admin plataforma | Parcial | `/avicore/empresas`, Actions Empresa, unidades por empresa en UI | EMP-01–04 OK; export REP pendiente |
| Tiempo real (Reverb / Echo) | Pendiente | `Events/.gitkeep` | ACT-02–04; `eventos.md` |
| Portal documental y plan de entrega | Implementado | `portal/`, `plan-entrega.html` | ORQ |

---

## Stack (una sola lectura)

| Componente | Estado |
|------------|--------|
| Laravel 13, Livewire 4, Tailwind 4, PostgreSQL | Implementado |
| PWA (manifest + SW assets) | Implementado MVP |
| Reverb + Echo | Pendiente |
| Offline / cola de sync | Fuera MVP |

Detalle técnico: [`arquitectura.md`](arquitectura.md).

---

## Roles (sin contradicción)

Matriz autoritativa: [`permisos.md`](../../avicore-negocio/references/permisos.md).

| Rol | v1 |
|-----|-----|
| Dueño | Resumen, reportes (cuando existan), equipo lectura, móvil; **no** estructura admin |
| Administrativo | Estructura, usuarios, operación autorizada; **no** mismo alcance que Dueño |
| Encargado | Supervisión parcial en panel + móvil según matriz |
| Operario | Solo `/operario` |
| Reparto | Fuera v1; denegación controlada (SEG-01) |
| Admin AviCore | Empresas + soporte auditado (EMP-06/07) + política datos personales (EMP-08) |

---

## Mantenimiento

Al cerrar una tarea del plan maestro que cambie el estado de una fila:

1. Actualizar esta matriz (misma sesión).
2. Ajustar solo el párrafo afectado en `producto.md` / `plan-desarrollo.md` si cambió alcance.
3. Línea en `portal/CHANGELOG.md`.
