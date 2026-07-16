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

- Documento (en local demo: visible, vacío y deshabilitado).
- Contraseña (en local demo: visible, vacía y deshabilitada).
- Perfil (select; solo `APP_ENV=local` + `AVICORE_DEMO_LOGIN=true`).
- Recordarme (opcional).

### Acciones

- Iniciar sesión.
- Cerrar sesión (`POST /logout`).

### Presentación (MVP implementado)

- Layout público en **split** (≥1024px): panel de marca a la izquierda (`auth-brand-panel`: logo `hero` con animación de entrada `entrance` y copy en columna alineada), tarjeta de login a la derecha.
- En **móvil** (<1024px): fondo `login-background.jpg` (granja al atardecer), logo apilado centrado sobre la foto (`entrance` — órbita del isotipo alrededor del wordmark) y tarjeta blanca anclada abajo con esquinas superiores redondeadas (bottom sheet).
- Inputs con icono Lucide (`id-card`, `lock-keyhole`) y **toggle** para mostrar/ocultar contraseña (un solo control visible).
- Checkbox «Recordarme» con foco visible.
- **Modo demo local** (`APP_ENV=local` + `AVICORE_DEMO_LOGIN=true`): documento y contraseña quedan vacíos y deshabilitados (sin lógica de credenciales); el acceso es solo con el selector **Perfil** (`x-ui.select` + `wire:model.live`), que autentica al usuario seedeado del rol (`DemoLoginService` + `AttemptLoginAction::executeDemo`). Tras login, redirect **full page** (sin Livewire `navigate`) para cambiar de layout público al admin u operario móvil. Fuera de local o con flag en `false`: login normal por documento + contraseña.
- Recuperación de contraseña: enlace **«¿Olvidaste tu contraseña?»** abre contacto de soporte (`x-ui.sheet`: bottom sheet en móvil, diálogo centrado en escritorio ≥1024px; WhatsApp y correo vía `config/avicore.php` / `.env`); sin flujo automático de reset en MVP (ver regla de negocio en `05`).

### Validaciones

- **Demo local:** perfil obligatorio; documento/contraseña no se validan; errores de rate-limit y empresa inactiva en campo `demoRole`.
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

### Objetivo

Landing post-login para roles con panel administrativo (Dueño, Administrativo, Encargado, Admin AviCore): contexto de empresa, KPIs en estado vacío y guía de configuración inicial.

### Usuarios

- Dueño.
- Administrativo.
- Encargado.
- Admin AviCore.

### Elementos

- Layout admin: sidebar verde (`avicore-primary`), logo con subtítulo «Gestión operativa avícola» en blanco, secciones «Navegación» y «Cuenta», perfil con iniciales y «Colapsar menú» (escritorio).
- Header: barra blanca sticky — título e contexto `{empresa o AviCore} · {rol}` en la misma línea; pill de fecha, campana (deshabilitada hasta módulo) y perfil con avatar a la derecha.
- **Masthead** (escritorio): tarjeta con foto de granja, copy «¡Bienvenido de nuevo!» y KPIs en fila debajo; contenido alineado a `max-w-7xl`.
- Hero con foto de granja (`admin-home-hero`) — **solo escritorio** (≥1024px); hero móvil reservado para asset futuro.
- Cuatro KPIs: Producción de hoy, Galpones activos, Alertas (empty state), Usuarios activos (conteo real por empresa; Admin AviCore ve total de usuarios activos).
- Card «Estado inicial»: checklist Granjas / Galpones / Usuarios (Pendiente) y botón «Configurar estructura» deshabilitado hasta existir módulos.
- Card «Actividad reciente»: empty state hasta haya operación.

### Navegación lateral (MVP)

- **Inicio** — activo en `/admin`.
- **Dashboard**, **Estructura**, **Usuarios**, **Reportes**, **Auditoría**, **Notificaciones** — visibles; ítems futuros deshabilitados con badge «Próximamente» (sin contador falso en notificaciones).

### Comportamiento

Tras login exitoso (sin cambio de contraseña pendiente), roles no operario llegan a `/admin` con esta pantalla.

---

## 4. Pantalla: Dashboard

**Estado:** planificado — fase 17 en [`plan-desarrollo.md`](../../avicore-contexto/references/plan-desarrollo.md) §2; tiempo real asociado en Bloque 6. Tarjetas, filtros y actualización en vivo se documentarán al implementar `Livewire/Dashboard/`.

---

## 5. Pantalla: Vista móvil del operario

**Estado MVP (2026-06-28):** implementado en `/operario` — shell responsive: **móvil** con barra inferior integrada (3 pestañas: Inicio · Cargar · Historial); **escritorio (≥1024px)** con sidebar verde (`x-operario.sidebar-nav`), contenido ancho (`max-w-6xl`) y bottom nav oculta. Detalle visual: `patrones-desktop-operario.md`. Heroes compactos con degradado suave, **panel de estado del galpón** (KPIs por galpón seleccionado: aves, huevos/muertes hoy, acumulado desde ingreso de lotes activos, lista de lotes con edad; galpón solo en chip del hero; sin enlace duplicado a Historial). Header hero fijo en móvil: grilla logo/usuario + línea ogee (`avicore-home-nav`); en escritorio el nav superior se oculta y la cuenta vive en sidebar. Avatar abre **menú cuenta** (`x-operario.user-menu`: dropdown Perfil + Cerrar sesión). Nav: `OperarioNav`; layout hero: `operarioIsHeroPage` (Inicio + Cargar + Historial).

### Navegación móvil (3 pestañas)

| Pestaña | Ruta | Contenido |
|---------|------|-----------|
| Inicio | `/operario` | Hero compacto, saludo, selector galpón, resumen KPI (aves, huevos/muertes hoy, acumulado, lotes activos) |
| Cargar | `/operario/cargar` | Hero + hoja con tipos; chip galpón interactivo; sin galpón → selector en página; diálogos `x-ui.dialog`; deep link `?form=` o `/operario/carga/*` (sin galpón → `?abrir_galpon=1`) |
| Historial | `/operario/historial` | Hero degradado; listado completo; chip galpón interactivo; filtro `?fecha=` vía `x-ui.date-picker`; meta tipo·galpón desde `md:`; paginación 20 |

En **Inicio**, el header fijo muestra logo + usuario (rol con `label()`); el avatar abre menú cuenta (perfil y logout). El galpón se elige con chip desplegable en el hero («Estado de hoy del galpón.»). La hoja blanca muestra KPIs y lotes activos del galpón seleccionado (`OperarioGalponResumenService`; edad de lote vía `edadSemanas()`), sin repetir el nombre del galpón ni enlace a Historial. Sin galpón: mensaje para elegir uno. Cargar e Historial por pestañas del dock.

### Objetivo

Permitir carga rápida desde celular.

### Usuarios

- Operario.
- Encargado, si necesita cargar.

### Elementos (por pestaña)

**Inicio:** saludo, chip galpón (selector), KPIs del galpón (aves, huevos/muertes hoy, acumulado), lista de lotes activos.

**Cargar:** Huevos, Muertes, Vacunación y (si el rol puede crear lote) **Nuevo lote** — grilla 2×2 con `--quad` para perfiles autorizados; operario ve grilla `--triple` (Huevos · Muertes arriba; Vacunación ancho abajo). Sin alimento ni combinada en móvil operario. Preguntas directas en diálogo; vacunación usa `x-ui.select` (lote + tipo de vacuna); nuevo lote: select galpón, checkboxes Blanca/Colorada, cantidad por tipo, fecha nacimiento.

**Historial:** listado completo del operario (cargas + vacunaciones), filtro por fecha con `x-ui.date-picker` (sin `input type="date"` nativo; error de validación visible bajo el trigger), paginación.

**Compartido:** logo, menú cuenta (avatar), dock inferior (Inicio · Cargar · Historial).

### Flujo

```text
Seleccionar tipo de carga en hub → diálogo centrado con formulario → guardar → snackbar → permanece en hub Cargar
```

Huevos/muertes: solo cantidad. Vacunación: lote activo del galpón + tipo de vacuna (`VacunaTipo`); auto-selección de lote si hay uno solo.

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

**Estado MVP (2026-06-22):** formulario huevos en diálogo centrado desde hub `/operario/cargar` (`CargarHub` + `x-ui.dialog`); solo cantidad obligatoria; `created_at` automático; deep link `/operario/carga/huevos` → redirect con `?form=huevos` (`CargaHuevos` usa vista `livewire._redirect-placeholder`). Evento tiempo real: pendiente (Bloque 6).

### Campos

- Galpón actual (contexto en hero; no se repite en el diálogo).
- Cantidad de huevos.

### Reglas

- Fecha y hora automática.
- No hay selector de fecha/hora para operario.
- Cantidad obligatoria (> 0).
- Debe guardar en unidad huevos.
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

## 9–10. Cargas operario (parcial)

**Estado:** huevos, muertes y vacunación implementados en hub `/operario/cargar`; alimento y combinada pendientes (fases 14–15). Reglas en [`avicore-negocio/references/reglas.md`](../../avicore-negocio/references/reglas.md).

| Pantalla | Fase | Estado |
|----------|------|--------|
| Carga de huevos | 12 | Hecho — diálogo en hub; `RegistrarCargaHuevosAction` |
| Carga de muertes | 13 | Hecho — diálogo en hub; `RegistrarCargaMuertesAction`; deep link `?form=muertes` |
| Carga de vacunación | — | Hecho — diálogo en hub; `RegistrarVacunacionAction`; deep link `?form=vacunacion` |
| Carga de alimento | 14 | Pendiente — kilos; **fuera del hub operario móvil** (encargado/admin o fase posterior) |
| Carga combinada | 15 | **Defer** — dos cargas rápidas en lugar de formulario mixto |

---

## Pantallas planificadas (admin y reportes)

| Pantalla | Fase 12-plan | Fuente al implementar |
|----------|--------------|------------------------|
| Empresas | 7 | Esta guía § nueva + `avicore-negocio/references/permisos.md` |
| Granjas | 8 | Esta guía + CRUD admin |
| Galpones | 9 | Esta guía + `avicore-modelo-datos/references/esquema-bd.md` |
| Lotes | 10 | Esta guía + `avicore-negocio/references/reglas.md` |
| Usuarios | 5 | Esta guía + permisos |
| Auditoría | 16 | Esta guía + tabla `auditorias` (cuando exista migración) |
| Reportes | 19 | `avicore-reportes/references/reportes.md` |
