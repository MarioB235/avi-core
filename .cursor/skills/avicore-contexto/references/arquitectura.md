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
| PostgreSQL | Según instalación local (ej. 16–18) |
| Node.js | **22** (CI y Laravel Cloud) |
| pnpm | 10.x (`packageManager` en `package.json`; lockfile `pnpm-lock.yaml`) |
| PWA (manifest + SW assets) | **Hecho MVP** — ver [`estado-capacidades.md`](estado-capacidades.md) y `avicore-pwa/references/pwa.md` |
| Reverb + Echo | **Pendiente** — ver `avicore-tiempo-real/references/eventos.md` |

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
- **Login:** `LoginCandidateResolver` (documento + contraseña + vigencia) y `AccountAccessService` (`activo` + `Empresa::permiteLogin()`); sin selector de empresa en MVP.
- **Sesiones:** `UserSessionService` invalida filas en `sessions` al resetear clave, cambiar contraseña o desactivar usuario; requiere `SESSION_DRIVER=database` (detalle en `arranque-local.md`; con `file` no-op documentado).
- **Middleware auth:** `EnsurePasswordChanged`, `EnsureRolePanelAccess`, `EnsureOperarioAccess`, `RedirectIfAuthenticated`.
- **Vigencia por request:** `EnsureAccountVigente` (grupo `web`, incluye `POST /livewire/update`) revalida usuario y empresa; si falla, cierra sesión sin mutar datos. Refresca usuario en cada request.
- **Rol y clave en Livewire:** `EnsurePasswordChanged`, `EnsureOperarioAccess` y `EnsureRolePanelAccess` registrados como middleware persistente Livewire para que snapshot abierto no evite restricciones tras cambio de rol o reset de contraseña.
- **Autorización por request Livewire:** `AdminModulePolicy` + Gates `admin.viewResumen|Equipo|Comercial` y trait `RequiresAdminModuleAccess` (`mount` + `hydrate`) en Resumen/Equipo/Comercial; `UserPolicy` / `GranjaPolicy` / … en Usuarios/Estructura; operario usa `LotePolicy::create` en `ManagesLoteForm`; `authorize()` explícito en mutaciones sensibles.
- **Scope por empresa:** `EmpresaScopeService` (+ `AdminResumenService::galponesEnScope`) y trait `BelongsToEmpresa::forEmpresa()`; `EmpresaContextService` para override Admin AviCore (soporte futuro).
- **Coherencia padre/hijo:** `EmpresaRelationalGuard` en Actions transaccionales (granja→galpón, galpón→lote, actor→recurso).

En módulos operativos (galpones, lotes, registros): policies y scope por `empresa_id` en consultas y Actions — ver [`permisos.md`](../../avicore-negocio/references/permisos.md).

Pendiente para v1 (plan SEG/EMP):

- Circuito completo de empresas y modo soporte auditado.

---

## 5b. Superficies técnicas (SEG-12)

| Superficie | Implementación |
|---|---|
| CSRF | Grupo `web` en rutas POST (p. ej. `/logout`); meta `csrf-token` en layouts; formularios con `@csrf` |
| Escape XSS | Blade `{{ }}` en datos de usuario; SVG de iconos/ilustraciones solo desde nombres validados (`SafeAssetName`) |
| Archivos/logo | `EmpresaLogoPathGuard`: rutas relativas bajo `empresas/logos/`, sin URL absoluta ni `..` (listo para EMP-03) |
| HTTPS/cookies | `trustProxies` en `bootstrap/app.php`; `ProductionSecurityConfig` fuerza `session.secure` + `same_site=lax` en `production` |
| Dependencias | `composer audit --locked` en CI y `pnpm run check:security` |

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
