# AviCore — Diagnóstico y mejoras para retomar el producto

Fecha: 2026-09-26. Revisión local: `d8af36d`, rama `feature/admin-resumen-galpon-select`, con cambios locales previos. Este informe incluye el contenido del workspace, no solo el commit.

## 1. Dictamen

AviCore tiene una base operativa real y una arquitectura suficiente para completar el producto sin reescribirlo: Laravel, PostgreSQL, Livewire, Actions, Services, Policies, UI compartida y tests. Operario es el flujo más desarrollado. Usuarios, estructura, equipo y resumen administrativo ya tienen código; el roadmap subestima ese avance.

Todavía no debe presentarse como producto completo: faltan movimientos de aves, cierre/reapertura consistente de lotes, correcciones auditadas, reportes, administración de empresas y soporte auditado. Existen riesgos de sesiones vigentes, indicadores y datos de muestra que hay que resolver antes del piloto.

Decisión del usuario en esta revisión: **primera entrega = operación avícola completa; comercial y reparto = segunda etapa**. No convertir el MVP en un ERP. La sencillez significa pocas decisiones visibles y operaciones confiables; no ausencia de permisos, controles o trazabilidad.

No se asigna porcentaje global: aún no había un denominador de aceptación aprobado. El plan maestro adjunto establece ese denominador para la primera entrega.

## 2. Qué se revisó y qué no certifica este informe

- Contrato de entrada: `AGENTS.md`, README, comando architect, catálogo de 15 skills, reglas y gobernanza.
- Producto, estrategia, roadmap, permisos, reglas, arquitectura, esquema, reportes y PWA en `.cursor/skills/`.
- Rutas, enum de roles, middleware, policies, Actions de operación/lotes/usuarios, servicios de resumen y demo.
- Inventario de módulos, migraciones y 63 archivos PHP bajo `tests/` (incluye TestCase; no son 63 pruebas individuales).
- Portal: estructura, navegación, mensajes copiables, servidor local y scripts anti-drift.
- Configuración Composer, pnpm, PHPUnit y CI; ejecución de verificadores y build.
- Consulta de fuentes oficiales de Laravel, Livewire y MGAP.

Es una revisión transversal del repositorio y lectura focalizada de flujos críticos; no una auditoría exhaustiva línea por línea. No se hizo prueba visual en navegador, entrevista al cliente, auditoría de producción ni certificación normativa. Las hipótesis de seguridad y rendimiento se distinguen de reproducciones comprobadas.

Context7 no estaba disponible entre las herramientas: se consultó documentación oficial web como alternativa. La terminal en sandbox falló; las lecturas y verificaciones funcionaron mediante ejecución autorizada fuera de él. No se modificó código de aplicación ni se hicieron commits, push o despliegues.

## 3. Estado por capacidad

| Capacidad | Evidencia local | Estado y cierre necesario |
|---|---|---|
| Login y contraseña temporal | `app/Actions/Auth/`, `app/Livewire/Auth/` | Implementado; completar revocación y aislamiento de sesión |
| Roles y paneles separados | `app/Enums/UserRole.php`, `routes/web.php` | Implementado parcial; enum incompleto y permisos a consolidar |
| Multiempresa | `forEmpresa`, policies, `EmpresaContextService` | Base existente; scope es explícito, no garantía global |
| Usuarios y reset | `app/Livewire/Admin/Usuarios/`, `app/Actions/User/` | Implementado; revisar protección del último administrador y sesiones existentes |
| Granjas/galpones/lotes | `app/Livewire/Admin/Estructura/`, Actions y migraciones | CRUD existente; ciclo de vida productivo incompleto |
| Operación móvil | `app/Livewire/Operario/` | Huevos, muertes, descarte, alimento, vacunación, alta de lotes según rol |
| Historial/anulación | `Historial.php`, Actions y policies | Existe; no sustituye corrección y auditoría general |
| Resumen/dueño/equipo | `AdminResumenService`, `AdminHomeService`, Livewire admin | Datos reales más previews; corregir semántica de indicadores |
| Movimientos/ajustes/cierre | Sin módulo equivalente en el inventario | Pendiente funcional, no solo visual |
| Auditoría transversal | `Actions/Auditoria/.gitkeep` | Pendiente; algunos campos de anulación sí existen |
| Reportes Excel/PDF | `Livewire/Reportes/.gitkeep`, referencia reportes | Pendiente; no hay módulo exportador implementado |
| Empresas/soporte | Modelo Empresa + contexto de soporte | No hay circuito completo de alta/suspensión/soporte auditado |
| PWA | Vite, manifest, SW, componentes y referencias | Implementada online; no soporta carga offline completa |
| Reverb/Echo | `Events/.gitkeep`; sin paquetes en manifests revisados | Planificado; documentación contradictoria sobre etapa |
| Comercial/stock/reparto | Preview Comercial y home Reparto | Segunda etapa; retirar previews de la experiencia productiva v1 |
| Portal y orquestación | 15 skills, reglas, 5 plantillas, 3 scripts de verificación | Buena base; falta protocolo de ejecución persistente y cobertura semántica |

## 4. Hallazgos de prioridad alta

### H01 — Permisos del enum incompletos [P1, reproducido]

`app/Enums/UserRole.php:74`, `:122`, `:168`: `Reparto` no tiene rama en `canAccessOperarioMobile`, `canViewResumen` ni `assignableRoles`.

Una ejecución aislada recorriendo los casos del enum devolvió `UnhandledMatchError` para esos tres métodos. Visitar `/operario` con ese rol pasa por el primero. Aun estando Reparto fuera de v1, un rol existente debe denegar de forma controlada.

**Ajuste:** completar las ramas sin ampliar permisos; prueba tabular de todos los roles y métodos; regresión HTTP de acceso denegado. No borrar el rol ni convertir una exclusión funcional en un error 500.

### H02 — Revocación de acceso durante una sesión [P1, brecha estática; falta reproducción HTTP]

`AttemptLoginAction` filtra usuarios activos y comprueba empresa al iniciar sesión. `EnsurePasswordChanged`, `EnsureOperarioAccess` y `EnsureRolePanelAccess` no revalidan conjuntamente usuario activo y empresa vigente. Las Actions operativas autorizan principalmente pertenencia al galpón. `AppServiceProvider` no registra middleware persistente propio de Livewire.

**Riesgo:** suspensión, desactivación o cambio de rol después de abrir una pantalla pueden no bloquear todas las lecturas/escrituras. No se demostró una explotación; hay que reproducirla en entorno de prueba.

**Ajuste:** control central de vigencia por request, autorización de cada operación sensible y pruebas de actualizaciones Livewire con página previamente abierta. Livewire documenta la necesidad de registrar middleware personalizado; no copiar una definición con argumentos sin verificar compatibilidad. Ver fuente F1.

### H03 — Cierre/reapertura de lotes sin transición auditada [P1, confirmado en código]

`app/Actions/Lote/UpdateLoteAction.php:16` acepta cualquier valor del enum y actualiza el estado. No solicita motivo ni escribe auditoría o movimiento; no concilia aves. Las cargas por galpón tampoco equivalen a verificar el ciclo del lote.

**Ajuste:** separar edición descriptiva de transiciones; diseñar cierre, traslado, salida, ajuste y reapertura con invariantes explícitas. No deducir automáticamente aves remanentes por lote cuando mortalidad/descarte se registraron solo por galpón. Resolver esa ambigüedad de negocio antes de programar saldos por lote.

### H04 — Indicador «todas las cargas al día» demasiado fuerte [P1, confirmado en código]

`app/Services/AdminResumenService.php:114` considera que un galpón tiene carga si existe cualquier registro operativo activo del día. Una entrega de alimento puede quitarlo de pendientes aunque falten huevos; ausencia de registro tampoco prueba producción cero.

**Ajuste:** por defecto decir «con registros hoy» y mostrar pendientes por tipo solo cuando exista una regla acordada. Mantener separados cero confirmado, no registrado y dato no aplicable. Comparar día parcial contra ayer completo requiere etiqueta visible.

### H05 — Mortalidad y entrega de alimento requieren definiciones correctas [P1]

`AdminResumenService.php:15` fija 1,1%; `:331` divide muertes de la ventana del galpón por población inicial de lotes actualmente activos. `OperarioGalponResumenService.php:88` usa la fecha del lote activo más antiguo como inicio. Entradas, cierres y coexistencia de lotes pueden cambiar ventana y denominador sin que cambien los registros históricos.

**Ajuste:** especificar población expuesta, período y efectos de movimientos; mientras no haya atribución confiable, mostrar métricas de galpón y no afirmar tasas exactas por lote. Validar umbrales con responsable del establecimiento/VLE. Una referencia sectorial no demuestra un umbral sanitario universal.

Las referencias de negocio llaman «conversión alimenticia» a gramos/ave/día y proponen inferirla de entregas. Son magnitudes distintas: entregado no equivale a consumido. No calcular eficiencia alimentaria sin medición adecuada y fórmula/unidades explícitas.

### H06 — Previews mezclados con datos operativos [P1 para entrega]

`app/Services/AdminHomeService.php:103`: reserva = 4.320 huevos y demanda = 1.800 son constantes; «disponible estimado» las combina con producción real. Está marcado `preview`, pero no condicionado exclusivamente a empresa demo. Comercial usa listas de ejemplo.

**Ajuste:** ocultar stock, demanda, mapa comercial y reparto en v1 productiva. Conservar el trabajo como demo claramente identificada o segunda etapa. No representar producción diaria como venta/salida comercial.

### H07 — Falta protección explícita contra reintentos duplicados [P1, riesgo de diseño]

`RegistrarCargaHuevosAction` crea un registro por invocación y la migración de registros no contiene clave de idempotencia. Se permiten varias cargas válidas por día, por lo que no corresponde un unique empresa/galpón/fecha.

**Ajuste:** identificar cada intención de carga, persistir una clave única por empresa y devolver el resultado original ante un reintento. Probar pérdida de respuesta, doble toque y dos cargas nuevas del mismo valor. Revalidar estados dentro de transacciones que usan locks: la disponibilidad comprobada antes del lock puede quedar obsoleta.

## 5. Orquestación, skills y textos reutilizables

### Lo que conviene conservar

- Un comando de entrada y selección de skills interna.
- Fuentes por dominio y lectura progresiva; no cargar todas las referencias siempre.
- Separación portal humano / contrato ejecutable / reglas cortas.
- Actions, policies y pruebas vinculadas a cambios de negocio.
- Autorización explícita para publicar por Git y documentación anti-drift.

### O01 — Resolver instrucciones heredadas y fuentes contradictorias [P1]

Las instrucciones proporcionadas en la sesión remiten a `docs/PROJECT-CONTEXT.md`, `.windsurf` y skills SIPROD. El archivo de contexto no existe en esta copia; el `AGENTS.md` real apunta al portal y a `.cursor`. `.agents` no devolvió archivos en el inventario.

`producto.md` aún declara Administrativo con mismo alcance que Dueño; `permisos.md` y el enum ya los diferencian. `arquitectura.md` llama PWA pendiente y menciona middleware antiguo. `estrategia-implementacion.md` dice en un apartado que admin no existe, pero otro enumera módulos hechos. El roadmap tiene corte 2026-08-01.

**Acción:** mantener un contrato activo AviCore y limpiar en su origen las instrucciones heredadas. Un único estado por capacidad con fecha y evidencia. Los documentos descriptivos deben enlazar ese estado; no replicar «hecho/pendiente» en tablas incompatibles.

### O02 — Auditoría limitada por archivos y cinco acciones [P1]

El skill exige solo `@rutas`; la plantilla permite sesión/diff como alternativa. Ambos omiten un modo explícito de auditoría integral. El máximo de cinco acciones sirve para resumir, no para agotar hallazgos.

**Acción:** incorporar alcance puntual / flujo / producto; expandir dependencias necesarias. Priorizar cinco acciones en chat y conservar todos los hallazgos en el artefacto. Reemplazar «cumplimiento %» subjetivo por evidencia: confirmado, reproducido, pendiente de prueba, no aplica justificado.

### O03 — Convertir el checklist en ejecución recuperable [P1]

Los cinco mensajes dependen del historial del chat. Falta un estado persistente con tarea actual, dependencias, criterios, bloqueos y evidencia.

**Acción:** ampliar el comando existente con modo «ejecutar plan»: leer estado → elegir tarea desbloqueada → investigar duda concreta → implementar → probar → auditar diff → actualizar contrato y evidencia → siguiente tarea. No crear un segundo orquestador ni quince nuevas skills para esto.

Un ítem solo se marca terminado si pasa sus pruebas y criterios. Los bloqueos humanos se registran y el agente continúa tareas independientes. La autonomía no autoriza inventar requisitos, publicar, borrar datos ni declarar aceptación del cliente.

### O04 — Worktree con cambios previos [P2]

El comando ordena detener todo ante cualquier cambio ajeno. En esta revisión había 56 entradas en `git status --porcelain` al contar, incluidas pruebas y UI en desarrollo.

**Acción:** distinguir lectura, archivos nuevos sin colisión y edición de archivos ya modificados. Capturar baseline; conservar cambios; pedir resolución solo ante superposición real. No hacer stash/reset/pull automáticamente sobre trabajo del usuario. El prefijo de rama debe respetar el entorno y la instrucción activa.

### O05 — Puerta de calidad obligatoria [P1]

El paso «Implementar» del comando no define por sí solo una puerta explícita de pruebas antes de declarar cierre; las plantillas de auditoría la agregan después.

**Acción:** integrar verificación en cada tarea de producto: permiso, empresa, validación, prueba de error, prueba de éxito, docs y resultado. Reservar auditoría integral y CI completa para hitos; no ejecutar toda la suite por cada frase documental.

### O06 — Comprobaciones documentales incompletas [P2, confirmado]

`check-skill-references.cjs:13` recorre `.cursor`, `docs`, AGENTS y README: **no recorre `portal/`**. Tampoco valida destinos `data-portal-page`, fragmentos, copy-to-clipboard ni coherencia semántica. `check-agent-docs-sync` busca cadenas y cuenta 15 skills; su verde no prueba que el producto esté terminado.

**Acción:** incluir HTML y navegación del portal, anclas y pruebas de carga/copia. Verificar enlaces en el contexto real del servidor cuyo root es `portal/`. Añadir pruebas negativas a los verificadores: un enlace roto o tarea duplicada debe hacerlos fallar. No usar número fijo de skills como objetivo.

### O07 — Investigación con cierre [P2]

**Acción:** cada investigación tendrá pregunta, fuente oficial/versionada, fecha, conclusión, decisión y tarea afectada. Usar Context7 si está disponible y web oficial como alternativa. No repetir investigación cerrada ni usar un límite arbitrario de consultas para dejar una duda crítica sin resolver.

El agente puede investigar stack y fuentes públicas. Solo el cliente puede confirmar su flujo real, y la aceptación de formatos oficiales requiere evidencia normativa adecuada y validación del responsable.

## 6. Rendimiento y experiencia de uso

- **P2 — Resumen:** el bucle por galpón llama consultas de lotes, totales de hoy y acumulados. Existe memo de lotes dentro del request, pero los agregados se repiten; Inicio llama al pulso por más de una vía. Medir consultas y tiempo con 1/10/100 galpones; agrupar por galpón y compartir resultado por request si la medición lo justifica. No introducir cache global sin invalidación.
- **P2 — Fechas:** `whereDate(created_at)` y `DATE(created_at)` requieren revisar plan de ejecución e índices con volumen real. Preferir rangos de día cuando resulte beneficioso, conservando zona horaria y resultados.
- **P2 — Assets:** `resources/js/app.js` importa `client-map` globalmente aunque comercial es segunda etapa. Evaluar carga diferida/exclusión de mapa y subconjuntos de fuentes; el build emitió numerosas fuentes. Medir red real antes de atribuir lentitud.
- **P1 — Semántica simple:** mantener alimento «entregado», descarte de aves separado de huevos descartados, producción separada de stock y venta; no esconder incertidumbre con ceros.
- **P2 — Operario:** seleccionar galpón → acción → cantidad → guardar. Confirmación clara con cantidad/galpón, conservación de entrada ante error y modo sin conexión que no prometa guardado.
- **P2 — Dueño:** resumen y excepciones primero; equipo de solo lectura; detalles bajo demanda. No duplicar todas las pantallas solo porque cambia el prefijo del rol.
- **P2 — Accesibilidad:** comprobar teclado, foco, contraste, zoom, lectores de pantalla, formularios numéricos y reducción de movimiento. Los tests de cadenas HTML no sustituyen un navegador.

## 7. Investigación de producto antes del piloto

| Tema | Entregable | Bloquea |
|---|---|---|
| Galpones con varios lotes | Regla escrita de identificación de muertes/salidas y conciliación de saldo | Cierre/traslado parcial por lote |
| Registro cero y omisión | Definición por tipo y horario esperado | Alertas de faltantes |
| Facultades Dueño/Administrativo | Matriz de capacidades acordada y circuito sin bloqueo del dueño | Aceptación de roles |
| Datos iniciales | Plantilla de empresas, estructura, lotes y saldo al corte | Onboarding real |
| Planilla real de ponedoras | Ejemplo anonimizado y mapeo de columnas | Declarar export oficial |
| Conectividad y teléfonos | Prueba en granja con red representativa | Aceptar MVP online |
| Métricas biológicas | Fórmula, denominador, período y umbral con responsable | Alertas interpretativas |
| Operación y soporte | Responsable, respaldo, recuperación y horario acordado | Go-live |

No dar por confirmado que una planilla publicada como reproductoras sea aplicable sin cambios a ponedoras. La documentación del repo contiene esa decisión previa; esta revisión encontró la página oficial de formularios, pero no validó su equivalencia normativa ni el layout completo. Los reportes internos pueden avanzar.

## 8. Orden recomendado

1. Unificar contrato, actualizar estado y activar plan persistente.
2. Estabilizar roles, sesiones, aislamiento, reintentos y transiciones.
3. Completar aves/movimientos/correcciones/auditoría y administración de empresas.
4. Entregar resumen confiable y reportes Excel/PDF internos; certificar formatos externos solo con evidencia.
5. Optimizar con mediciones y validar móvil/PWA/recuperación.
6. Piloto con operario y dueño, correcciones, respaldo/restauración, capacitación y aceptación.

Tiempo real con Reverb: documentado tanto dentro como fuera del MVP. Propuesta: entregar consulta actualizable con estado de frescura; activar WebSockets únicamente si la aceptación exige actualización inmediata entre dispositivos. Resolver explícitamente el contrato, sin marcar Reverb implementado por tener dashboard.

## 9. Evidencia de verificación de esta revisión

| Comprobación | Resultado |
|---|---|
| `node scripts/check-agent-docs-sync.cjs` | OK, 15 skills |
| `node scripts/check-skill-references.cjs` | OK, 131 enlaces en 61 archivos; no incluye portal |
| `node scripts/check-cloud-readiness.cjs` | OK local; no verifica infraestructura desplegada ni restauración |
| `pnpm run build` | Exit 0; Vite 8.0.16 + PWA; precache 34 entradas, 823,83 KiB |
| Prueba aislada enum | Tres `UnhandledMatchError` para Reparto |
| `php artisan test --compact` | Ver actualización de resultado al final del informe |
| Navegador / carga / restauración | No ejecutados en esta revisión |

PHP local 8.3.30, Node local 24.14.0, pnpm 10.32.1. CI declara PHP 8.3 y Node 22; build local no prueba paridad con Node 22. No se revisó una ejecución remota de CI.

## 10. Fuentes oficiales consultadas

Consultadas el 2026-09-26; contrastar de nuevo al implementar si cambian versiones.

- F1: [Livewire 4 — Security](https://livewire.laravel.com/docs/4.x/security): autorización de acciones y middleware persistente personalizado.
- F2: [Laravel — Release notes](https://laravel.com/framework/docs/releases): requisitos de versión; Laravel 13 requiere PHP 8.3 como mínimo.
- F3: [MGAP — Procedimiento de trazabilidad 7.12](https://www.gub.uy/ministerio-ganaderia-agricultura-pesca/comunicacion/publicaciones/guia-buenas-practicas-establecimientos-avicolas-version-1-julio-2025-10): referencia para investigación de trazabilidad; no certificación del software.
- F4: [MGAP — Planillas de registros productivos](https://www.gub.uy/ministerio-ganaderia-agricultura-pesca/tramites-y-servicios/formularios/planillas-registro-productivos-para-pollos-parrilleros-para): confirmar formulario y aplicabilidad antes de etiquetar un export como oficial.

## 11. Entregable de ejecución

Continuar con [PLAN-MAESTRO-ENTREGA-AVICORE.md](PLAN-MAESTRO-ENTREGA-AVICORE.md). Ese documento propone trabajo futuro; no altera automáticamente las reglas de negocio vigentes. Los cambios de contrato deben implementarse en su referencia dueña y enlazarse desde el plan.

### Resultado final de la suite local

`php artisan test --compact` terminó con exit 1: **412 pruebas, 411 aprobadas, 1 fallida, 1.753 aserciones**, duración reportada 181.231 ms. Fallo: `Tests\Feature\Ui\AdminUserMenuTest::test_admin_home_renders_shared_user_menu_in_sidebar_and_home_nav`, `tests/Feature/Ui/AdminUserMenuTest.php:35`; esperaba «Resumen de Avícola Demo», ausente en el HTML recibido. Contrastar contrato visual antes de corregir aplicación o expectativa; no se modificó el test.

La salida mostró referencias a Vite local (puerto 5173): el plan incorpora aislar tests de ese servidor. Las 411 pruebas aprobadas no descartan las brechas estáticas ni sustituyen E2E, revisión visual o validación en granja.
