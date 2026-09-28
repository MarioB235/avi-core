# 05 — Reglas de negocio

## 1. Multiempresa

1. Cada empresa ve solamente sus datos.
2. Toda tabla operativa debe tener empresa_id.
3. El Admin AviCore no accede libremente a datos productivos reales.
4. El modo soporte requiere motivo y auditoría (EMP-06): solo Admin AviCore; `StartSoporteEmpresaAction` crea `soporte_sesiones` (empresa, actor, motivo, inicio, caducidad) y guarda `avicore.soporte_sesion_id` en sesión. Sin soporte activo el admin no ve Resumen ni datos operativos de clientes (`EmpresaScopeService` → `1=0`). Con soporte: banner visible, lectura operativa y **sin** mutaciones productivas (`LotePolicy` + `SoporteEmpresaService::blocksProductionMutations`). Salida manual (`POST avicore/soporte/finalizar`) o logout; caducidad automática al expirar `expires_at`.
5. **Alta de empresa real (EMP-01):** solo Admin AviCore; `CreateEmpresaAction` crea en una transacción la fila `empresas` (nombre, `codigo` único en mayúsculas, estado) y un Dueño inicial con contraseña temporal. La empresa queda operable vacía (sin seed demo).
6. **Cambio de estado de empresa (EMP-02):** solo Admin AviCore; `UpdateEmpresaEstadoAction` exige motivo (mín. 5 caracteres), registra historial en `configuracion.estado_historial` (estado anterior/nuevo, actor, fecha) y, si el nuevo estado no permite login, invalida sesiones de todos los usuarios de esa empresa. No se borra historia operativa. La empresa demo (`DEMO`) está protegida.
7. **Configuración mínima de empresa (EMP-03):** solo Admin AviCore; `UpdateEmpresaConfiguracionAction` actualiza nombre, logo (`logo_path` bajo `empresas/logos/`), `zona_horaria` y unidades en `configuracion` sin tabla nueva. Defaults: `America/Montevideo`, 30 huevos/maple, 12 maples/cajón. `EmpresaHuevosUnidad` consume esas unidades (base para EMP-04).
8. **Onboarding corto (EMP-05):** `EmpresaOnboardingService` evalúa 6 pasos por empresa (activa, administrador dueño/administrativo, granja, galpón, lote/saldo inicial, operario). Se muestra en Inicio admin a Dueño/Administrativo/Encargado mientras haya pendientes; enlaces según permisos (Estructura, Usuarios o `operario/cargar?form=lote`). Sin pasos comerciales.
9. **Salida de soporte (EMP-07):** al finalizar (`POST avicore/soporte/finalizar`), logout o caducidad se limpia `avicore.soporte_sesion_id` y se registra `fin` en `soporte_sesiones.acciones` con `reason` (`manual`, `logout`, `expired`, `replaced`). Destinos de salida permitidos en `config/avicore.php` (`destinos_salida`); destino inválido → `avicore.empresas.index`. Al abrir soporte en otra empresa se cierra la sesión previa (`replaced`) para que el contexto A no contamine B. Acciones auditadas: `inicio`, `consulta_resumen`, `fin`.
10. **Datos personales (EMP-08):** inventario y política operativa en `datos-personales.md` (sin afirmar cumplimiento legal). `DatosPersonales` + `x-ui.documento-label` enmascaran documento en listados de solo lectura (Equipo); gestión de Usuarios y perfil propio muestran documento completo según rol. Exportaciones futuras deben usar el mismo helper.

---

## 2. Usuarios

1. El login se realiza con documento y contraseña.
2. El documento es único dentro de cada empresa (`empresa_id` + `documento`).
3. Admin AviCore (`empresa_id` null) tiene documento único a nivel global.
4. Los usuarios son creados por administrador o perfil autorizado.
5. Todo usuario nuevo puede tener contraseña temporal (`must_change_password`).
6. El primer ingreso exige cambio obligatorio de contraseña antes de usar el sistema.
7. La nueva contraseña debe cumplir política mínima: 8+ caracteres, letras, mayúsculas/minúsculas y números; no puede repetir la actual.
8. Tras 5 intentos fallidos de login por documento e IP en 60 segundos, se bloquea temporalmente el acceso; en login demo (`AVICORE_DEMO_LOGIN=true`) el mensaje va en el campo `demoRole`.
9. Si un documento resuelve más de una cuenta **elegible** (activa, empresa vigente, contraseña correcta), se rechaza el login (ambigüedad). Si solo una cuenta es elegible aunque existan duplicados bloqueados (empresa suspendida/inactiva), se permite el ingreso. No hay selector de empresa en MVP: la contraseña (o la vigencia) desambigua.
10. Usuario inactivo o empresa no activa impiden el acceso (Admin AviCore exceptuado de validación de empresa).
11. Usuario no Admin AviCore sin `empresa_id` asignado no puede iniciar sesión.
12. La recuperación de contraseña en MVP la realiza administrador o encargado autorizado (`ResetUserPasswordAction`): clave temporal en pantalla, `must_change_password=true`, invalidación de sesiones del usuario (`UserSessionService`). No se registra la clave en logs. En login y cambio obligatorio de contraseña, el enlace «¿Olvidaste tu contraseña?» abre un diálogo con contacto de soporte (WhatsApp y/o correo desde `config/avicore.php` / `.env`, URLs validadas en `SupportContactService`); no hay reset automático por correo.
13. Login demo MVP (`AVICORE_DEMO_LOGIN=true`): selector de perfil sin credenciales; cada rol usa un usuario demo fijo (no se muta el rol en BD). Solo activo si existe empresa `DEMO`; en `production` queda deshabilitado siempre. Desactivar antes de go-live. Detalle: [`demo.md`](../../avicore-datos-demo/references/demo.md) § 4.
14. **Autogestión de perfil:** todo usuario autenticado puede editar su nombre y correo, y cambiar su contraseña voluntariamente (`/perfil` o `/operario/perfil`). No puede cambiar documento, rol ni empresa; eso lo hace un administrador.
15. **Superficies técnicas:** rutas web con CSRF; datos de usuario escapados en vistas; logos solo bajo `empresas/logos/` (`EmpresaLogoPathGuard`); cookies seguras forzadas en `production`. Ver [`arquitectura.md`](../../avicore-contexto/references/arquitectura.md) § 5b.

---

## 3. Granjas (EST-01)

1. Alta/edición solo **Administrativo** con `empresa_id` (`GranjaPolicy` + `CreateGranjaAction` / `UpdateGranjaAction`).
2. Datos mínimos: nombre (obligatorio), DICOSE y código interno opcionales, ubicación opcional, estado `activa`.
3. **DICOSE** texto (números y guiones); único por empresa; se normaliza sin espacios.
4. **Código interno** único por empresa si se informa.
5. Validación centralizada en `GranjaValidacion`; errores mapeados al formulario Livewire Estructura.
6. **Jerarquía (EST-03):** al desactivar una granja, sus galpones pasan a `activo = false` (conservan `estado` e historial). Reactivar la granja **no** reactiva galpones automáticamente.

---

## 4. Galpones

1. La carga operativa se realiza por galpón.
2. El operario puede elegir cualquier galpón **disponible para carga** de su empresa (`activo` y `estado = activo`).
3. El sistema recuerda el último galpón seleccionado (`users.ultimo_galpon_id`).
4. Si el galpón recordado deja de estar disponible (inactivo, mantenimiento, etc.), el operario debe elegir otro en el selector de **Inicio**; las pantallas de carga redirigen a `/operario` con el selector abierto (flash `abrirSelectorGalpon` desde `CargarHub`/`CargaHuevos`; `Home` también abre el selector con `?abrir_galpon=1` desde enlaces del hero).
5. Un galpón puede tener uno o varios lotes.
6. Si tiene varios lotes, se muestra aviso informativo.
7. El aviso no bloquea la carga.
8. **Alta/edición admin (EST-02):** `GalponValidacion` — nombre obligatorio, código opcional **único por granja** (`granja_id` + `codigo`), granja de la misma empresa; **alta** solo en granja activa; al pasar a estado distinto de `activo` se sincroniza `activo = false` (bloquea carga, conserva historial).
9. **Disponibilidad operativa (EST-03):** `Galpon::disponibleParaCargaOperativa()` y `GalponValidacion::assertDisponibleParaCarga()` exigen galpón activo, `estado = activo` y **granja activa**; aplica en selector operario, Actions de carga/lote y resúmenes admin (no se evade por ID directo).
10. **Baja y reasignación (EST-09):** no hay borrado físico de granja/galpón/lote (`PreventsHardDelete` + FK `RESTRICT`); la baja es lógica (`activa`/`activo`/estado). `empresa_id` es inmutable en edición. Reasignar galpón a otra granja solo si **no** tiene lotes ni registros operativos; con historial, la granja queda fija en UI y `EstructuraValidacion` rechaza el cambio. Reasignar lote a otro galpón o empresa: no permitido en MVP.
11. **Vacío, mantenimiento y ciclos (EST-08):**
    - Estados `inactivo`, `en_mantenimiento` y `vacio_sanitario` bloquean **toda** carga operativa (sin checklist POES automático; post-MVP).
    - **Sin lote activo** (`activo` / `en_produccion`): no se cargan huevos, muertes ni descarte (`assertLoteActivoParaCargaProductiva`); vacunación ya exige lote elegible.
    - **Carga excepcional:** alimento en galpón disponible **sin** lote activo (logística entre ciclos; no mezcla población).
    - **Nuevo ciclo:** `RegistrarLoteAction` exige sin lotes activos/en producción y `aves_actuales = 0`.
    - **Transición a estado no operativo:** `UpdateGalponAction` exige ciclo cerrado (sin lotes activos ni aves vivas registradas).

---

## 5. Lotes

1. El lote conserva información histórica.
2. El lote puede trasladarse.
3. El lote cerrado no permite cargas normales (`LoteEstado::permiteCargaNormal()`).
4. La reapertura de lote cerrado requiere perfil superior (`UserRole::canReabrirLote` — Dueño o Administrativo) y motivo en `estado_historial`.
5. El tipo de huevo se define en el lote.
6. No se debe usar solamente fecha de nacimiento como identificador.
7. **Edición y transición de lote (EST-06):** `UpdateLoteAction` solo metadatos (SMA, raza, observación); no acepta `estado`. Cambios de estado vía `TransicionarLoteEstadoAction` + motivo obligatorio + historial JSON; matriz en `LoteEstado::transicionesPermitidas()`. UI Estructura: «Cambiar estado» separado del formulario de edición.
8. **Alta de lote (EST-04/05):** solo perfiles con permiso «Crear lote» (`LotePolicy::create` — dueño, administrativo, encargado; **no** operario). Rutas: hub **Cargar** (`/operario/cargar`, admite dos tipos en un registro) y panel **Estructura → Lotes** (un tipo por alta). `LoteValidacion` centraliza SMA opcional, fechas (nacimiento ≤ hoy, ingreso ≤ hoy, nacimiento ≤ ingreso), cantidades enteras (1…`CANTIDAD_MAXIMA`) y tipos válidos; `RegistrarLoteAction` aplica esas reglas aunque la llamada no pase por Livewire, revalida galpón bajo `lockForUpdate`, genera `codigo` único `{codigo_galpon}-{YYYYMMDD}-{B|C}-{secuencia}`, fija `fecha_ingreso` = hoy por defecto, estado `activo` y suma `cantidad_inicial` a `aves_actuales`.

---

## 5. Producción

1. La producción se carga por galpón.
2. Si hay varios lotes en el galpón, la producción se asigna al galpón completo.
3. La unidad principal es el huevo.
4. 1 maple equivale a 30 huevos.
5. 1 caja/cajón equivale a `maples_por_cajon` maples (por defecto 12); huevo base y maple por empresa en `empresas.configuracion.unidades` (EMP-03/04). Pantallas usan `HuevosUnidad::para($empresa)`; reportes futuros deben usar el mismo resolver.
6. **Inicio operario — acumulado:** huevos y muertes acumuladas del galpón seleccionado se calculan desde la `fecha_ingreso` más antigua entre lotes con estado `activo` o `en_produccion` del galpón; registros anteriores a esa ventana no cuentan. Sin lotes activos, no hay ventana de acumulado.
7. **avicore-defer:** objetivo diario por galpón (KPI «Objetivo» en Inicio operario) — pendiente definir meta y umbral por empresa/galpón.
8. Los reportes del MVP muestran huevos; el panel Dueño y operario muestran maples/cajas/sobrantes vía `HuevosUnidad::para($empresa)` (misma lógica que `EmpresaHuevosUnidad`).

---

## 6. Mortalidad

1. Las muertes se cargan por galpón.
2. Si hay varios lotes, la mortalidad se asigna al galpón completo.
3. Las muertes descuentan aves vivas (`aves_actuales` del galpón).
4. No se permite que aves vivas quede negativo; `RegistrarCargaMuertesAction` valida cantidad > 0 y ≤ aves vivas (transacción con `lockForUpdate` en el galpón).
5. Cada apertura del diálogo genera `idempotencia_clave` (UUID); reintento con la misma clave no duplica registro; nueva apertura permite otra carga aunque la cantidad sea igual (CAP-03).
6. Mismo criterio de permisos y empresa que huevos: `GalponPolicy::view`, `empresa_id` y galpón disponible para carga.

---

## 6.25 Descarte de aves (operario MVP)

1. **Descarte** = gallinas **vivas** que se sacan del galpón (no murieron en el piso). Distinto de mortalidad.
2. Tipo de registro `descarte`, campo `descarte_aves`.
3. Descuenta `aves_actuales` con las mismas validaciones que muertes (`RegistrarCargaDescarteAction`: `lockForUpdate`, idempotencia por apertura CAP-04).
4. Anulación restaura aves vivas y excluye el registro de resúmenes (`OperarioGalponResumenService` solo suma registros activos).
5. No confundir con `huevos_descarte` (huevos rotos/sucios) ni con tipo `muertes`.

---

## 6.5 Vacunación (operario MVP)

1. La vacunación se registra **por lote** en el galpón de trabajo del operario.
2. Tipos MVP (`VacunaTipo`): Newcastle, Bronquitis, Gumboro, Encefalomielitis, Pox — catálogo fijo en enum.
3. Solo lotes con estado `activo` o `en_produccion` del galpón seleccionado.
4. El lote debe pertenecer al galpón y a la misma `empresa_id` del usuario.
5. Mismo criterio de permisos y galpón disponible que huevos/muertes: `GalponPolicy::view` vía `RegistrarVacunacionAction`.
6. Persistencia en tabla `vacunaciones` (no en `registros_operativos`).
7. Historial operario incluye vacunaciones activas del usuario, mezcladas con `registros_operativos` por `created_at` descendente (`OperarioHistorialItem`).
8. Observación opcional (máx. 500 caracteres, `VacunacionValidacion`) para detalle útil (vía, lote completo, etc.); sin inventar calendario ni prescripción.
9. Cada apertura del diálogo genera `idempotencia_clave` (UUID); reintento con la misma clave no duplica (CAP-06).
10. Anulación desde Historial con motivo; registros anulados no cuentan en `vacunaciones_hoy`.
11. **avicore-defer:** plan sanitario completo (calendario, dosis, stock vacunas) — fuera del hub operario; ver `plan-desarrollo.md`.

---

## 7. Alimento

1. El MVP no maneja stock de alimento.
2. Solo se registra **alimento entregado** (kg del remito cuando llega el camión), no consumo diario estimado.
3. La unidad es kilos.
4. Se permiten decimales (hasta 2); UI acepta coma decimal (`1250,5` o `8.500,50`).
5. Rango por entrega: mínimo `0,01` kg, máximo `999.999,99` kg (`AlimentoValidacion`).
6. El alimento puede cargarse sin huevos ni muertes y **sin lote activo** (logística entre ciclos).
7. Puede haber varios días sin registro entre entregas; la omisión **no** implica falta de alimentación.
8. Varias entregas el mismo día suman en `alimento_kg_hoy` del resumen operario.
9. Cada apertura del diálogo genera `idempotencia_clave` (UUID); reintento con la misma clave no duplica (CAP-05).

---

## 8. Carga diaria

1. La carga es flexible y en tiempo real.
2. No existe cierre diario automático.
3. El operario no selecciona fecha ni hora.
4. Cada registro usa fecha y hora del momento.
5. Puede haber varias cargas del mismo galpón el mismo día.
6. Para guardar, debe existir al menos un dato cargado.

---

## 8.5 Idempotencia de capturas (CAP-07)

1. Cada intención de guardado (apertura del diálogo en hub **Cargar**) genera UUID con `IdempotenciaCaptura::generarClave()`.
2. Las Actions de captura delegan en `IdempotenciaCaptura::resolverRegistroOperativo` o `resolverVacunacion`: buscan resultado persistido por `empresa_id` + clave (+ `tipo` en registros operativos) antes de insertar; ante carrera concurrente recuperan el registro existente por violación de índice único.
3. Reintento con la **misma** clave (doble toque, timeout de red) devuelve el registro ya guardado — una sola operación persistida.
4. **Nueva** apertura del diálogo genera clave distinta aunque la cantidad sea igual — varias cargas válidas el mismo día siguen permitidas.
5. Clave vacía o solo espacios se trata como ausente (sin idempotencia); la UI operario siempre envía clave.
6. Alcance transversal: huevos, muertes, descarte, alimento (`registros_operativos`) y vacunación (`vacunaciones`). Tests: `OperarioCargaIdempotenciaCap07Test`.

---

## 8.6 Revalidación bajo lock (CAP-08)

1. Toda mutación crítica de captura usa `GalponValidacion::bloquearParaMutacion()` dentro de transacción antes de persistir.
2. Tras el lock se revalida disponibilidad (`revalidarParaCargaBajoLock`); producción (huevos/muertes/descarte) exige lote activo; alimento no.
3. Muertes y descarte comparan `aves_actuales` **después** del lock, no con modelo en memoria.
4. Vacunación bloquea también el lote y rechaza si fue cerrado concurrentemente.
5. Validación previa en Livewire/Action sigue siendo fast-fail; el lock evita carreras con inactivación, mantenimiento o cierre de lote. Tests: `OperarioCargaEstadoBajoLockCap08Test`.

---

## 8.7 Red y respuesta perdida (CAP-09)

1. Los formularios de captura en hub **Cargar** usan `ejecutarEnvioCarga`: validación de negocio conserva el diálogo; fallo de red/servidor muestra aviso sin cerrar ni resetear datos.
2. Estados visibles: **Guardando…** (`wire:loading`), **error de envío** (`cargaEnvioError` + botón «Reintentar»), **confirmado** (snackbar de éxito y cierre solo tras persistir en servidor).
3. El reintento reutiliza la misma `idempotencia_clave` hasta éxito o cierre manual del diálogo (CAP-07).
4. Errores de validación no muestran banner de red. Tests: `OperarioCargaEnvioRedCap09Test`.

---

## 8.8 Cero confirmado y omisión (CAP-10, D03)

1. Huevos, muertes y descarte de aves admiten **confirmación explícita de cero** (`cero_confirmado=true`) sin implicar cierre diario obligatorio.
2. **Omisión** = sin registro activo del tipo hoy; **cero confirmado** = registro con bandera y cantidades en 0; **registrado** = suma del día &gt; 0. Resolución: `CapturaCeroEstado::resolverEstadoDia`.
3. Alimento **excluido**: no registrar alimento no implica falta de alimentación ni admite cero confirmado en MVP.
4. Cero en muertes/descarte **no decrementa** `aves_actuales`; anular ese registro tampoco restaura aves (cantidad 0).
5. UI operario: botón «Confirmar 0 hoy» en formularios de captura; home distingue «Sin registro hoy» vs «0 confirmado hoy»; historial muestra «0 … (confirmado)» en detalle. Tests: `CapturaCeroEstadoTest`, `OperarioCargaCeroConfirmadoCap10Test` (Actions + Livewire huevos/muertes/descarte), `OperarioHistorialTest`.

---

## 8.9 Día operativo y hora de corte (CAP-11)

1. El **día lógico** de capturas usa `configuracion.zona_horaria` de la empresa (default `America/Montevideo`); corte a **medianoche local** de esa zona.
2. Historial (`enFecha`), totales (`delDia`), anulación del operario, pulso admin y gráfico **Postura de la semana** (`AdminResumenService::posturaSemanal`) comparten el mismo rango UTC derivado (`DiaOperativoEmpresa`).
3. `created_at` sigue siendo timestamp real (UTC en BD); el filtro interpreta el instante en la zona de la empresa.
4. Filtro de historial: fecha máxima = día lógico actual de la empresa, no `today()` del servidor.
5. Postura semanal: últimos **7 días lógicos** de la empresa (incluido hoy), no `DATE(created_at)` en calendario del servidor.
6. Tests: `DiaOperativoEmpresaTest`, `OperarioDiaOperativoCap11Test`, `AdminResumenServiceTest` (postura semanal TZ).

---

## 8.10 Perfil y ayuda (CAP-12)

1. Todo usuario autenticado edita **solo** nombre y correo (`UpdateProfileAction`); cambio voluntario de contraseña en pestaña dedicada.
2. Documento, rol y empresa son **solo lectura** en perfil; no hay campos editables ni persistencia de esos atributos desde autogestión.
3. Pestaña **Ayuda** (`?seccion=ayuda`) muestra contacto real de soporte vía `SupportContactService` / `config/avicore.php` (WhatsApp y/o correo).
4. Misma vista compartida en `/operario/perfil` y `/perfil` (admin). Tests: `OperarioPerfilTest`, `OperarioPerfilCap12Test`.

---

## 8.11 Formularios obsoletos (CAP-13)

1. Al abrir un diálogo de captura en hub **Cargar**, se registra contexto (`ultimo_galpon_id` + rol del usuario).
2. Si cambia el galpón seleccionado o el galpón deja de estar disponible mientras hay diálogo abierto, se **cierra y resetea** el formulario y se muestra aviso (snackbar warning).
3. Si cambia el rol o se pierde permiso (p. ej. lote sin `canCreateLote()`), mismo cierre + reset.
4. Antes de persistir, `resolveGalponParaGuardar` / `abortarSiCapturaObsoleta` bloquea guardado con contexto obsoleto (defensa en profundidad).
5. Tests: `OperarioFormulariosObsoletosCap13Test`, regresión `OperarioGalponSelectorTest`.

---

## 8.12 Recorrido móvil operario (CAP-14)

1. Camino feliz verificado de punta a punta: **login** → elegir **galpón** → **capturas** (hub Cargar) → **historial** → **anular** registro propio del día con motivo obligatorio.
2. Shell móvil operario (`operario-mobile`): dock inferior, `viewport-fit=cover`, navegación `wire:navigate` entre Inicio/Cargar/Historial.
3. Formularios de captura con teclado numérico (`inputmode="numeric"`) y feedback snackbar tras guardar o anular.
4. Sin cola offline ni asistencia técnica en el flujo MVP; red perdida cubierta por CAP-09 en cada formulario.
5. Tests: `OperarioRecorridoMovilCap14Test`, regresión `OperarioBottomNavTest` y `OperarioHistorialTest`.

---

## 9. Anulación

1. Se usa “anular”, no eliminar.
2. Las FK de tablas operativas y de estructura avícola usan `ON DELETE RESTRICT` (no cascade): no se puede borrar físicamente un padre que tenga historial o hijos.
3. El registro anulado no cuenta en cálculos.
4. El registro anulado queda en auditoría.
5. El operario solo anula registros propios del día (desde **Historial** → detalle → motivo obligatorio).
6. Toda anulación requiere motivo obligatorio.
7. Muertes y descarte de aves anulados **restauran** `aves_actuales` del galpón.
8. **AUD-02:** segunda anulación del mismo registro se rechaza (policy + validación en Action); los totales del día en Inicio/Resumen usan solo registros `activos`.
9. **AUD-01:** detalle de historial muestra galpón, registrado por, resumen y estado/motivo si está anulado.
10. **AUD-03:** historial supervisor en `/{rol}/historial-operativo` lista cargas de todo el equipo con filtros granja/galpón/operario/tipo/estado/período; detalle solo lectura; no sustituye al historial móvil del operario.

---

## 10. Corrección

1. Toda corrección requiere motivo.
2. Debe guardarse valor anterior y valor nuevo.
3. Encargado o superior puede corregir.
4. Las correcciones deben auditarse.
5. **AUD-04 (D07):** no se borra el registro original; `CorregirRegistroOperativoAction` persiste fila en `correcciones_registro_operativo` (antes/después, actor, fecha efectiva, `registro_operativo_id`) y actualiza el registro activo. Muertes/descarte ajustan `aves_actuales` solo por el delta (no duplican impacto). UI en historial supervisor: detalle → «Corregir registro». Tipos corregibles: huevos, muertes, descarte, alimento. Operario no corrige.
6. **AUD-09:** tipo legado `combinado` — solo lectura/corrección vía anulación; `RegistroOperativoImpactoAves` unifica restauración de saldo al anular (muertes + `descarte_aves`). Sin capturas nuevas (`admiteCapturaNueva()`). Ver `tipos-historicos.md`.
7. **AUD-05:** acciones críticas registran fila en `auditorias` vía `RegistrarAuditoriaAction` (actor, categoría, acción, entidad, motivo si aplica, `occurred_at`). Cubre usuarios/roles (`CreateUserAction`, `UpdateUserAction`, `ResetUserPasswordAction`), empresas (`UpdateEmpresaConfiguracionAction`, estado, soporte), lotes, anulaciones, correcciones y entrada externa de aves (`RegistrarEntradaAvesAction`). Metadata sanitizada: nunca contraseñas ni tokens en logs.
8. **AUD-06:** mutación + auditoría van en la **misma** `DB::transaction` dentro de cada Action; si `RegistrarAuditoriaAction` falla (`AuditoriaCriticaException`), se revierte todo (sin saldo parcial ni estado inconsistente). Patrón verificado en `AuditoriaAtomicidadTest` (anulación, corrección, transición lote).
9. **AUD-07:** consulta de bitácora en `/{rol}/auditoria` — solo lectura (filtros categoría, actor, acción, período; detalle sin editar/borrar). Filtros de fecha usan `AdminFiltroFechasOperativas` + `DiaOperativoEmpresa`. Gate `admin.viewAuditoria`: dueño, administrativo, encargado; soporte AviCore con sesión activa; operario sin acceso. Aislamiento estricto por `empresa_id`.
10. **AUD-08 (D07 retención):** plazos operativos en `config/avicore.php` → `retencion.d07` (default 60 meses; sin afirmar plazos legales). Historial, correcciones, auditoría y `documentos_emitidos` usan `PreventsHardDelete`. `RegistrarDocumentoEmitidoAction` guarda copia inmutable de PDF/Excel para REP-11. Comando operativo `php artisan avicore:retencion-d07`. Purga automática deshabilitada en MVP. Ver `retencion-d07.md`.

---

## 11. Aves vivas

1. Se calculan principalmente por galpón.
2. Se actualizan con muertes, salidas, traslados y ajustes.
2b. **MOV-01:** movimientos persistidos en `movimientos_aves`; `MovimientoAvesEfecto` reconstruye saldo por galpón; `lote_id` identifica población cuando aplica (D01). Sin borrado físico; reversión enlazada. Ver `movimientos-aves.md`.
2c. **MOV-02 (D01):** con varios lotes activos no se estima saldo por lote; traslado/cierre/faena exigen `lote_id` e imputación explícita de muertes/descarte del galpón (`metadata.muertes_imputadas_lote`). `MovimientoAvesConciliacionService` expone hechos vs declaración.
2d. **MOV-03:** alta de lote incrementa `aves_actuales` solo en `RegistrarLoteAction` y registra movimiento `saldo_inicial_lote` sin segundo incremento; entradas externas vía `RegistrarEntradaAvesAction` con idempotencia. Ver `movimientos-aves.md` § MOV-03.
3. El ajuste manual solo lo puede hacer encargado o superior.
4. El ajuste impacta desde ese momento.
5. El ajuste no modifica reportes históricos ya generados.
6. Todo ajuste requiere motivo.

---

## 12. Reportes

1. Los reportes se generan manualmente.
2. PDF incluye logo empresa y marca AviCore.
3. Excel debe ser limpio para análisis.
4. Observaciones del operario no van en PDF principal.
5. Observaciones quedan en detalle operativo.

---

## 13. Tiempo real

1. Dashboard se actualiza con eventos relevantes.
2. Las alertas importantes aparecen sin recargar.
3. No se usa tiempo real para CRUD simples.
4. Los canales deben respetar empresa_id.

---

## 14. Demo

1. Debe existir empresa demo.
2. Debe tener 2 granjas.
3. Debe tener 8 galpones.
4. Debe tener 30 días de datos.
5. Debe incluir escenarios variados.

---

## 15. Coeficientes técnicos de referencia (Uruguay)

Valores de **referencia nacional** (MGAP / DIEA) para gráficos, desvíos y alertas. No son metas fijas por empresa hasta que exista configuración explícita (`avicore-defer`: umbrales por galpón/empresa).

| Coeficiente | Referencia sector Uruguay | Fuente de datos AviCore |
|-------------|---------------------------|-------------------------|
| Postura | ~269–278 huevos por gallina al año | Huevos diarios + aves vivas + edad del lote |
| Conversión alimenticia | ~121–125 g alimento por ave y día | Alimento (kg) + aves vivas |
| Mortalidad | ~1,0%–1,1% (tasa aceptada en encuestas) | Muertes acumuladas vs. aves |
| Ciclo de lote | Postura semana 19–20; descarte semana 86–87 | `fecha_ingreso` del lote + estado |

**Implementación:** recolección operativa en MVP; cálculo automático, curvas y alertas → post-MVP (dashboard fase 17). Detalle mercado y SMA/SNIG: [`mercado-uruguay.md`](../../avicore-contexto/references/mercado-uruguay.md).

---

## 16. Planilla MGAP Anexo Nº 2 (ponedoras — ciclo largo)

Referencia: [`mercado-uruguay.md`](../../avicore-contexto/references/mercado-uruguay.md) §4 · export: [`reportes.md`](../../avicore-reportes/references/reportes.md).

1. **Rubro MVP:** gallinas **ponedoras** (aves de ciclo largo); no usar planilla de pollos parrilleros (engorde).
2. **Registro diario obligatorio:** mortalidad, **descarte de aves** (tipo `descarte`), **huevos aptos** y **huevos de descarte** (rotos/sucios), alimento (kg por entrega) y agua (cuando esté operativo).
3. **Huevos:** `huevos` = aptos/comerciales; `huevos_descarte` = rotos/sucios (puede ser 0). Al menos un total > 0 por registro. Cada apertura del diálogo genera `idempotencia_clave` (UUID); reintento con la misma clave no duplica registro; nueva apertura permite otra carga aunque las cantidades sean iguales (CAP-02).
4. **Pre-faena:** al exportar o cerrar lote hacia faena, incluir historial de las **últimas 9 semanas** de producción (norma DGSG).
5. **Cabecera export:** DICOSE, **lote SMA** (`lotes.codigo_sma`, opcional al crear), lote interno, fecha ingreso/nacimiento, línea genética, población inicial, establecimiento.
6. **Agua:** `avicore-defer` — en granjas con bebederos automáticos el operario **no** registra consumo diario; lectura de medidor o módulo técnico queda para encargado/admin o integración futura.
7. **Certificación VLE:** texto y espacio de firma en PDF; no sustituye al manual BPA firmado.

---

## 17. Panel Dueño — vistas previa (pre-módulo comercial/stock)

1. **Inicio — Stock y demanda** y **Comercial** pueden mostrar KPIs y mapa con **datos de ejemplo** hasta existir módulo de ventas/stock persistido.
2. La UI debe indicar **«Vista previa»** (eyebrow o subtítulo); no presentar cifras ficticias como producción real.
3. Constantes demo en `AdminHomeService` llevan `avicore-defer:`; reemplazar al implementar comercial/stock.
4. **Pulso** y **Resumen** usan datos operativos reales (`RegistroOperativo`, galpones activos); no mezclar con preview comercial.
