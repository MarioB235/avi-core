# 07 — Arquitectura técnica

> **Árbol de carpetas y mapa módulo → código:** [`arbol-proyecto.md`](arbol-proyecto.md)

## 1. Stack definido

```text
Laravel + PostgreSQL + Livewire + Tailwind CSS + Alpine.js + PWA + Laravel Reverb + Echo
```

### Versiones instaladas (Bloque 1, 2026-05-31)

| Componente | Versión |
|------------|---------|
| Laravel | 13.x |
| Livewire | 4.x |
| Tailwind CSS | 4.x (Vite plugin) |
| PHP | 8.3+ |
| PostgreSQL | Según instalación local (ej. 18) |
| Node / pnpm | 10.x (`pnpm-lock.yaml`; ver [`arranque-local.md`](arranque-local.md)) |

Reverb, Echo y PWA: pendientes (Bloques 6–7 del plan).

---

## 1b. Entorno local

Procedimiento completo (PostgreSQL, pgAdmin, `.env`, migrate, serve): [`arranque-local.md`](arranque-local.md). Producción (Laravel Cloud): [`deploy-laravel-cloud.md`](deploy-laravel-cloud.md).

## 2. Función de cada tecnología

| Tecnología | Función |
|---|---|
| Laravel | Backend, reglas, rutas, autenticación, permisos, auditoría, reportes |
| PostgreSQL | Base relacional |
| Livewire | Interfaz dinámica |
| Tailwind CSS | Diseño responsivo |
| Alpine.js | Interacciones pequeñas |
| PWA | Experiencia instalable en celular |
| Laravel Reverb | WebSockets |
| Laravel Echo | Cliente JS para eventos |

---

## 3. Principios

1. No poner lógica de negocio en vistas.
2. Usar Services o Actions para reglas importantes.
3. Usar Policies/Gates para permisos.
4. Usar Events para tiempo real.
5. Usar auditoría centralizada.
6. Respetar empresa_id en toda consulta.
7. Separar panel web y vista móvil.

---

## 4. Estructura de código

Ver árbol completo y convenciones en [`arbol-proyecto.md`](arbol-proyecto.md).

Vistas Blade servidas con `Route::view` (p. ej. Inicio admin): datos vía **View Composer** en `app/Http/View/Composers/` + **Service**; sin `@php` de negocio en la vista.

---

## 5. Multiempresa

Toda consulta debe filtrar por empresa_id salvo Admin AviCore en modo soporte.

Implementado (Bloque 2):

- **`EmpresaContextService`:** resuelve `empresa_id` de la sesión; Admin AviCore puede override en sesión (`avicore.empresa_context_id`) validando que la empresa exista (modo soporte futuro).
- **Login:** validación de empresa activa vía `Empresa::permiteLogin()` (solo estado `activa`).
- **Middleware auth:** `EnsurePasswordChanged`, `EnsureAdminPanelAccess`, `EnsureOperarioAccess`, `RedirectIfAuthenticated`.

Pendiente en módulos siguientes:

- Scope global en modelos operativos.
- Policies que validen empresa_id en CRUD.

---

## 6. Livewire

Usar Livewire para:

- Dashboard.
- Vista móvil operario.
- Formularios dinámicos.
- Filtros.
- Tablas interactivas.
- Alertas.

No abusar de Livewire en pantallas simples si Blade alcanza.

---

## 7. Reverb + Echo

Usar tiempo real en:

- Dashboard.
- Alertas.
- Producción del día.
- Mortalidad del día.
- Anulaciones.
- Correcciones.
- Ajustes de aves vivas.

No usar tiempo real en:

- CRUD de empresas.
- CRUD de granjas.
- CRUD de usuarios.
- Configuración.
- Exportaciones.

---

## 8. PWA

Incluir:

- Manifest.
- Íconos.
- Instalación en celular.
- Optimización móvil.

Offline complejo queda para futuro.

---

## 9. Reportes

- PDF desde backend.
- Excel desde backend.
- Generación manual.
- Identidad cliente + AviCore.

---

## 10. Jobs y colas

Usar Jobs para procesos que puedan crecer:

- Generar reportes pesados.
- Recalcular indicadores.
- Enviar eventos.
- Importar datos demo.
