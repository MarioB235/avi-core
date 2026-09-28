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
- **Modo demo MVP** (`AVICORE_DEMO_LOGIN=true`): selector de perfil sin credenciales; cada rol entra con su usuario demo fijo (ver `demo.md` § 4). Solo si existe empresa `DEMO`; deshabilitado en `production`.
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
2. Si no → home según rol (`UserRole::homeRouteName()`): operario → `/operario`; dueño → `/dueno`; administrativo → `/administrativo`; encargado → `/encargado`; reparto → `/reparto`; admin AviCore → `/avicore`. `/admin` redirige al prefijo del rol autenticado.

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

Mientras `must_change_password` sea verdadero, el middleware bloquea el acceso a paneles por rol, `/operario` y demás rutas protegidas excepto esta pantalla.

Tras guardar: `must_change_password` pasa a falso y redirección al home del rol (p. ej. `/dueno` o `/operario`).

---

## 3.1 Pantalla: Inicio admin (MVP)

**Estado MVP (2026-07-16):** shell **visual** igual al operario (sidebar `lg+`, bottom nav móvil, home-nav + sheet), con **tabs y contenido solo de gestión** (sin Campo/carga). **Persona de referencia:** Dueño (`permisos.md` §10). Detalle: `patrones-web-admin.md`.

### Objetivo

Landing post-login para roles con panel administrativo (Dueño, Administrativo, Encargado, Admin AviCore): contexto de empresa, KPIs de gestión, accesos a módulos de administración y guía de configuración.

### Usuarios

- Dueño.
- Administrativo.
- Encargado.
- Admin AviCore.

### Elementos

- Layout: `components/layouts/admin.blade.php` reutiliza clases `avicore-operario-*`; nav `AdminNav` según rol (Dueño: Inicio · Resumen · Equipo · Comercial); menú cuenta `x-ui.user-menu`; PWA (`x-ui.pwa-meta` + banner instalar si `AVICORE_PWA_INSTALL_PROMPT=true`).
- Hero: saludo horario + subtítulo `{empresa · rol}.`
- **Primeros pasos (onboarding):** checklist `x-ui.setup-checklist` mientras falte algún paso operativo (granja, galpón, lote, operario, etc.); oculta al completar. Ver `EmpresaOnboardingService`.
- **Tu empresa:** panorama estructural — granjas y galpones activos (2 KPIs).
- **Tu empresa hoy (pulso):** estado del día (huevos vs ayer, alertas mortalidad, galpones sin carga), KPIs huevos/muertes hoy con maples/cajas, enlace a Resumen.
- **Stock y demanda (vista previa):** reserva en cámara, demanda, salida hoy y disponible estimado — **datos ficticios** hasta módulo comercial/stock.
- Empty state si no hay granjas ni galpones cargados.
- **No incluye** paneles/tiles de carga operario (`kpi-panel`, `carga-tile`, chip de galpón) ni accesos a Cargar/Historial.

### Navegación (MVP)

Cada rol con panel usa su prefijo; las vistas Livewire se comparten hasta tener pantallas propias por rol.

- **Dueño:** `/dueno`, `/dueno/resumen`, `/dueno/equipo`, `/dueno/comercial` (sin Estructura ni Usuarios)
- **Administrativo:** `/administrativo` (+ resumen, estructura, usuarios)
- **Encargado:** `/encargado` (+ resumen, estructura, usuarios limitado)
- **Admin AviCore:** `/avicore` (+ empresas, usuarios; tab Resumen solo con sesión de soporte activa)
- **Reparto:** `/reparto` (stub)

### Comportamiento

Tras login exitoso (sin cambio de contraseña pendiente), cada rol llega a su prefijo (p. ej. Dueño → `/dueno`).

---

## 3.1.1 Pantalla: Equipo (Dueño)

**Estado MVP (2026-08-22):** `/dueno/equipo` — lista plana con chips de filtro (Todos, Campo, Supervisión, Oficina); **solo lectura** (sin CRUD).

### Elementos

- Resumen en una línea: total de personas activas.
- Chips de filtro con contador (`avicore-operario-filter-chip`).
- Lista continua (`avicore-team-list`): nombre, documento enmascarado (`x-ui.documento-label`), correo y badge de rol; **sin avatar** (EMP-08).

### Usuarios autorizados

- Dueño (`canViewEquipo`).

---

## 3.1.2 Pantalla: Comercial (Dueño, preview)

**Estado MVP (2026-08-22):** `/dueno/comercial` — vista previa con KPIs de ejemplo + **mapa interactivo** (Leaflet/OSM) y lista de clientes demo con **última compra** (fecha y cantidad de huevos). Módulo real post-MVP (`producto.md` excluye ventas en MVP).

### Elementos

- KPIs orientativos: clientes, última venta, pedido de mañana, huevos reservados.
- Mapa con pins verdes; al tocar un pin, card de detalle debajo (sin lista ni tooltip flotante).
- Card: nombre, zona, última compra (fecha) y cantidad de huevos.

### Usuarios autorizados

- Dueño (`canViewComercial`).

---

## 3.1.5 Pantalla: Empresas (Admin AviCore)

**Estado MVP (2026-09-26):** `/avicore/empresas` — listado con búsqueda y alta en diálogo; crea empresa + Dueño inicial en transacción (`CreateEmpresaAction`).

### Objetivo

Dar de alta empresas cliente reales sin depender del seed demo: nombre, identificador (`codigo`), estado inicial y administrador Dueño con contraseña temporal.

### Usuarios autorizados

- Solo **Admin AviCore** (`EmpresaPolicy`).

### Campos (alta)

- Nombre de la empresa (obligatorio).
- Identificador / código (obligatorio, único; se guarda en mayúsculas).
- Estado inicial (`activa`, `suspendida`, `inactiva`; por defecto `activa`).
- Dueño inicial: nombre, documento y correo opcional.

### Acciones

- Nueva empresa → transacción empresa + Dueño (`must_change_password = true`); diálogo muestra la clave una sola vez.
- Listado con búsqueda por nombre o identificador.
- **Cambiar estado** (EMP-02): diálogo con nuevo estado y motivo obligatorio; registra actor/fecha en `empresas.configuracion.estado_historial[]`; al suspender/inactivar invalida sesiones de la empresa (`UserSessionService::invalidateAllForEmpresa`) y bloquea acceso vía SEG-02. La empresa demo (`codigo` DEMO) no se puede modificar.
- **Configurar** (EMP-03): nombre visible, zona horaria (`configuracion.zona_horaria`), unidades (`huevos_por_maple`, `maples_por_cajon`) y logo (subida a `empresas/logos/` vía `EmpresaLogoStorageService` + `EmpresaLogoPathGuard`).
- **Soporte** (EMP-06/07): diálogo con motivo (mín. 10 caracteres) → `StartSoporteEmpresaAction` → redirect a Resumen (`/avicore/resumen`). Banner amarillo (`x-admin.support-banner`) con «Salir de soporte» (`POST avicore/soporte/finalizar`; destino validado en config, default Empresas). Logout también cierra soporte. Bitácora en `soporte_sesiones.acciones` (`inicio`, `consulta_resumen`, `fin`). Cambiar de empresa cliente cierra la sesión anterior sin mezclar contexto.

### Notas

- La empresa queda vacía (sin granjas/galpones); el Dueño puede operar tras login y configurar estructura (Administrativo) o móvil según D02.
- Unidades por empresa (EMP-04): Inicio admin, Resumen y operario calculan maples/cajas/sobrantes con `HuevosUnidad::para($empresa)`; exportaciones futuras deben reutilizar el mismo resolver.

---

## 3.2 Pantalla: Usuarios (admin)

**Estado MVP (2026-07-16):** implementado en `/admin/usuarios` — listado con búsqueda/filtros, alta/edición en diálogo, reset de contraseña temporal (mostrada una vez) y activar/desactivar.

### Objetivo

Gestionar el equipo de la empresa: crear usuarios con rol, editar datos, resetear contraseña temporal y desactivar cuentas.

### Usuarios autorizados

| Acción | Admin AviCore | Dueño | Administrativo | Encargado | Operario |
|--------|---------------|-------|----------------|-----------|----------|
| Ver listado | Sí (todas las empresas) | No (módulo Equipo) | Sí (su empresa) | Sí (su empresa) | No |
| Crear / editar / activar-desactivar | Sí | No | Sí | No | No |
| Reset contraseña | Sí | No | Sí | Sí | No |

Roles asignables: Administrativo (administrativo→operario/reparto); Dueño solo asigna vía Admin AviCore en alta de empresa; Admin AviCore (todos, con selector de empresa salvo `admin_avicore`).

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

## 3.3 Pantalla: Estructura (admin)

**Estado MVP (2026-09-26):** implementado en `/admin/estructura` — pestañas Granjas · Galpones · Lotes; granjas con DICOSE/código únicos por empresa y `GranjaValidacion` (EST-01); galpones con código único por granja, estados operativos y `GalponValidacion` (EST-02); desactivar granja bloquea carga de sus galpones (EST-03); alta/edición con diálogos `x-ui.dialog`; edición de lote separa metadatos (`UpdateLoteAction`) de transición de estado con motivo (`TransicionarLoteEstadoAction`, EST-06); listados con búsqueda, filtros en URL y empty states (EST-07).

### Usuarios autorizados

| Acción | Dueño | Administrativo | Encargado |
|--------|-------|----------------|-----------|
| Ver listados | No (sin tab) | Sí | Sí |
| Crear/editar granja y galpón | No | Sí | No |
| Crear/editar lote | No* | Sí | Sí |

\* Dueño: alta de lote solo vía `/operario` (hub Cargar), no panel Estructura.

Operario y Admin AviCore sin `empresa_id` no acceden a esta pantalla.

### Campos principales

- **Granja:** nombre, DICOSE (único por empresa), código interno, ubicación, activa.
- **Galpón:** granja, nombre, código, capacidad, estado operativo, activo, observación.
- **Lote:** galpón, tipo Blanca/Colorada, cantidad, fecha nacimiento, Nº SMA (opcional); edición: SMA, línea/raza, estado, observación.

### Comportamiento

Multiempresa por `empresa_id`. Alta de lote reutiliza `RegistrarLoteAction` + `LoteValidacion` (EST-04); panel Lotes con SMA opcional y errores en formulario. Nav admin: tab **Estructura**.

**Listados (EST-07):** búsqueda de texto y filtros persistidos en URL (`#[Url]`):

| Sección | Filtros |
|---------|---------|
| Granjas | Activa / inactiva |
| Galpones | Granja, estado operativo |
| Lotes | Granja, galpón, estado del lote, tipo de ave |

Paginación por sección. Sin resultados: mensaje contextual; si hay filtros activos, botón **Limpiar filtros**. Galpones y lotes muestran badge o hint cuando la granja está inactiva o el galpón no admite carga operativa. Filtro de granja ajena no filtra registros de otra empresa.

**Baja y reasignación (EST-09):** no hay botón eliminar; desactivar granja/galpón conserva historial. Editar galpón: si ya tiene lotes o cargas, la granja se muestra en lectura (no reasignable). `empresa_id` no se edita en estructura.

**Ficha de lectura (EST-10):** botón **Ver ficha** en galpones y lotes (Encargado y Administrativo). Diálogo readonly con ubicación (granja/DICOSE), saldo vivo, producción del día a nivel galpón, listado de lotes del galpón e historial de estados del lote. Métricas por lote solo si hay un único lote activo/en producción; con varios lotes activos se muestra aviso de que la producción es por galpón. Población inicial del lote y saldo del galpón siempre diferenciados en texto.

---

## 3.4 Pantalla: Resumen (admin)

**Estado MVP (2026-08-15):** implementado en `/admin/resumen` — KPIs del día agregados y por galpón; filtros por granja y galpón (`x-ui.select`); alertas de mortalidad acumulada (> 1,1% referencia).

### Objetivo

Vista operativa para Dueño, Administrativo y Encargado: seguir producción del día sin entrar al módulo operario.

### Usuarios

- Dueño, Administrativo, Encargado (`canViewResumen`).

### Elementos

- Hero `x-admin.page-hero`.
- Filtro granja y galpón (`x-ui.select`; al cambiar granja se limpia galpón).
- KPIs globales: huevos hoy, **descarte hoy**, muertes hoy, **alimento kg hoy**, aves actuales, alertas mortalidad.
- Gráfico de línea «Postura de la semana» (`x-ui.line-chart`, huevos aptos últimos **7 días lógicos** de la empresa — CAP-11, no calendario UTC del servidor).
- Comparación por galpón: **tabla compacta** en `md+` (huevos, descarte, muertes, alimento kg, aves, mortalidad); **cards** en móvil (`< md`).
- Servicio `AdminResumenService` (reutiliza `OperarioGalponResumenService`).

### Comportamiento

- Sin galpones activos: empty state con enlace implícito a Estructura.
- Postura semanal agrupa con `DiaOperativoEmpresa` + `delDia` (misma ventana que KPIs y pulso).
- Operario redirigido fuera de `/admin/*`.

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

**Cargar:** Huevos, Muertes, **Descarte de aves**, Vacunación, **Alimento** (entrega del camión) y (si el rol puede crear lote) **Nuevo lote** — grilla 2 columnas; con permiso de lote, tile ancho «Nuevo lote». Diálogos: huevos aptos + descarte; descarte de aves vivas; alimento en kg del remito; vacunación con `x-ui.select`; nuevo lote: galpón, **Nº lote SMA** (opcional), tipos Blanca/Colorada, cantidad, fecha nacimiento.

**Historial:** listado completo del operario (cargas + vacunaciones), filtro por fecha con `x-ui.date-picker` (sin `input type="date"` nativo; error de validación visible bajo el trigger), paginación. Cada ítem abre **detalle** (tipo, galpón, fecha/hora, resumen). Registros **anulados** visibles con badge; el operario puede **anular** registros propios del día con motivo obligatorio (`x-ui.textarea` en el diálogo; muertes/descarte restauran `aves_actuales`; vacunación vía `AnularVacunacionAction`). Dueño/administrativo/encargado pueden anular registros ajenos del día vía policy (sin UI en Historial MVP).

**Compartido:** logo, menú cuenta (avatar), dock inferior (Inicio · Cargar · Historial).

### Perfil de cuenta (MVP)

**Estado MVP (2026-08-15):** `/operario/perfil` (layout operario) y `/perfil` (layout admin) comparten **misma vista** (`x-operario.perfil-hero` + `avicore-operario-home-sheet`); el admin usa `avicore-home-nav` en el header (sin badge toolbar legacy). Pestañas con `wire:navigate` + query `?seccion=password|ayuda` (sin morph parcial). Partials `tabs`, `datos-form`, `password-form`, `ayuda-panel` (`x-support.contact-links`). Menú cuenta: **Editar datos** / **Cambiar contraseña** (misma navegación). `UpdateProfileAction` y `ChangePasswordAction` exigen `UserPolicy::updateProfile`.

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
- **CAP-01:** `syncGalponSelector` en cada hydrate Livewire; chip muestra granja bajo el nombre del galpón; al guardar carga se revalida `ultimo_galpon_id`.
- **CAP-13:** cambio de galpón o rol con diálogo abierto cierra y resetea el formulario (snackbar warning); evita datos cruzados y falsa confirmación.
- **CAP-09:** fallo de red mantiene diálogo y datos; banner «No pudimos confirmar…» + botón «Reintentar» (misma `idempotencia_clave`); éxito solo tras persistir (snackbar + cierre).
- **CAP-10:** huevos/muertes/descarte admiten «Confirmar 0 hoy» (`cero_confirmado`); home distingue «Sin registro hoy» vs «0 confirmado hoy»; alimento sin cero confirmado.
- **CAP-11:** día lógico por `zona_horaria` de empresa (medianoche local); historial, totales, anulación y postura semanal admin usan `DiaOperativoEmpresa`.
- **CAP-12:** perfil con datos propios + contraseña + pestaña Ayuda (contacto soporte); documento/rol/empresa solo lectura.
- **CAP-13:** formularios de captura invalidados al cambiar galpón/rol/disponibilidad; `ManagesCapturaContexto` en `CargarHub`.
- **CAP-14:** recorrido móvil login → galpón → capturas → historial → anular; verificado en `OperarioRecorridoMovilCap14Test`.

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
- **CAP-02:** formulario con teclado numérico (`inputmode="numeric"`), bloque «Llevás hoy…» (acumulado del galpón), confirmación con desglose maples antes de guardar, `idempotencia_clave` por intención (reintento no duplica; dos cargas nuevas suman en Inicio).
- Requiere galpón disponible; sin galpón o galpón no disponible → redirección a `/operario` con selector abierto (no hay ruta `/operario/galpon`).
- `RegistrarCargaHuevosAction` valida empresa, permiso (`GalponPolicy`) y estado del galpón.
- Debe emitir evento en tiempo real.

---

## 8. Pantalla: Carga de muertes

**Estado MVP (2026-09-26, CAP-03):** diálogo desde hub con saldo vivo, muertes hoy, confirmación con saldo restante e `idempotencia_clave` por apertura; deep link `?form=muertes`. Evento tiempo real: pendiente (Bloque 6).

### Campos

- Galpón actual (contexto en hero; saldo y acumulado en el diálogo).
- Cantidad de muertes (`wire:model.live`).

### Reglas

- Fecha y hora automática.
- Cantidad obligatoria (> 0) y no mayor que aves vivas del galpón; error conserva valor y diálogo abierto.
- Requiere galpón disponible; sin galpón o galpón no disponible → redirección a `/operario` con selector abierto.
- `RegistrarCargaMuertesAction` valida empresa, permiso (`GalponPolicy`), estado del galpón y stock de aves (bloqueo pesimista en transacción + idempotencia).
- Debe emitir evento en tiempo real.

---

## 8.5 Pantalla: Carga de vacunación

**Estado MVP (2026-09-26, CAP-06):** diálogo con lote/vacuna obligatorios, observación opcional, confirmación, contador del día, idempotencia y aviso «sin calendario ni receta»; deep link `?form=vacunacion`. Evento tiempo real: pendiente (Bloque 6).

### Campos

- Galpón actual (contexto en hero; vacunaciones hoy en el diálogo).
- Lote a vacunar (`wire:model.live`, solo activos/en producción del galpón).
- Tipo de vacuna (`VacunaTipo`, `wire:model.live`).
- Observación opcional (detalle útil, máx. 500 caracteres).

### Reglas

- Fecha y hora automática (`created_at`).
- Requiere galpón disponible; sin galpón o galpón no disponible → selector abierto.
- `RegistrarVacunacionAction` valida empresa, permiso, lote↔galpón y estado del lote; error conserva diálogo.
- Sin lotes activos: mensaje en el diálogo (no se muestra formulario).
- Varias vacunaciones el mismo día suman en `vacunaciones_hoy`; anulación excluye del contador.
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

**Estado MVP (2026-09-26, CAP-05):** diálogo «Entrega de alimento» con coma decimal, acumulado del día, confirmación, límites visibles e idempotencia; deep link `?form=alimento`.

### Campos

- Galpón actual (contexto en hero; entregado hoy en el diálogo).
- Kilogramos entregados (`wire:model.live`, texto con coma decimal).

### Reglas

- Fecha y hora automática (`created_at`).
- Cantidad obligatoria (> 0 kg); decimales con coma; máximo 999.999,99 kg por entrega.
- **No** es consumo diario: registrar solo cuando llega el camión; días sin carga son válidos.
- Varias entregas el mismo día suman en resumen (`alimento_kg_hoy`).
- `RegistrarCargaAlimentoAction` — tipo `alimento`, permisos/galpón disponible; sin exigir lote activo.
- Historial: resumen «X kg entregados».

---

## 8.75 Pantalla: Descarte de aves

**Estado MVP (2026-09-26, CAP-04):** diálogo «Descarte de aves» desde hub con saldo vivo, descarte hoy, confirmación, etiqueta diferenciada («no es mortalidad ni huevo descartado») e idempotencia; tile aparte de Muertes; deep link `?form=descarte`.

### Campos

- Galpón actual (contexto en hero; saldo y acumulado en el diálogo).
- Cantidad de aves descartadas (`wire:model.live`).

### Reglas

- Distinto de **muertes** (aves que murieron en el piso) y de **huevos descarte** (rotos/sucios).
- Descuenta `aves_actuales` igual que muertes; error conserva valor y diálogo abierto.
- `RegistrarCargaDescarteAction` — tipo `descarte`, campo `descarte_aves`, `lockForUpdate` + idempotencia.
- Anulación desde Historial restaura aves y deja de contar en resúmenes.

---

## 9–10. Cargas operario (parcial)

**Estado:** huevos (aptos/descarte), muertes, **descarte de aves**, vacunación y **alimento** implementados en hub `/operario/cargar`. Reglas en [`avicore-negocio/references/reglas.md`](../../avicore-negocio/references/reglas.md).

| Pantalla | Fase | Estado |
|----------|------|--------|
| Carga de huevos | 12 | Hecho — aptos + descarte; `RegistrarCargaHuevosAction` |
| Carga de muertes | 13 | Hecho — diálogo en hub; `RegistrarCargaMuertesAction`; deep link `?form=muertes` |
| Descarte de aves | — | Hecho — `RegistrarCargaDescarteAction`; deep link `?form=descarte` |
| Carga de vacunación | — | Hecho — diálogo en hub; `RegistrarVacunacionAction`; deep link `?form=vacunacion` |
| Carga de alimento | 14 | Hecho — entrega camión (kg); `RegistrarCargaAlimentoAction`; deep link `?form=alimento` |

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
