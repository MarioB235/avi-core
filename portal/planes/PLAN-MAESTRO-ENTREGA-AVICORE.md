# AviCore — Plan maestro de entrega y ejecución autónoma

Versión 1.0 · 2026-09-26 · Estado: preparado para ejecución; producto aún no certificado.

Diagnóstico: [DIAGNOSTICO-Y-MEJORAS-AVICORE.md](DIAGNOSTICO-Y-MEJORAS-AVICORE.md).

## 1. Mandato y alcance

Entregar una aplicación operativa para avícolas de gallinas ponedoras: capturar en campo, supervisar, conciliar aves, consultar historia y exportar información confiable por empresa. La experiencia debe ser clara para quienes hoy trabajan con papel o planillas.

**Decisión expresa del usuario: operación avícola completa en primera entrega; comercial y reparto en segunda etapa.** No desarrollar ventas, facturación, logística, packing industrial, MOBA, RFID, IA ni app nativa en v1. No transformar entregas de alimento en inventario avanzado.

Este plan establece el alcance de aceptación propuesto y las decisiones pendientes. Nuevos hallazgos se incorporan como tareas justificadas, sin esconderlos ni ampliar el negocio unilateralmente. El plan no sustituye las referencias canónicas: al implementar cambios de contrato, actualizar la referencia de su skill dueño.

Los casilleros empiezan pendientes aunque exista código: significan **revalidar y completar para entrega**, no reimplementar todo. Reutilizar lo que funciona. El diagnóstico distingue capacidades implementadas, previews y pendientes.

Raíz: `C:/Proyectos/proyectos-2026/Proyectos de prueba/avi-core`. Las rutas siguientes son relativas a ella; resolverlas a rutas absolutas y comprobar existencia antes de editar. Las ubicaciones nuevas son propuestas, no archivos existentes.

## 2. Qué significa 100% completo

Todas las tareas obligatorias de v1 verificadas, decisiones bloqueantes resueltas y puertas de entrega aceptadas. No es porcentaje de archivos ni valoración subjetiva de calidad.

- Estados: PENDIENTE, EN_CURSO, BLOQUEADA, VERIFICADA, NO_APLICA_APROBADO.
- `[x]` solo para VERIFICADA. Bloqueada sigue `[ ]`.
- NO_APLICA_APROBADO exige motivo y decisión del responsable; no significa realizado.
- Progreso técnico = verificadas / obligatorias aplicables. Informar exclusiones aparte.
- Las puertas GATE consolidan aceptación y no se suman otra vez al denominador de tareas.
- Comercial/reparto no entran en el denominador v1.
- El agente no puede autofirmar aceptación del cliente, validación de campo ni autorización productiva.
- No declarar entrega con P0/P1 abiertos, pruebas obligatorias fallidas o restauración no comprobada.

Definición de terminado: resultado observable, criterios cumplidos, permisos/empresa/validación/error comprobados, tests relevantes ejecutados, diff revisado, contrato actualizado cuando corresponda y evidencia con fecha/revisión/comando. Test bloqueado por entorno no equivale a aprobado.

## 3. Protocolo del orquestador

Entrada única: `/avicore-architect-direct`. Ampliar el comando y skills existentes, no crear otro orquestador. Este documento es cola de trabajo y registro de aceptación, no copia del esquema implementado.

1. Leer AGENTS, contrato portal, comando, plan y checkpoint; inspeccionar rama y cambios locales.
2. Elegir tarea de mayor prioridad con dependencias resueltas; declarar ID, objetivo y prueba de cierre.
3. Leer skills/referencias pertinentes y flujo real. Comprobar lo existente antes de reemplazarlo.
4. Investigar dudas concretas en fuentes oficiales/versionadas; registrar pregunta, fuente, fecha, conclusión e impacto. Negocio del cliente requiere evidencia real.
5. Implementar una unidad coherente con validaciones, permisos y tests; no agrupar dominios ajenos.
6. Verificar y corregir fallos propios; comparar contra baseline para identificar problemas preexistentes.
7. Auditar diff, actualizar contrato y evidencia; marcar solo si cumple definición de terminado.
8. Continuar con tareas desbloqueadas. Al terminar sesión, guardar checkpoint suficiente para retomar sin historial del chat.

Autonomía al invocar el plan: lectura, investigación pública, implementación local acordada, tests en base exclusiva, build, documentación y correcciones relacionadas. No autoriza commit/push/PR, producción, comunicaciones externas, borrado de datos ni contratación de servicios. Respetar permisos del entorno.

Bloqueos: registrar ID, decisión faltante, alternativas, recomendación e impacto; seguir tareas independientes. El silencio no aprueba una decisión. Si no queda trabajo seguro desbloqueado, informar el bloqueo concreto.

Worktree: capturar baseline y conservar cambios locales. Leer o crear un archivo nuevo sin colisión no exige detener toda la tarea. Resolver superposición antes de editar; no hacer stash/reset/pull a ciegas.

### Checkpoint persistente

| Campo | Valor inicial |
|---|---|
| Revisión | 2026-09-26 |
| Base | b2623ef + cambios locales en `portal/planes/` |
| Rama | feature/admin-resumen-galpon-select |
| Siguiente ID | ORQ-05 (o BAS-02 si se prioriza baseline verde) |
| En curso | Ninguno |
| Checkpoint persistente | `portal/planes/CHECKPOINT.md` |
| Tests base | 412: 411 aprobados, 1 fallido; 1.753 aserciones |
| Fallo base | AdminUserMenuTest::test_admin_home_renders_shared_user_menu_in_sidebar_and_home_nav |
| Build base | Exit 0 |
| Alcance | Operación v1; comercial/reparto etapa 2 |
| Decisiones pendientes | D01–D07 antes de sus tareas dependientes |

### Evidencia obligatoria por cierre

```text
ID y estado:
Fecha, revisión y estado del diff:
Archivos afectados:
Resultado observable:
Pruebas/comandos, exit code y resumen:
Evidencia visual cuando corresponda:
Referencia canónica actualizada o no aplica justificado:
Riesgos/bloqueos:
Siguiente ID:
```

Agregar cierres a la sección final. Evidencias extensas pueden ir en `portal/planes/evidencias/` (futuro), sin secretos, datos personales reales ni volcados completos de sesiones autenticadas.

## 4. Decisiones pendientes

| ID | Decisión | Propuesta mínima | Responsable / bloqueo |
|---|---|---|---|
| D01 | Saldo por lote con varios lotes en galpón | Captura por galpón; movimiento requiere identificación/conciliación; nunca reparto silencioso de muertes | Dueño/encargado; MOV-02 y cierre parcial |
| D02 | Facultades Dueño/Administrativo | **Cerrada (SEG-07):** Dueño = supervisión + móvil; Administrativo = estructura + usuarios; roles complementarios | `permisos.md` §2 D02; tests matriz |
| D03 | Cero y carga esperada | Diferenciar sin registro de cero; no introducir cierre obligatorio | Encargado; alertas nuevas |
| D04 | Actualización entre dispositivos | Refresco visible y periódico acotado; WebSockets solo si exige inmediatez | Producto; ACT-02/03 |
| D05 | Reporte normativo exacto | Reporte interno primero; oficial solo con fuente aplicable a ponedoras y validación competente | Cliente/VLE; etiqueta oficial |
| D06 | Piloto e infraestructura | Volumen, usuarios, conectividad, responsables y RPO/RTO acordados | Dueño/operaciones; rendimiento/go-live |
| D07 | Corrección histórica, cierre, retención | No borrar; motivo, reversión trazable y momento efectivo | Dueño/encargado; AUD-04, MOV-06, retención |

Agrupar preguntas en entrevista breve. No pedir al cliente decisiones técnicas rutinarias ni reabrir decisiones cerradas sin evidencia nueva.

## 5. Fases y dependencias

| Fase | Resultado | Depende de |
|---|---|---|
| ORQ | Orquestación recuperable y documentación coherente | Ninguna |
| BAS | Línea base reproducible | ORQ mínima |
| SEG | Acceso y aislamiento | BAS |
| EMP | Alta/configuración/soporte de empresas | SEG |
| EST | Estructura y lotes | SEG |
| CAP | Captura confiable | SEG, EST |
| AUD | Historia/corrección/auditoría | SEG, CAP, D07 |
| MOV | Inventario/ciclo productivo | EST, CAP, AUD, D01/D07 |
| RES | Métricas y supervisión | CAP, MOV |
| REP | Excel/PDF | AUD, MOV, RES |
| ACT | Frescura y PWA | CAP, RES, D04 |
| UX | Experiencia sencilla | Módulos disponibles |
| QAL | Regresión y rendimiento | Módulos v1, D06 |
| OPS | Staging y recuperación | QAL, EMP, D06 |
| ENT | Piloto y aceptación | Fases anteriores |

Adelantar investigación, documentación y pruebas independientes; no saltar integridad para pulir etapa 2. Prioridad P1 para integridad/seguridad/entrega, P2 para optimizaciones medidas.

## 6. ORQ — Orquestación, skills, mensajes y portal

Archivos: `.cursor/commands/avicore-architect-direct.md`, catálogo y skills contexto/auditoría/evolución, reglas, `portal/contenido/desarrollo/`, scripts. Puerta: fuentes alineadas y recuperación entre sesiones demostrada.

- [x] **ORQ-01 — Registrar baseline y contrato activo.** Identificar instrucciones SIPROD/.windsurf heredadas y rutas inexistentes. Éxito: entrada AviCore válida sin requerir documentos de otro proyecto.
- [x] **ORQ-02 — Reconciliar capacidades.** Roadmap, producto y arquitectura basados en código/rutas/tests. Éxito: PWA/admin no figuran a la vez hechos y pendientes; preview no cuenta como módulo.
- [x] **ORQ-03 — Incorporar ejecutar-plan.** Selección por ID/dependencia, investigación, implementación, verificación y checkpoint. Éxito: sesión nueva retoma sin chat anterior.
- [x] **ORQ-04 — Hacer obligatoria la prueba de cierre.** Éxito: no se declara realizado por terminar de editar; bloqueos humanos permanecen visibles.
- [ ] **ORQ-05 — Unificar alcance de auditoría.** Rutas/sesión/diff/flujo/producto y dependencias; cinco prioridades en chat, todos los hallazgos en documento. Éxito: revisión integral no queda truncada por plantilla.
- [ ] **ORQ-06 — Retirar porcentajes subjetivos.** Estado, evidencia, severidad y criterio incumplido. Éxito: progreso con denominador verificable.
- [x] **ORQ-07 — Proteger worktree sin bloqueo global.** Baseline, colisiones y archivos nuevos. Éxito: no forzar stash/pull ni sobrescribir trabajo del usuario.
- [x] **ORQ-08 — Mensajes copiables para plan y reanudación.** Variante del mensaje 1 con ruta/checkpoint; conservar autorización de publicación. Éxito: copiar genera instrucción completa y simple.
- [ ] **ORQ-09 — Lectura progresiva y fuente única.** No duplicar matriz de skills ni cargar referencias irrelevantes. Éxito: cada contrato tiene dueño y enlaces vigentes.
- [ ] **ORQ-10 — Incluir portal en anti-drift.** href, data-portal-page/href, NAV_SECTIONS y anclas. Éxito: fixture de enlace roto falla; destinos válidos pasan.
- [ ] **ORQ-11 — Validar checklist.** IDs únicos, dependencias existentes y sin ciclos, evidencia para cerradas. Éxito: caso inválido devuelve exit no cero.
- [x] **ORQ-12 — Enlazar estos MD desde Desarrollo.** Acceso/descarga sin duplicar texto. Éxito: funciona con raíz del servidor `portal/` y enlaces entre documentos.
- [ ] **ORQ-13 — Probar portal en navegador.** Copia exacta, teclado, móvil, tema y páginas imprimibles. Éxito: sin errores de consola ni navegación rota.
- [ ] **ORQ-14 — Revisar servidor local.** URL mal codificada controlada y confinamiento por directorio real, no prefijo de cadena. Éxito: petición inválida devuelve 4xx sin exponer carpeta vecina.
- [ ] **ORQ-15 — Ensayar continuación.** Escribir checkpoint y continuar en contexto nuevo. Éxito: no repite cambios ni marca pendientes como hechas.

Verificación: `pnpm run check:agent-docs`, pruebas nuevas de validadores y portal real. Chat breve no limita extensión del artefacto solicitado.

## 7. BAS — Línea base de calidad

Archivos: tests, PHPUnit, manifests, lockfiles y CI. Puerta: fallos clasificados y baseline reproducible.

- [ ] **BAS-01 — Aislar tests.** APP_ENV testing, base exclusiva y sin config cache a datos reales; evitar suites simultáneas destructivas. Éxito: preflight rechaza base productiva.
- [x] **BAS-02 — Resolver fallo del menú.** Revisar `tests/Feature/Ui/AdminUserMenuTest.php:35` y «Resumen de Avícola Demo». Éxito: contrato UI claro y prueba útil; no borrar assertion por conveniencia.
- [x] **BAS-03 — Obtener baseline verde.** Suite completa tras resolver fallo, build y Pint en modo comprobación. Éxito: fecha, revisión y resultados registrados.
- [ ] **BAS-04 — Paridad de versiones.** PHP 8.3+, Node 22 destino frente a 24 local, pnpm fijado. Éxito: build con versión destino sin actualización masiva oportunista.
- [ ] **BAS-05 — Aislar Vite de tests.** Salida actual usa localhost:5173; configurar manifest de build para integración/CI. Éxito: no depender de un dev server ajeno.
- [ ] **BAS-06 — Verificar CI real.** Tests, formato, build, docs y PostgreSQL del SHA a entregar. Éxito: evidencia remota, no solo archivo workflow presente.

## 8. SEG — Identidad, permisos y multiempresa

Archivos: `UserRole.php`, policies, middleware, Actions Auth/User, AppServiceProvider, rutas y tests. Puerta: autorización negativa y aislamiento por todos los caminos.

| Rol | Experiencia v1 | Límite |
|---|---|---|
| Admin AviCore | Empresas, administrador inicial y soporte | No operar producción sin contexto auditado |
| Dueño | Resumen/reportes/equipo/trazabilidad y móvil | Gestión adicional solo según D02 |
| Administrativo | Estructura/usuarios/operación/reportes autorizados | Sin autoescalada ni datos de otra empresa |
| Encargado | Supervisión/lotes/correcciones/movimientos | Capacidades explícitas, no todo el panel |
| Operario | Inicio/Cargar/Historial/Perfil | Anulación propia del día; sin administración |
| Reparto | Fuera de v1 | Denegación controlada, nunca 500 |

- [x] **SEG-01 — Completar enum.** Reparto en los tres métodos fallidos. Éxito: todos los casos/métodos sin UnhandledMatchError y sin ampliar permisos.
- [x] **SEG-02 — Vigencia por request.** Usuario desactivado o empresa suspendida bloquean sesión existente. Éxito: GET y acción Livewire abierta antes del cambio denegados sin mutación.
- [x] **SEG-03 — Cambios de rol y reset.** Revocar capacidad previa y exigir cambio de clave temporal. Éxito: snapshot previo no evita restricciones.
- [x] **SEG-04 — Autorizar cada acción en servidor.** No depender de mount/URL/botón; middleware compatible con Livewire 4. Éxito: llamada manipulada denegada.
- [x] **SEG-05 — Aislamiento transversal.** IDs ajenos en consulta, filtro, alta, edición, anulación, movimiento y descarga. Éxito: sin filtración ni mutación de empresa ajena.
- [x] **SEG-06 — Coherencia relacional.** Empresa de padre/hijo/actor compatible mediante constraints o validación transaccional suficiente. Éxito: ID existente pero ajeno rechazado.
- [x] **SEG-07 — Cerrar D02 y matriz.** Pruebas parametrizadas por capacidad. Éxito: Dueño/Administrativo descritos según permisos reales.
- [x] **SEG-08 — Administración segura.** Auto-desactivación, asignación de rol prohibido y último administrador. Éxito: no dejar empresa sin gestión ni permitir escalada.
- [x] **SEG-09 — Login multiempresa.** Documento repetido, claves ambiguas y experiencia de selección solo si hace falta. Éxito: nunca entrar a empresa incorrecta ni revelar cuentas.
- [x] **SEG-10 — Recuperación y sesiones.** Reset autorizado, entrega segura de clave, expiración/logout. Éxito: no registrar secretos ni conservar acceso revocado.
- [x] **SEG-11 — Separar demo.** Guard de production y revisión de staging con datos reales; evitar cambiar rol compartido entre demostradores. Éxito: flag accidental no abre datos productivos.
- [x] **SEG-12 — Superficies técnicas.** CSRF, escape, archivos/logo, HTTPS/cookies y dependencias. Éxito: vulnerabilidades confirmadas resueltas antes de entregar.

Pruebas: PostgreSQL, Feature/Livewire y navegador con página abierta. Render por sí solo no certifica seguridad.

## 9. EMP — Empresas, configuración y soporte

Base: `app/Models/Empresa.php`, EmpresaContextService y UserPolicy. Crear pantallas/Actions faltantes tras verificar inventario. Depende de SEG.

- [x] **EMP-01 — Alta de empresa real.** Nombre, identificador, estado y administrador inicial en transacción. Éxito: operar empresa vacía sin seed demo.
- [x] **EMP-02 — Activar/suspender/reactivar.** Motivo, actor, fecha y efectos sobre sesiones/tareas. Éxito: suspensión bloquea según SEG-02 sin borrar historia.
- [x] **EMP-03 — Configuración mínima.** Nombre/logo, zona horaria y unidades; reutilizar `empresas.configuracion` si basta. Éxito: no añadir tabla genérica sin necesidad.
- [x] **EMP-04 — Unidades consistentes.** Huevo base, maple 30, cajón acordado por empresa y sobrantes visibles. Éxito: pantalla/export calculan igual sin perder huevos.
- [x] **EMP-05 — Onboarding corto.** Empresa → administrador → granja → galpón → lote/saldo inicial → operario. Éxito: faltantes accionables sin pasos comerciales.
- [x] **EMP-06 — Soporte auditado.** Empresa, motivo, actor, inicio/fin, caducidad, permisos mínimos y banner. Éxito: contexto no da acceso silencioso a producción.
- [x] **EMP-07 — Salida de soporte.** Limpiar override al finalizar/logout, validar destino y registrar acciones. Éxito: contexto A no contamina B.
- [x] **EMP-08 — Datos personales.** Finalidad/acceso/retención acordada, minimizar documento en vistas/export. Éxito: inventario y política verificable; no afirmar cumplimiento legal sin validación.

## 10. EST — Estructura avícola y lotes

Base: Livewire Admin/Estructura, Actions Granja/Galpon/Lote, policies y migraciones. Depende de SEG. No rehacer CRUD existente.

- [x] **EST-01 — Granjas completas.** Alta/edición, DICOSE texto, unicidad según regla, estado y datos mínimos. Éxito: duplicados/inconsistencias rechazados con mensajes útiles.
- [x] **EST-02 — Galpones completos.** Granja de misma empresa, código/nombre, disponibilidad y estados. Éxito: inactivo/mantenimiento no acepta carga y sigue en historia.
- [x] **EST-03 — Jerarquía de estados.** Definir efecto de granja inactiva sobre galpones. Éxito: no operar hijos por ruta alternativa cuando el contrato lo prohíbe.
- [x] **EST-04 — Alta de lote autorizada.** Código único, SMA opcional, fechas, población inicial y tipo. Éxito: operario sin permiso no crea; múltiples tipos generan lotes coherentes.
- [ ] **EST-05 — Validación en Action.** Fechas futuras/nacimiento posterior a ingreso, tipos inválidos, cantidades y concurrencia. Éxito: llamada fuera de UI no evade reglas ni duplica código.
- [ ] **EST-06 — Separar edición/transición.** UpdateLoteAction no cambia estado crítico sin delegar al circuito controlado. Éxito: no reabrir desde formulario genérico sin motivo/auditoría.
- [ ] **EST-07 — Listados útiles.** Búsqueda y filtros granja/galpón/lote/estado/tipo, paginación. Éxito: conservan empresa y explican indisponibilidad.
- [ ] **EST-08 — Vacío y mantenimiento.** Acordar sin lote, carga excepcional y vacío sanitario sin automatismo normativo inventado. Éxito: no mezclar ciclos.
- [ ] **EST-09 — Baja y reasignación seguras.** Padres con historia retenidos; no cambiar empresa rompiendo referencias. Éxito: bajas lógicas conservan trazabilidad.
- [ ] **EST-10 — Ficha de lote/galpón.** Población, estado, ubicaciones e historia; métricas por lote solo si atribuibles. Éxito: origen y límites del saldo visibles.

## 11. CAP — Operación móvil confiable

Base: `app/Livewire/Operario/`, concerns, Actions Operacion, OperarioGalponService, views y tests. Depende de SEG y EST.

| Captura | Regla | Rechazos / semántica |
|---|---|---|
| Huevos | Aptos/descarte enteros no negativos; al menos uno positivo según contrato actual | Cero confirmado requiere D03 |
| Muertes | Entero positivo y saldo suficiente | Distinto de descarte de aves |
| Descarte de aves | Salida de aves vivas | No huevo descartado; saldo suficiente |
| Alimento | Kg entregados positivos, decimales | No representa consumo |
| Vacunación | Lote elegible del galpón/empresa y tipo permitido | No lote ajeno/cerrado |
| Alta de lote | Solo perfiles autorizados | No dar permiso por mostrar botón |

- [ ] **CAP-01 — Selector robusto.** Recordar galpón disponible, invalidar ajeno/inactivo, mostrar contexto siempre. Éxito: no guardar en galpón anterior tras cambiar selección.
- [ ] **CAP-02 — Huevos punta a punta.** Cantidades, teclado, confirmación y acumulado. Éxito: dos cargas nuevas suman; reintento no duplica.
- [ ] **CAP-03 — Muertes transaccionales.** Lock de saldo y error conservando entrada. Éxito: solicitudes simultáneas no dejan saldo negativo.
- [ ] **CAP-04 — Descarte de aves.** Integridad de saldo, etiqueta diferenciada y anulación. Éxito: no contabiliza muerte ni huevo descartado.
- [ ] **CAP-05 — Alimento entregado.** Precisión, coma decimal en UI, límites documentados y múltiples entregas. Éxito: días sin entrega no significan falta de alimentación.
- [ ] **CAP-06 — Vacunación básica.** Lote/empresa/galpón vigente, tipo y detalle útil; anulación. Éxito: historia sanitaria sin inventar calendario o prescripción.
- [ ] **CAP-07 — Idempotencia.** Clave por intención/empresa y resultado persistido. Éxito: doble toque/timeout crea una operación; nueva intención de igual cantidad crea otra.
- [ ] **CAP-08 — Estado actual bajo lock.** Revalidar disponibilidad y saldo dentro de mutación crítica. Éxito: inactivación/cierre concurrente no acepta carga prohibida.
- [ ] **CAP-09 — Red y respuesta perdida.** Mantener formulario y distinguir pendiente/error/confirmado. Éxito: reintento usa misma clave, éxito solo después de persistir.
- [ ] **CAP-10 — Cero y ausencia con D03.** Mecanismo mínimo acordado sin cierre diario obligatorio. Éxito: cero real distinguible de omisión.
- [ ] **CAP-11 — Hora de corte.** Zona acordada, medianoche y fecha del registro. Éxito: historial/totales/anulación comparten día lógico.
- [ ] **CAP-12 — Perfil/ayuda.** Datos propios permitidos, contraseña y contacto real. Éxito: no modificar rol/empresa/documento desde perfil.
- [ ] **CAP-13 — Formularios obsoletos.** Cambios de galpón/rol con formulario abierto revalidados. Éxito: sin datos cruzados ni falsa confirmación.
- [ ] **CAP-14 — Recorrido móvil.** Login → galpón → capturas → historial → anular con motivo. Éxito: teléfono representativo, sin asistencia técnica.

Pruebas: Feature/Livewire para reglas y navegador/dispositivo para teclado, foco y red. No introducir cola offline en este módulo.

## 12. AUD — Historial, correcciones y auditoría

Base: Historial, Actions/policies de anulación. Auditoría transversal y corrección aún pendientes. Depende de SEG, CAP y D07.

- [ ] **AUD-01 — Historial operario.** Paginación, fecha, detalle, usuario/galpón/estado, vacunación integrada. Éxito: orden estable y datos autorizados.
- [ ] **AUD-02 — Anulación propia del día.** Motivo, retención y exclusión de totales. Éxito: repetir anulación no restaura aves dos veces.
- [ ] **AUD-03 — Historial supervisor.** Empresa/granja/galpón/usuario/tipo/estado/período. Éxito: supervisión no depende de historial propio.
- [ ] **AUD-04 — Corrección con D07.** Antes/después, razón, actor, fecha efectiva y original vinculado. Éxito: saldo/totales cambian una vez con historia preservada.
- [ ] **AUD-05 — Auditoría crítica.** Usuarios/roles, empresas, soporte, lotes, movimientos, ajustes y correcciones. Éxito: quién/qué/cuándo/por qué sin claves en logs.
- [ ] **AUD-06 — Atomicidad.** Fallo de auditoría requerida revierte mutación. Éxito: prueba de rollback sin saldo parcial.
- [ ] **AUD-07 — Consulta autorizada.** Filtros y detalle, sin edición/borrado desde UI. Éxito: operario sin bitácora global y empresa aislada.
- [ ] **AUD-08 — Retención/documentos.** Aplicar D07 y conservar versiones emitidas. Éxito: plazos acordados, sin inventar exigencias legales.
- [ ] **AUD-09 — Tipos históricos.** Verificar existencia de `combinado`; definir anulación/restauración o migración. Éxito: tipo legado no rompe saldo por usar un camino diferente.

## 13. MOV — Aves, movimientos y ciclo productivo

Pendiente principal. Nuevas Actions/modelo/migraciones tras especificación; no editar migraciones históricas ya desplegadas. Depende de EST, CAP, AUD, D01/D07.

Invariantes: no saldo negativo; traslado conserva total; ajuste explícito; muerte/descarte afectan una vez; reversión inversa auditada; historia independiente de ubicación actual. Locks en orden estable para evitar deadlocks.

- [ ] **MOV-01 — Modelo mínimo.** Origen/destino, lote si corresponde, cantidad/tipo, momento efectivo, actor/motivo/reversión. Éxito: ejemplos reales aprobados permiten reconstruir saldo.
- [ ] **MOV-02 — Población por lote con D01.** Conciliar muertes del galpón antes de trasladar/cerrar parcialmente. Éxito: estimación no presentada como hecho.
- [ ] **MOV-03 — Entrada y saldo inicial.** Vincular alta/corte sin duplicar incremento de RegistrarLoteAction. Éxito: reintento/importación no suma dos veces.
- [ ] **MOV-04 — Traslado.** Misma empresa, destino disponible, origen distinto, cantidad/saldo/lote. Éxito: éxito atómico; fallo conserva ambos saldos.
- [ ] **MOV-05 — Ajuste de inventario.** Conteo vs sistema, diferencia/motivo/rol superior. Éxito: no reescribir mortalidad para cuadrar.
- [ ] **MOV-06 — Cierre de lote.** Remanente, destino/salida, fecha y motivo con D07. Éxito: sin aves fantasma ni nuevas cargas incompatibles.
- [ ] **MOV-07 — Reapertura excepcional.** Permiso/motivo y conflictos con nuevo ciclo. Éxito: no sumar población inicial otra vez.
- [ ] **MOV-08 — Reversión.** Validar movimientos posteriores y saldo; inversa enlazada. Éxito: segunda reversión rechazada sin negativos.
- [ ] **MOV-09 — Conciliación.** Inicial + entradas − salidas − muertes − descartes + ajustes/reversiones = final. Éxito: diferencias visibles por galpón/período.
- [ ] **MOV-10 — Ubicación histórica.** Consultar ubicación al momento del hecho. Éxito: trasladar no reasigna producción pasada al destino nuevo.
- [ ] **MOV-11 — Concurrencia real.** Dos conexiones PostgreSQL: traslados opuestos, doble cierre, muerte vs traslado. Éxito: invariantes y recuperación; dos llamadas secuenciales no bastan.
- [ ] **MOV-12 — UI de supervisor.** Vista previa del efecto y motivo obligatorio. Éxito: operario no ejecuta acciones superiores.
- [ ] **MOV-13 — Faena como salida operativa.** Destino/motivo/referencias si caso real; documentos oficiales sujetos a D05. Éxito: trazabilidad interna sin prometer envío SMA automático.

## 14. RES — Resumen y supervisión

Base: AdminResumenService, OperarioGalponResumenService, AdminHomeService y páginas Inicio/Resumen. Depende de CAP/MOV para métricas finales.

- [ ] **RES-01 — Quitar previews de v1 productiva.** Stock/demanda/mapa/Comercial fuera de experiencia real. Éxito: ninguna cifra ficticia mezclada con producción del cliente.
- [ ] **RES-02 — Definir métricas.** Fuente/unidad/período/población/exclusiones/ausencia. Éxito: referencia canónica con casos verificables.
- [ ] **RES-03 — Completitud diaria.** D03: registro por tipo, cero y omisión. Éxito: alimento solo no dispara «todas las cargas al día».
- [ ] **RES-04 — Conciliar totales.** Inicio/Resumen/Historial iguales en mismo scope; anulaciones/correcciones. Éxito: datos de prueba conocidos coinciden.
- [ ] **RES-05 — Mortalidad y ventana.** Movimientos/coexistencia/cierres; no inferir tasa exacta por lote. Éxito: cerrar lote no borra historia ni cambia denominador sin explicación.
- [ ] **RES-06 — Umbrales.** Validar 1,1% y período con responsable. Éxito: referencia etiquetada, sin diagnóstico automático ni norma universal inventada.
- [ ] **RES-07 — Comparaciones honestas.** Hoy parcial, ayer completo y denominador cero. Éxito: porcentajes contextualizados o no calculables explícitos.
- [ ] **RES-08 — Gráficos útiles.** Aptos/descarte/muertes/kg entregados y tabla accesible. Éxito: fecha sin carga no equivale automáticamente a cero confirmado.
- [ ] **RES-09 — Excepciones primero.** Alerta accionable con acceso al galpón. Éxito: dueño identifica qué revisar sin interpretar tarjetas técnicas.
- [ ] **RES-10 — Equipo real de solo lectura.** Roles/estado; minimizar datos personales. Éxito: actividad no etiquetada como productividad laboral sin fundamento.
- [ ] **RES-11 — Entrega no es consumo.** No calcular conversión con kg entregados. Éxito: textos/gráficos/export no afirman eficiencia no medida.

## 15. REP — Reportes y exportaciones

Base documental: skill `avicore-reportes`; módulo aún por crear. Depende de AUD/MOV/RES. Investigar bibliotecas compatibles antes de añadir dependencias; elegir solución mantenida y suficiente.

| Reporte v1 | Contenido | Filtros / aceptación |
|---|---|---|
| Producción diaria | Aptos/descarte de huevos, muertes, descarte de aves y alimento entregado | Empresa/granja/galpón/período; igual a consulta |
| Movimientos/existencias | Saldo inicial, entradas/salidas/ajustes/reversiones/final | Origen/destino/lote cuando atribuible; conciliación |
| Historia de lote | Alta, ubicaciones, vacunas y movimientos | Lote/fechas; límite explícito de producción por galpón |
| Sanidad básica | Vacunaciones y anulaciones autorizadas | Lote/galpón/período; sin diagnóstico/tratamiento |
| Auditoría operativa | Cambios y razones | Actor/tipo/fechas; permiso restringido |

- [ ] **REP-01 — Confirmar catálogo.** Validar destinatario y utilidad de cada reporte con cliente/D05. Éxito: cada salida responde una necesidad concreta.
- [ ] **REP-02 — Consulta compartida.** Vista/Excel/PDF usan mismas reglas y agregados. Éxito: filas/totales iguales sin fórmulas duplicadas.
- [ ] **REP-03 — Excel productivo.** Fechas/números nativos, títulos/unidades/filtros/totales. Éxito: abre sin reparación y se analiza sin limpiar texto.
- [ ] **REP-04 — PDF operativo.** Logo cliente/AviCore, empresa/DICOSE cuando aplica, período/generación/páginas. Éxito: A4 legible, cabeceras repetidas, sin cortes ni observaciones operarias en principal.
- [ ] **REP-05 — Movimientos/conciliación.** Inicial/final y reversiones identificadas. Éxito: resultado aritmético exacto sobre fixtures conocidos.
- [ ] **REP-06 — Lote/sanidad.** Atribución real tras traslados. Éxito: no asignar a cada lote todos los huevos del galpón.
- [ ] **REP-07 — Vacíos/extremos.** Sin registros/logo, textos largos, múltiples páginas, decimales y períodos grandes. Éxito: vacío explícito; fallo de consulta no produce export vacío «exitoso».
- [ ] **REP-08 — Generación/descarga autorizadas.** Revisar permiso/empresa al pedir y descargar, incluida pérdida posterior de acceso. Éxito: URL ajena/caducada no abre archivo.
- [ ] **REP-09 — Contenido seguro.** Neutralizar fórmulas en celdas de texto y escapar PDF. Éxito: nombres/observaciones no ejecutan fórmulas al abrir Excel.
- [ ] **REP-10 — Volumen.** Medir; cola solo si necesaria con progreso/error/reintento/caducidad. Éxito: no bloquear servidor ni duplicar trabajo.
- [ ] **REP-11 — Documento emitido estable.** Metadatos/filtros/fecha de corte y reemisión tras correcciones. Éxito: copia emitida no cambia silenciosamente.
- [ ] **REP-12 — Investigar formato normativo.** Fuente exacta de ponedoras, campos/período/responsable/layout. Éxito: D05 documentada; fuente faltante sigue bloqueada.
- [ ] **REP-13 — Formato oficial condicional.** Implementar solo si D05 lo incorpora a entrega; de lo contrario exclusión aprobada con reporte interno completo. Éxito: no afirmar certificación sin validación.
- [ ] **REP-14 — Validación humana.** Revisar muestra anonimizada en Excel y PDF impreso. Éxito: aceptación por reporte registrada.

## 16. ACT — Frescura y PWA online

Base: `vite.config.js`, JS PWA, layouts y skills tiempo-real/PWA. Depende de CAP/RES y D04. Offline con cola/sincronización queda fuera.

- [ ] **ACT-01 — Frescura visible.** Hora de actualización y refresco. Éxito: red caída no deja parecer actuales datos viejos.
- [ ] **ACT-02 — Cerrar D04.** Refresco periódico acotado o Reverb según necesidad. Éxito: contrato único sin prometer tecnología ausente.
- [ ] **ACT-03 — Mecanismo elegido.** Polling visible sin solapamiento, o eventos tras commit por canal privado de empresa. Éxito: otro dispositivo recibe estado coherente y recupera tras reconexión.
- [ ] **ACT-04 — Aislamiento de Reverb si aplica.** Suscripción ajena rechazada, duplicados no alteran contador y recuperación por consulta. Éxito: prueba con dos empresas; si no se elige, exclusión explícita aprobada.
- [ ] **ACT-05 — Instalación real.** Android/guía iOS, icono, inicio según rol y navegación. Éxito: prueba en dispositivo, no solo manifest.
- [ ] **ACT-06 — Desconexión.** Aviso/formulario sin falso guardado, sin cache privado. Éxito: offline no revela datos de otra sesión ni promete sincronización inexistente.
- [ ] **ACT-07 — Actualización sin pérdida.** Nuevo SW con formulario abierto y rollback. Éxito: recarga no descarta carga silenciosamente.
- [ ] **ACT-08 — Dispositivo compartido.** Logout/cambio de usuario, atrás y cache. Éxito: no mostrar datos privados anteriores.

## 17. UX — Sencillez, accesibilidad e identidad

Conservar diseño/componentes útiles; no rediseño integral por preferencia del agente. Validación visual pendiente en la auditoría base.

- [ ] **UX-01 — Navegación por tarea.** Operario Inicio/Cargar/Historial; dueño resumen/excepciones; gestión según permiso. Éxito: sin menú de módulos no entregados.
- [ ] **UX-02 — Formularios mínimos.** Una acción principal, etiquetas de negocio, defaults seguros y detalles opcionales. Éxito: no pedir empresa/fecha a quien no debe decidirlas.
- [ ] **UX-03 — Estados completos.** Cargando/guardando/éxito/vacío/validación/error/acceso vencido. Éxito: sin blanco ni pérdida silenciosa.
- [ ] **UX-04 — Accesibilidad de interacción.** Teclado, foco, escape, lector de pantalla y nombres accesibles. Éxito: select/calendario/menú sin mouse.
- [ ] **UX-05 — Legibilidad y tacto.** 360/390 px, zoom 200%, contraste y botones cómodos. Éxito: sin solapes de teclado/nav ni scroll horizontal accidental.
- [ ] **UX-06 — Motion moderado.** Reducir movimiento cuando se solicite; no retrasar captura. Éxito: información importante visible sin esperar animación.
- [ ] **UX-07 — Vocabulario consistente.** Galpón/lote, kg entregados, aptos/descarte, anular/corregir, producción/stock. Éxito: misma palabra implica misma operación.
- [ ] **UX-08 — Uso real.** Operario registra y resuelve error; dueño identifica producción/excepción. Éxito: registrar tiempo/errores y corregir bloqueos; objetivo cuantitativo con D06.

## 18. QAL — Calidad y rendimiento

Integridad P1; optimizaciones P2 con medición. Depende de módulos v1 y D06.

- [ ] **QAL-01 — Matriz de cobertura.** Vincular reglas críticas a prueba/tarea. Éxito: éxito, rechazo por rol/empresa, validación y fallos relevantes cubiertos.
- [ ] **QAL-02 — E2E completo.** Empresa → estructura → lote → operario → carga → supervisor → corrección → reporte. Éxito: navegador y BD coinciden.
- [ ] **QAL-03 — Límites.** Medianoche, mes/año, decimales, cero/máximos y anulados. Éxito: UI/BD/export consistentes.
- [ ] **QAL-04 — Concurrencia/idempotencia.** Escenarios CAP-07/MOV-11 y rollback. Éxito: sin duplicados ni negativos.
- [ ] **QAL-05 — Baseline medido.** 1/10/100 galpones e historia representativa; hardware/red/datos/p50/p95/consultas/memoria. Éxito: objetivo D06 comprobable, no percepción.
- [ ] **QAL-06 — Optimizar resumen.** Agregación por galpón, precarga y reutilización intra-request según medición. Éxito: menos consultas con iguales totales y sin cache cruzado.
- [ ] **QAL-07 — Índices/planes.** EXPLAIN en test para historial/agregados/fecha/empresa. Éxito: rango/índice justificado, no especulativo.
- [ ] **QAL-08 — Assets.** Mapa fuera de v1, carga diferida y fuentes necesarias. Éxito: comparar bytes/peticiones/tiempos en móvil antes/después.
- [ ] **QAL-09 — Tests significativos.** Contratos y comportamiento, no frases accidentales; conservar snapshots útiles. Éxito: cambio de título no elimina prueba de seguridad.
- [ ] **QAL-10 — CI final.** Tests/formato/build/dependencias/docs y E2E sobre SHA final. Éxito: evidencia ejecutada, no prueba omitida reportada como aprobada.
- [ ] **QAL-11 — Deuda aceptada.** Solo no bloqueante con razón/disparador/tarea futura. Éxito: no rebajar severidad para llegar al 100%.

## 19. OPS — Infraestructura y recuperación

Base: deploy Laravel Cloud, CI y config Laravel. No publicar sin autorización de destino/momento. Depende de QAL, EMP y D06.

- [ ] **OPS-01 — Entornos separados.** Local/test/staging/producción con BD/secretos distintos. Éxito: demo/tests no escriben producción.
- [ ] **OPS-02 — Staging reproducible.** Lockfiles, assets, configuración, migraciones y versión. Éxito: arranca de checkout limpio sin seed demo obligatorio.
- [ ] **OPS-03 — Config segura.** Debug off, HTTPS/cookies/proxies adecuados y demo off. Éxito: valores efectivos comprobados sin imprimir secretos.
- [ ] **OPS-04 — Migración segura.** Actualizar desde datos previos, backfill y constraints. Éxito: historia intacta y recuperación/ventana operativa documentadas.
- [ ] **OPS-05 — Carga inicial real.** Plantilla de estructura/lotes/saldos, dry-run y rechazos. Éxito: conciliación del responsable y reejecución sin duplicados.
- [ ] **OPS-06 — Backup automático.** BD/archivos necesarios, frecuencia/acceso/retención con D06/D07. Éxito: copia exitosa y alerta de fallo.
- [ ] **OPS-07 — Restauración ensayada.** Destino aislado, login/consulta/conciliación/export. Éxito: tiempos/pérdida comparados con RPO/RTO acordados.
- [ ] **OPS-08 — Observabilidad.** Salud/errores/colas si existen/espacio/latencia; contexto sin secretos. Éxito: incidente de prueba detectado y responsable identificado.
- [ ] **OPS-09 — Workers/eventos.** Solo servicios usados, reinicio/recuperación. Éxito: despliegue no pierde tareas pendientes.
- [ ] **OPS-10 — Rollback.** Versión anterior compatible y estrategia de datos. Éxito: ensayo staging, sin down destructivo improvisado.
- [ ] **OPS-11 — Runbook.** Red, acceso, dato erróneo, restore y escalamiento. Éxito: otra persona puede seguirlo.
- [ ] **OPS-12 — Accesos administrativos.** Cuentas nominales, mínimo privilegio, secretos gestionados y baja temporal. Éxito: sin credenciales demo activas al corte.

## 20. ENT — Piloto y entrega

Depende de puertas técnicas y decisiones humanas; UI terminada no basta.

- [ ] **ENT-01 — Guion de demo.** Empresa/galpón/lote, móvil, anulación, resumen y export. Éxito: datos demo rotulados y cero promesas de funciones ausentes.
- [ ] **ENT-02 — Piloto acotado.** Personas/galpones/duración D06 y comparación con registro habitual. Éxito: incidencias y discrepancias diarias registradas.
- [ ] **ENT-03 — Conciliar piloto.** Producción/muertes/descarte/alimento/saldo contra fuente real. Éxito: diferencias explicadas/corregidas con trazabilidad.
- [ ] **ENT-04 — Capacitar por rol.** Guías cortas operario/supervisor/dueño/admin; instalar y pedir ayuda. Éxito: cada rol completa recorrido sin que el técnico maneje el teléfono.
- [ ] **ENT-05 — Cerrar bloqueantes.** Pérdida/duplicación/permisos/saldos/usabilidad. Éxito: cero P0/P1 con regresión comprobada.
- [ ] **ENT-06 — Paquete de entrega.** Versión/alcance/manuales/online/soporte/restore/etapa 2. Éxito: límites conocidos por cliente.
- [ ] **ENT-07 — Aceptación funcional.** Confirmación verificable del responsable por criterios/reportes. Éxito: no autofirma del agente.
- [ ] **ENT-08 — Go-live autorizado.** Destino/fecha/corte/backup/responsable. Éxito: autorización y smoke test posterior por rol.
- [ ] **ENT-09 — Acompañamiento inicial.** Revisar errores y conciliación en período acordado. Éxito: operación estable y responsable de continuidad.

## 21. Puertas finales v1

Estos casilleros son acta de aceptación, no tareas adicionales para inflar el porcentaje.

- [ ] **GATE-01:** tareas obligatorias aplicables verificadas; exclusiones justificadas/aprobadas.
- [ ] **GATE-02:** cliente nuevo operable sin ficticios ni SQL rutinario.
- [ ] **GATE-03:** aislamiento con dos empresas y todos los roles existentes.
- [ ] **GATE-04:** reintentos/concurrencia sin duplicados ni corrupción de aves/totales.
- [ ] **GATE-05:** movimientos/cierres/correcciones trazables y conciliados.
- [ ] **GATE-06:** Inicio/Resumen/Excel/PDF coherentes y limitaciones honestas.
- [ ] **GATE-07:** móvil/PWA probados, incluida desconexión.
- [ ] **GATE-08:** suite/build/CI del SHA final y E2E ejecutados.
- [ ] **GATE-09:** respaldo/restore ensayados, rollback y soporte definidos.
- [ ] **GATE-10:** aceptación humana y autorización productiva registradas.

## 22. Segunda etapa — dirección, no ejecución v1

No ejecutar por invocar este plan. Crear especificación separada cuando el usuario active etapa 2; previews no demuestran persistencia.

| Módulo | Funciones a especificar | Dependencia / criterio mínimo |
|---|---|---|
| Clientes | Ficha/contacto/dirección/estado/preferencias | Empresa/privacidad; no datos cruzados |
| Stock de huevos | Producción validada, clasificación si aplica, ajustes/mermas | Stock distinto de producción; conciliación |
| Pedidos | Borrador/confirmado/reservado/preparado/entregado/cancelado | Disponibilidad/unidades/transiciones/idempotencia |
| Preparación | Pedido/preparado/faltantes/sustituciones autorizadas | No descontar inventario dos veces |
| Reparto | Asignaciones, recorrido simple, parcial/no entrega/devolución | Rol limitado; móvil/red |
| Venta operativa | Registro ligado a entrega, precios y anulación | Alcance monetario definido; no facturación DGI |
| Devoluciones | Motivo/cantidad/reingreso apto o merma/vínculo | Conciliar pedido/stock/venta |
| Reportes comerciales | Ventas/entregas/pendientes por cliente/período | Datos reales y aislamiento |

Fuera incluso de etapa 2 salvo pedido: contabilidad integral, DGI, rutas avanzadas, API SMA, sensores/IA, stock industrial de alimento, tratamientos veterinarios completos y offline complejo.

## 23. Mensaje para iniciar ejecución

```text
/avicore-architect-direct
Ejecutá portal/planes/PLAN-MAESTRO-ENTREGA-AVICORE.md.
Leé el diagnóstico enlazado y el checkpoint; preservá cambios locales previos.
Empezá por la primera tarea P1 desbloqueada y reutilizá lo implementado.
Investigá dudas concretas en fuentes oficiales y verificá antes de marcar tareas.
Si falta decisión humana, registrá bloqueo y continuá trabajo independiente.
V1 incluye operación avícola; comercial/reparto quedan en etapa 2.
No hagas commit, push, PR ni despliegue sin autorización explícita.
Al cerrar sesión actualizá evidencia, estado y siguiente ID.
```

## 24. Evidencias y decisiones de ejecución

### Línea base — 2026-09-26

- Revisión d8af36d y cambios locales previos; no es certificación del commit aislado.
- Build exit 0; verificadores agente/enlaces/cloud aprobados con límites del diagnóstico.
- Suite: 412 pruebas, 411 aprobadas, 1 fallida, 1.753 aserciones; duración reportada 181.231 ms.
- Fallo: AdminUserMenuTest, esperaba «Resumen de Avícola Demo» ausente.
- Reparto: tres UnhandledMatchError reproducidos sin modificar datos.
- Ninguna tarea de implementación queda completada por este inventario.

### Decisión confirmada

2026-09-26, usuario: operación avícola completa en primera entrega; comercial/reparto en segunda etapa.

### ORQ-01 — 2026-09-26 · VERIFICADA

```text
ID y estado: ORQ-01 VERIFICADA
Fecha, revisión y estado del diff: 2026-09-26 · base b2623ef · sin commit
Archivos afectados: portal/planes/CHECKPOINT.md, evidencias/ORQ-01-*, plan-entrega.html, site.nav.js
Resultado observable: contrato activo documentado; sin SIPROD/windsurf/docs/ en entrada ejecutable
Pruebas/comandos: pnpm run check:agent-docs exit 0; búsqueda rutas obsoletas sin hallazgos en config activa
Referencia canónica: portal/planes/ (capa humana); sin cambio de contrato de producto
Riesgos/bloqueos: ninguno
Siguiente ID: ORQ-02
```

Detalle: [evidencias/ORQ-01-baseline-contrato.md](evidencias/ORQ-01-baseline-contrato.md).

### ORQ-12 — 2026-09-26 · VERIFICADA

Página `portal/contenido/desarrollo/plan-entrega.html` con enlaces de descarga/apertura a plan, diagnóstico y checkpoint. Entrada en `NAV_SECTIONS` → Equipo técnico.

### ORQ-03 / 04 / 07 / 08 — 2026-09-26 · VERIFICADA

Modo ejecutar plan en comando slash; plantilla **1b**; puerta de cierre en paso 4; worktree sin bloqueo global. Detalle: [evidencias/ORQ-03-ejecutar-plan.md](evidencias/ORQ-03-ejecutar-plan.md).

### ORQ-02 — 2026-09-26 · VERIFICADA

```text
ID y estado: ORQ-02 VERIFICADA
Fecha, revisión y estado del diff: 2026-09-26 · sin commit
Archivos afectados: estado-capacidades.md, producto.md, plan-desarrollo.md, arquitectura.md, estrategia-implementacion.md, portal mvp/contexto, README
Resultado observable: fuente única estado-capacidades.md; PWA=hecho; Reverb=pendiente; Comercial/Reparto=preview etapa 2; Dueño≠Administrativo alineado a permisos.md
Pruebas/comandos: pnpm run check:agent-docs exit 0; contraste rutas web.php vs matriz
Referencia canónica: avicore-contexto/references/estado-capacidades.md
Riesgos/bloqueos: ninguno
Siguiente ID: ORQ-03
```

Detalle: [evidencias/ORQ-02-reconciliar-capacidades.md](evidencias/ORQ-02-reconciliar-capacidades.md).

### Registro futuro

Agregar cada cierre con plantilla de sección 3. No reemplazar historial de decisiones ni borrar fallos anteriores para aparentar baseline verde.
