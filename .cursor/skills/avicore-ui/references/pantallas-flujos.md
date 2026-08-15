# 02 — Pantallas y flujos

> **Gobernanza incremental:** solo se detalla aquí lo que tiene ruta/UI en el repo. Pantallas planificadas: una línea + enlace a [`plan-desarrollo.md`](../../avicore-contexto/references/plan-desarrollo.md). Al implementar, expandir la sección correspondiente en el mismo PR.

## 1. Objetivo

Definir las pantallas principales de AviCore, sus campos, acciones, usuarios autorizados y comportamiento esperado.

---

## 2. Pantalla: Login

### Objetivo

Permitir el acceso seguro al sistema.

### Usuarios

- Admin AviCore.
- Dueño.
- Administrativo.
- Encargado.
- Operario.

### Campos

- Documento (con `AVICORE_DEMO_LOGIN=true`: visible, vacío y deshabilitado).
- Contraseña (con `AVICORE_DEMO_LOGIN=true`: visible, vacía y deshabilitada).
- Perfil (select; `AVICORE_DEMO_LOGIN=true`).
- Recordarme (opcional).

### Acciones

- Iniciar sesión.
- Cerrar sesión (`POST /logout`).

### Presentación (MVP implementado)

- Layout público en **split** (≥1024px): panel de marca a la izquierda (`auth-brand-panel`: logo `hero` con animación de entrada `entrance` y copy en columna alineada), tarjeta de login a la derecha.
- En **móvil** (<1024px): fondo `login-background.jpg` (granja al atardecer), logo apilado centrado sobre la foto (`entrance` — órbita del isotipo alrededor del wordmark) y tarjeta blanca anclada abajo con esquinas superiores redondeadas (bottom sheet).
- **PWA:** banner inferior «Instalá AviCore» si no está instalada (`AVICORE_PWA_INSTALL_PROMPT=true`); Chrome/Android → botón Instalar; iOS → guía Compartir. Detalle: `avicore-pwa/references/pwa.md`.
- Inputs con icono Lucide (`id-card`, `lock-keyhole`) y **toggle** para mostrar/ocultar contraseña (un solo control visible).
- Checkbox «Recordarme» con foco visible.
- **Modo demo MVP** (`AVICORE_DEMO_LOGIN=true`): un usuario (`000000000`); el selector asigna el rol al entrar (sin credenciales en pantalla).
- Recuperación de contraseña: enlace **«¿Olvidaste tu contraseña?»** abre contacto de soporte (`x-ui.sheet`: bottom sheet en móvil, diálogo centrado en escritorio ≥1024px; WhatsApp y correo vía `config/avicore.php` / `.env`); sin flujo automático de reset en MVP (ver regla de negocio en `05`).

### Validaciones

- **Demo (`AVICORE_DEMO_LOGIN=true`):** perfil obligatorio; documento/contraseña no se validan; errores de rate-limit y empresa inactiva en campo `demoRole`.
- **Login normal:** documento obligatorio (máx. 50 caracteres); contraseña obligatoria.
- Usuario activo.
- Empresa activa (estado `activa`; no aplica a Admin AviCore).
- Usuario no Admin AviCore sin empresa asignada no puede iniciar sesión.
- Credenciales válidas (solo login normal).
- Máximo 5 intentos fallidos por documento e IP en 60 segundos; luego mensaje con tiempo de espera.
- Si el documento coincide en más de una cuenta activa con la misma contraseña, se rechaza el acceso (contactar administrador).

### Comportamiento

Tras login exitoso:

1. Si `must_change_password` → `/password/change`.
2. Si no → home según rol: operario → `/operario`; resto (Dueño, Administrativo, Encargado, Admin AviCore) → `/admin`.

Usuario autenticado que visita `/login` se redirige a su home correspondiente.

La raíz `/` redirige: sin sesión → `/login`; con sesión → home del rol (o `/password/change` si aplica).

---

## 3. Pantalla: Cambio obligatorio de contraseña

### Objetivo

Forzar al usuario a cambiar la contraseña temporal.

### Campos

- Contraseña actual.
- Nueva contraseña.
- Confirmar nueva contraseña.

### Acciones

- Guardar nueva contraseña.

### Validaciones

- Contraseña actual correcta.
- Nueva contraseña segura: mínimo 8 caracteres, letras, mayúsculas y minúsculas, números.
- Nueva contraseña distinta a la actual.
- Confirmación coincidente.
- No permitir seguir sin cambiarla.

### Presentación

Mismo **layout público** que login: split en escritorio (≥1024px) y bottom sheet en móvil; inputs con iconos Lucide (`lock-keyhole`, `key-round`, `shield-check`), placeholders, hint de política en nueva contraseña, toggle de contraseña y enlace de soporte con el mismo diálogo de contacto que login (`x-auth.support-contact-dialog`).

### Comportamiento

Mientras `must_change_password` sea verdadero, el middleware bloquea el acceso a `/admin`, `/operario` y demás rutas protegidas excepto esta pantalla.

Tras guardar: `must_change_password` pasa a falso y redirección al home del rol (`/admin` o `/operario`).

---

## 3.1 Pantalla: Inicio admin (MVP)

**Estado MVP (2026-07-16):** shell **visual** igual al operario (sidebar `lg+`, bottom nav móvil, home-nav + sheet), con **tabs y contenido solo de gestión** (sin Campo/carga). Detalle: `patrones-web-admin.md`.

### Objetivo

Landing post-login para roles con panel administrativo (Dueño, Administrativo, Encargado, Admin AviCore): contexto de empresa, KPIs de gestión, accesos a módulos de administración y guía de configuración.

### Usuarios

- Dueño.
- Administrativo.
- Encargado.
- Admin AviCore.

### Elementos

- Layout: `components/layouts/admin.blade.php` reutiliza clases `avicore-operario-*`; nav `AdminNav` (Inicio · Usuarios); menú cuenta `x-ui.user-menu`; PWA (`x-ui.pwa-meta` + banner instalar si `AVICORE_PWA_INSTALL_PROMPT=true`).
- Hero: saludo horario + subtítulo `Resumen de {empresa · rol}.` + chip de empresa (`avicore-admin-context`).
- KPIs: `<x-ui.kpi-card>` — Usuarios activos (conteo real); Granjas y galpones (placeholder hasta estructura).
- Accesos («¿Qué querés gestionar?»): tarjetas de gestión (`avicore-admin-home-action`) — Usuarios; Estructura y Reportes → «Próximamente».
- Checklist «Estado inicial» en panel paralelo (escritorio).
- **No incluye** paneles/tiles de carga operario (`kpi-panel`, `carga-tile`, chip de galpón) ni accesos a Cargar/Historial.

### Navegación (MVP)

- **Inicio** — `/admin` (hero).
- **Usuarios** — `/admin/usuarios` (hero + CRUD; §3.2).

### Comportamiento

Tras login exitoso (sin cambio de contraseña pendiente), roles no operario llegan a `/admin` con esta pantalla.

---

## 3.2 Pantalla: Usuarios (admin)

**Estado MVP (2026-07-16):** implementado en `/admin/usuarios` — listado con búsqueda/filtros, alta/edición en diálogo, reset de contraseña temporal (mostrada una vez) y activar/desactivar.

### Objetivo

Gestionar el equipo de la empresa: crear usuarios con rol, editar datos, resetear contraseña temporal y desactivar cuentas.

### Usuarios autorizados

| Acción | Admin AviCore | Dueño | Administrativo | Encargado | Operario |
|--------|---------------|-------|----------------|-----------|----------|
| Ver listado | Sí (todas las empresas) | Sí (su empresa) | Sí (su empresa) | Sí (su empresa) | No |
| Crear / editar / activar-desactivar | Sí | Sí | Sí | No | No |
| Reset contraseña | Sí | Sí | Sí | Sí | No |

Roles asignables: Dueño (dueño→operario); Administrativo (administrativo→operario); Admin AviCore (todos, con selector de empresa salvo `admin_avicore`).

### Campos (alta / edición)

- Nombre completo (obligatorio).
- Documento (obligatorio; único por `empresa_id`).
- Correo (opcional).
- Rol (select según permisos del actor).
- Empresa (solo Admin AviCore en alta; no aplica a rol Admin AviCore).
- Activo (solo edición).

### Acciones

- Nuevo usuario → genera contraseña temporal + `must_change_password = true`; diálogo muestra la clave una sola vez.
- Editar → actualiza datos y rol (sin cambiar empresa).
- Reset clave → nueva temporal + `must_change_password`; no sobre sí mismo.
- Activar / Desactivar → no sobre sí mismo.
- Filtros: búsqueda (nombre/documento/correo), rol, estado (activos por defecto).

### Presentación

- Layout admin (`components.layouts.admin`) + snackbar.
- Tabla responsive con avatar, badges de rol/estado; empty state si no hay resultados.
- Diálogos `x-ui.dialog` para formulario y para revelar contraseña temporal.

### Comportamiento

Multiempresa: actores de empresa solo ven/modifican usuarios de su `empresa_id`. Operario redirigido fuera de `/admin/*`. Policy: `UserPolicy`.

---

## 4. Pantalla: Dashboard

**Estado:** planificado — fase 17 en [`plan-desarrollo.md`](../../avicore-contexto/references/plan-desarrollo.md) §2; tiempo real asociado en Bloque 6. Tarjetas, filtros y actualización en vivo se documentarán al implementar `Livewire/Dashboard/`.

---

## 5. Pantalla: Vista móvil del operario

**Estado MVP (2026-06-28):** implementado en `/operario` — shell responsive: **móvil** con barra inferior integrada (3 pestañas: Inicio · Cargar · Historial); **escritorio (≥1024px)** con sidebar verde (`x-operario.sidebar-nav`), contenido ancho (`max-w-6xl`) y bottom nav oculta. Detalle visual: `patrones-desktop-operario.md`. Heroes compactos con degradado suave, **panel de estado del galpón** (KPIs por galpón seleccionado: aves, huevos/muertes hoy, acumulado desde ingreso de lotes activos, lista de lotes con edad; galpón solo en chip del hero; sin enlace duplicado a Historial). Header hero fijo en móvil: grilla logo/usuario + línea ogee (`avicore-home-nav`); en escritorio el nav superior se oculta y la cuenta vive en sidebar. Avatar abre **menú cuenta** (`x-operario.user-menu`: dropdown Perfil + Cerrar sesión). Nav: `OperarioNav`; layout hero: `operarioIsHeroPage` (Inicio + Cargar + Historial). **PWA:** mismo banner/manifest que login (`avicore-pwa/references/pwa.md`).

### Navegación móvil (3 pestañas)

| Pestaña | Ruta | Contenido |
|---------|------|-----------|
| Inicio | `/operario` | Hero compacto, saludo, selector galpón, resumen KPI (aves, huevos/muertes hoy, acumulado, lotes activos) |
| Cargar | `/operario/cargar` | Hero + hoja con tipos; chip galpón interactivo; sin galpón → selector en página; diálogos `x-ui.dialog` solo si están abiertos (perf); deep link `?form=` o `/operario/carga/*` (sin galpón → `?abrir_galpon=1`) |
| Historial | `/operario/historial` | Hero degradado; listado completo; chip galpón interactivo; filtro `?fecha=` vía `x-ui.date-picker`; meta tipo·galpón desde `md:`; paginación 20 |

En **Inicio**, el header fijo muestra logo + usuario (rol con `label()`); el avatar abre menú cuenta (perfil y logout). El galpón se elige con chip desplegable en el hero («Estado de hoy del galpón.»). La hoja blanca muestra KPIs y lotes activos del galpón seleccionado (`OperarioGalponResumenService`; edad de lote vía `edadSemanas()`), sin repetir el nombre del galpón ni enlace a Historial. Sin galpón: mensaje para elegir uno. Bloques de sección con `x-ui.reveal` (fade+slide al entrar en viewport; sin cascada en listas). Cargar e Historial por pestañas del dock.

### Objetivo

Permitir carga rápida desde celular.

### Usuarios

- Operario.
- Encargado, si necesita cargar.

### Elementos (por pestaña)

**Inicio:** saludo, chip galpón (selector), KPIs del galpón (aves, muertes/descarte aves hoy, huevos **aptos + descarte** hoy y acumulados), lista de lotes activos (con **SMA** si existe).

**Cargar:** Huevos, Muertes, **Descarte de aves**, Vacunación, **Alimento** (entrega del camión) y (si el rol puede crear lote) **Nuevo lote** — grilla 2 columnas; con permiso de lote, tile ancho «Nuevo lote». Diálogos: huevos aptos + descarte; descarte de aves vivas; alimento en kg del remito; vacunación con `x-ui.select`; nuevo lote: galpón, **Nº lote SMA** (opcional), tipos Blanca/Colorada, cantidad, fecha nacimiento. Sin carga combinada en móvil.

**Historial:** listado completo del operario (cargas + vacunaciones), filtro por fecha con `x-ui.date-picker` (sin `input type="date"` nativo; error de validación visible bajo el trigger), paginación. Cada ítem abre **detalle** (tipo, galpón, fecha/hora, resumen). Registros **anulados** visibles con badge; el operario puede **anular** registros propios del día con motivo obligatorio (`x-ui.textarea` en el diálogo; muertes/descarte restauran `aves_actuales`; vacunación vía `AnularVacunacionAction`). Dueño/administrativo/encargado pueden anular registros ajenos del día vía policy (sin UI en Historial MVP).

**Compartido:** logo, menú cuenta (avatar), dock inferior (Inicio · Cargar · Historial).

### Perfil de cuenta (MVP)

**Estado MVP (2026-08-11):** `/operario/perfil` (shell operario) y `/perfil` (shell admin). Pestañas con `wire:navigate` + query `?seccion=password` (sin morph parcial). Partials `tabs`, `datos-form`, `password-form`. Menú cuenta: **Editar datos** / **Cambiar contraseña** (misma navegación). `UpdateProfileAction` y `ChangePasswordAction` exigen `UserPolicy::updateProfile`.

| Campo | Editable por el usuario |
|-------|-------------------------|
| Nombre | Sí |
| Correo | Sí (opcional) |
| Contraseña | Sí (actual + nueva + confirmar; misma política que cambio obligatorio) |
| Documento | No (solo lectura; lo gestiona admin) |
| Rol / Empresa | No (solo lectura) |

Tras guardar: snackbar de confirmación. Sin reset por correo en MVP (contacto soporte vía `x-auth.support-contact-dialog`).

### Flujo

```text
Seleccionar tipo de carga en hub → diálogo centrado con formulario → guardar → snackbar → permanece en hub Cargar
```

Huevos: aptos + descarte (al menos uno > 0). Muertes, descarte de aves y alimento: formularios separados. Tras **Guardar**: snackbar de confirmación y cierre automático del diálogo (vuelve al hub Cargar). Vacunación: lote activo + tipo (`VacunaTipo`).

---

## 6. Pantalla: Selector de galpón

**Estado MVP (2026-06-22):** integrado en **Inicio** (`/operario`) — chip desplegable sobre el hero; persiste `users.ultimo_galpon_id` al elegir un ítem. Sin ruta `/operario/galpon` dedicada.

### Objetivo

Permitir elegir galpón de trabajo.

### Campos

- Empresa actual.
- Granja.
- Galpón.

### Reglas

- Solo se listan galpones **activos** de la empresa con `estado = activo` y `activo = true` (disponibles para carga).
- La validación Livewire exige que el `galpon_id` pertenezca a la empresa del usuario y cumpla esas condiciones (`Rule::exists` con scope).
- `GalponPolicy::view`, `OperarioGalponService::galponDisponibleParaUsuario` y `seleccionarGalpon` refuerzan multiempresa y disponibilidad.
- El usuario puede elegir cualquier galpón disponible de su empresa.
- El sistema recuerda el último galpón seleccionado (`users.ultimo_galpon_id`).
- Si el galpón recordado deja de estar disponible, la carga abre el selector en la pantalla actual (`selectorGalponAbierto`); deep links sin galpón → `/operario/cargar?abrir_galpon=1`. Flash `abrirSelectorGalpon` y `?abrir_galpon=1` los consume `ManagesGalponSelector::bootGalponSelector`.
- Tras elegir galpón: snackbar «Galpón actualizado.» (`dispatch snackbar-show`).

---

## 7. Pantalla: Carga de huevos

**Estado MVP (2026-08-11):** formulario huevos en diálogo «Huevos de hoy» desde hub `/operario/cargar` (`CargarHub` + `x-ui.dialog`); campos **aptos** y **descarte** (rotos/sucios); al menos un total > 0; `created_at` automático; deep link `/operario/carga/huevos` → redirect con `?form=huevos` (`CargaHuevos` usa vista `livewire._redirect-placeholder`). Evento tiempo real: pendiente (Bloque 6).

### Campos

- Galpón actual (contexto en hero; no se repite en el diálogo).
- Huevos aptos (comerciales).
- Huevos de descarte (rotos o sucios; puede ser 0).

### Reglas

- Fecha y hora automática.
- No hay selector de fecha/hora para operario.
- Al menos un huevo entre aptos y descarte (> 0 en conjunto).
- Debe guardar en unidad huevos (`huevos` + `huevos_descarte`).
- Requiere galpón disponible; sin galpón o galpón no disponible → redirección a `/operario` con selector abierto (no hay ruta `/operario/galpon`).
- `RegistrarCargaHuevosAction` valida empresa, permiso (`GalponPolicy`) y estado del galpón.
- Debe emitir evento en tiempo real.

---

## 8. Pantalla: Carga de muertes

**Estado MVP (2026-07-02):** formulario muertes en diálogo centrado desde hub `/operario/cargar` (`CargarHub` + `x-ui.dialog`); solo cantidad obligatoria; descuenta `aves_actuales`; deep link `/operario/carga/muertes` → redirect con `?form=muertes` (`CargaMuertes` usa vista `livewire._redirect-placeholder`). Evento tiempo real: pendiente (Bloque 6).

### Campos

- Galpón actual (contexto en hero; no se repite en el diálogo).
- Cantidad de muertes.

### Reglas

- Fecha y hora automática.
- Cantidad obligatoria (> 0) y no mayor que aves vivas del galpón.
- Requiere galpón disponible; sin galpón o galpón no disponible → redirección a `/operario` con selector abierto.
- `RegistrarCargaMuertesAction` valida empresa, permiso (`GalponPolicy`), estado del galpón y stock de aves (bloqueo pesimista en transacción).
- Debe emitir evento en tiempo real.

---

## 8.5 Pantalla: Carga de vacunación

**Estado MVP (2026-07-02):** formulario vacunación en diálogo centrado desde hub `/operario/cargar` (`CargarHub` + `x-ui.dialog` + `partials/carga-vacunacion-form`); lote y vacuna obligatorios; `x-ui.select` con `wire:model.defer` (sin re-render del botón Guardar al elegir); deep link `/operario/carga/vacunacion` → redirect con `?form=vacunacion` (`CargaVacunacion` usa vista `livewire._redirect-placeholder`). Evento tiempo real: pendiente (Bloque 6).

### Campos

- Galpón actual (contexto en hero; no se repite en el diálogo).
- Lote a vacunar (solo lotes activos/en producción del galpón).
- Tipo de vacuna (`VacunaTipo`).

### Reglas

- Fecha y hora automática (`created_at`).
- Requiere galpón disponible; sin galpón o galpón no disponible → redirección a `/operario` con selector abierto.
- `RegistrarVacunacionAction` valida empresa, permiso (`GalponPolicy`), estado del galpón, pertenencia lote↔galpón y estado del lote.
- Sin lotes activos: mensaje en el diálogo (no se muestra formulario).
- Snackbar: «Vacunación guardada.»

---

## 8.6 Pantalla: Alta de lote nuevo

**Estado MVP (2026-07-05):** formulario en diálogo desde hub `/operario/cargar` (`CargarHub` + `partials/carga-lote-form`); tile «Nuevo lote» en grilla 2×2 (`--quad`); **oculto para operario** (`UserRole::canCreateLote()`). Deep link `/operario/carga/lote` → `?form=lote` (`CargaLote` redirect-only).

### Campos

- Galpón (`disponiblesParaCarga()` de la empresa).
- **Nº lote SMA** (opcional, texto libre — código del sistema del gobierno).
- Tipo de ave / huevo: multi-selección UI «Blanca» / «Colorada» → `TipoHuevo` (`blanco` / `color`); un lote por tipo marcado.
- Fecha aproximada de nacimiento (`fecha_nacimiento`).
- Cantidad por tipo marcado → `cantidad_inicial` de cada lote.

### Reglas

- `codigo` generado en servidor: `{codigo_galpon}-{YYYYMMDD}-{B|C}-{secuencia}`; `fecha_ingreso` = hoy al registrar.
- `estado` inicial `activo`; suma `cantidad_inicial` a `aves_actuales` del galpón (transacción + lock).
- `LotePolicy::create` + `RegistrarLoteAction`.
- Snackbar con código(s) generados: «Lote {codigo} registrado.» o «Lotes registrados: …».
- Tests: `OperarioCargaLoteTest` (flujo hub, Action, gating por rol, bordes Livewire); deep link HTTP `?form=lote` en `OperarioBottomNavTest`.

---

## 8.7 Pantalla: Entrega de alimento

**Estado MVP (2026-08-11):** formulario en diálogo «Entrega de alimento» desde hub `/operario/cargar` (`CargarHub` + `ManagesAlimentoForm` + `partials/carga-alimento-form`); tile con icono camión; deep link `/operario/carga/alimento` → `?form=alimento` (`CargaAlimento` redirect-only).

### Campos

- Galpón actual (contexto en hero).
- Kilogramos entregados (remito del camión).

### Reglas

- Fecha y hora automática (`created_at`).
- Cantidad obligatoria (> 0 kg); decimales permitidos.
- **No** es consumo diario: el operario registra cada llegada del camión (puede haber varios días sin registro).
- `RegistrarCargaAlimentoAction` — tipo `alimento`, mismo criterio de permisos/galpón que huevos.
- Historial: resumen «X kg entregados».

---

## 8.75 Pantalla: Descarte de aves

**Estado MVP (2026-08-11):** diálogo «Descarte de aves» desde hub; tile aparte de Muertes; deep link `/operario/carga/descarte` → `?form=descarte`.

### Campos

- Cantidad de aves descartadas (vivas que se sacan del galpón).

### Reglas

- Distinto de **muertes** (aves que murieron en el piso).
- Descuenta `aves_actuales` igual que muertes.
- `RegistrarCargaDescarteAction` — tipo `descarte`, campo `descarte_aves`.

---

## 9–10. Cargas operario (parcial)

**Estado:** huevos (aptos/descarte), muertes, **descarte de aves**, vacunación y **alimento** implementados en hub `/operario/cargar`; combinada pendiente (fase 15). Reglas en [`avicore-negocio/references/reglas.md`](../../avicore-negocio/references/reglas.md).

| Pantalla | Fase | Estado |
|----------|------|--------|
| Carga de huevos | 12 | Hecho — aptos + descarte; `RegistrarCargaHuevosAction` |
| Carga de muertes | 13 | Hecho — diálogo en hub; `RegistrarCargaMuertesAction`; deep link `?form=muertes` |
| Descarte de aves | — | Hecho — `RegistrarCargaDescarteAction`; deep link `?form=descarte` |
| Carga de vacunación | — | Hecho — diálogo en hub; `RegistrarVacunacionAction`; deep link `?form=vacunacion` |
| Carga de alimento | 14 | Hecho — entrega camión (kg); `RegistrarCargaAlimentoAction`; deep link `?form=alimento` |
| Carga combinada | 15 | **Defer** — dos cargas rápidas en lugar de formulario mixto |

---

## Pantallas planificadas (admin y reportes)

| Pantalla | Fase 12-plan | Fuente al implementar |
|----------|--------------|------------------------|
| Empresas | 7 | Esta guía § nueva + `avicore-negocio/references/permisos.md` |
| Granjas | 8 | Esta guía + CRUD admin |
| Galpones | 9 | Esta guía + `avicore-modelo-datos/references/esquema-bd.md` |
| Lotes | 10 | Esta guía + `avicore-negocio/references/reglas.md` |
| Usuarios | 5 | **Hecho MVP** — esta guía §3.2 + `permisos.md` |
| Auditoría | 16 | Esta guía + tabla `auditorias` (cuando exista migración) |
| Reportes | 19 | `avicore-reportes/references/reportes.md` |
