# 02 — Pantallas y flujos

> **Gobernanza incremental:** solo se detalla aquí lo que tiene ruta/UI en el repo. Pantallas planificadas: una línea + enlace a [`12-plan-de-desarrollo.md`](12-plan-de-desarrollo.md). Al implementar, expandir la sección correspondiente en el mismo PR.

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

- Documento.
- Contraseña.
- Recordarme (opcional).
- Perfil demo (opcional; solo `APP_ENV=local` + `AVICORE_DEMO_LOGIN=true` — ver presentación).

### Acciones

- Iniciar sesión.
- Cerrar sesión (`POST /logout`).

### Presentación (MVP implementado)

- Layout público en **split** (≥1024px): panel de marca a la izquierda (`auth-brand-panel`: logo `hero` con animación de entrada `entrance` y copy en columna alineada), tarjeta de login a la derecha.
- En **móvil** (<1024px): fondo `login-background.jpg` (granja al atardecer), logo apilado centrado sobre la foto (`entrance` — órbita del isotipo alrededor del wordmark) y tarjeta blanca anclada abajo con esquinas superiores redondeadas (bottom sheet).
- Inputs con icono Lucide (`id-card`, `lock-keyhole`) y **toggle** para mostrar/ocultar contraseña (un solo control visible).
- Checkbox «Recordarme» con foco visible.
- **Modo demo (solo `APP_ENV=local` + `AVICORE_DEMO_LOGIN=true`):** credencial única `000000000` / `Avicore2026!` y selector de perfil; autentica al usuario seedeado del rol elegido (`DemoLoginService`). No visible en producción.
- Recuperación de contraseña: enlace **«¿Olvidaste tu contraseña?»** abre un diálogo con contacto de soporte (WhatsApp y correo configurables en `config/avicore.php` / `.env`); sin flujo automático de reset en MVP (ver regla de negocio en `05`).

### Validaciones

- Documento obligatorio (máx. 50 caracteres).
- Contraseña obligatoria.
- Usuario activo.
- Empresa activa (estado `activa`; no aplica a Admin AviCore).
- Usuario no Admin AviCore sin empresa asignada no puede iniciar sesión.
- Credenciales válidas.
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

**Estado:** planificado — fase 17 en [`12-plan-de-desarrollo.md`](12-plan-de-desarrollo.md) §2; tiempo real asociado en Bloque 6. Tarjetas, filtros y actualización en vivo se documentarán al implementar `Livewire/Dashboard/`.

---

## 5. Pantalla: Vista móvil del operario

**Estado MVP (2026-06-28):** implementado en `/operario` — shell móvil con **barra inferior integrada** (3 pestañas: Inicio · Cargar · Historial; inactivos con círculo soft verde; ítem activo con círculo verde sobresaliente; Historial usa icono `calendar`), **heroes compactos** con degradado suave, **panel de estado del galpón** (KPIs por galpón seleccionado: aves, huevos/muertes hoy, acumulado desde ingreso de lotes activos, lista de lotes con edad; galpón solo en chip del hero; sin enlace duplicado a Historial). Header hero fijo: grilla logo/usuario + línea ogee (`avicore-home-nav`); avatar abre **menú cuenta** (`x-operario.user-menu`: dropdown Perfil + Cerrar sesión). Nav: `OperarioNav`; layout hero: `operarioIsHeroPage` (Inicio + Cargar + Historial).

### Navegación móvil (3 pestañas)

| Pestaña | Ruta | Contenido |
|---------|------|-----------|
| Inicio | `/operario` | Hero compacto, saludo, selector galpón, resumen KPI (aves, huevos/muertes hoy, acumulado, lotes activos) |
| Cargar | `/operario/cargar` | Hero degradado + hoja con tipos; formulario huevos en diálogo centrado (`x-ui.dialog` + `wire:model`); deep link `?form=huevos` o `/operario/carga/huevos`; chip galpón solo lectura (vacío → enlace `?abrir_galpon=1` en Inicio) |
| Historial | `/operario/historial` | Hero degradado; listado de **todos** los registros del operario (todos los tipos), orden descendente; filtro opcional `?fecha=` (validado: `date`, no futura; error visible); paginación 20; ítems sin icono; chip galpón solo lectura |

En **Inicio**, el header fijo muestra logo + usuario (rol con `label()`); el avatar abre menú cuenta (perfil y logout). El galpón se elige con chip desplegable en el hero («Estado de hoy del galpón.»). La hoja blanca muestra KPIs y lotes activos del galpón seleccionado (`OperarioGalponResumenService`; edad de lote vía `edadSemanas()`), sin repetir el nombre del galpón ni enlace a Historial. Sin galpón: mensaje para elegir uno. Cargar e Historial por pestañas del dock.

### Objetivo

Permitir carga rápida desde celular.

### Usuarios

- Operario.
- Encargado, si necesita cargar.

### Elementos (por pestaña)

**Inicio:** saludo, chip galpón (selector), KPIs del galpón (aves, huevos/muertes hoy, acumulado), lista de lotes activos.

**Cargar:** tipos de carga (Huevos activo; Muertes/Alimento/Combinada próximamente), alerta sin galpón, diálogo huevos.

**Historial:** listado completo del operario, filtro por fecha, paginación.

**Compartido:** logo, menú cuenta (avatar), dock inferior (Inicio · Cargar · Historial).

### Flujo

```text
Seleccionar tipo de carga en hub → diálogo centrado con formulario (solo cantidad) → guardar → snackbar → permanece en hub Cargar
```

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
- Si el galpón recordado deja de estar disponible, la carga redirige a **Inicio** con el selector abierto (`session` flash `abrirSelectorGalpon` desde hub y deep link; `Home` también acepta `?abrir_galpon=1` desde enlaces del hero).
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

## 8–10. Cargas operario (pendientes)

**Estado:** planificado — fases 13–15 en [`avicore-contexto/references/plan-desarrollo.md`](../../avicore-contexto/references/plan-desarrollo.md) §2. Reglas de negocio en [`avicore-negocio/references/reglas.md`](../../avicore-negocio/references/reglas.md). Placeholders en hub `/operario/cargar`.

| Pantalla | Fase | Nota breve |
|----------|------|------------|
| Carga de muertes | 13 | Cantidad obligatoria; descuenta aves vivas |
| Carga de alimento | 14 | Kilos con decimales; sin stock en MVP |
| Carga combinada | 15 | Al menos un dato (huevos, muertes o alimento) |

Detalle de campos y validaciones se añadirá aquí al implementar cada pantalla.

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

