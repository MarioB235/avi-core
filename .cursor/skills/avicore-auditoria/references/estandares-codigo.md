# Referencia — Estándares de código AviCore

**Fuente maestra** para revisión y auditoría de código. Complementa `avicore-contexto/references/arquitectura.md` y `avicore-design-system/references/guia-visual.md`.

---

## PHP / Laravel

- Lógica de negocio en **Services** o **Actions**, no en Blade ni componentes Livewire voluminosos.
- Validaciones en Form Requests o reglas Livewire explícitas.
- Permisos con **Policies/Gates**; no solo ocultar botones en vista.
- Consultas operativas con **`empresa_id`** (scope o middleware).
- Anulación lógica con motivo; no `delete()` en registros operativos.
- Nombres en inglés para código (clases, métodos); textos de UI en español.
- Tipado y return types donde el proyecto ya los use.
- No silenciar excepciones (`catch` vacío).

## Livewire / Blade

- Componentes acotados; extraer lógica repetida a métodos o Actions.
- Sin lógica de negocio compleja en la vista.
- Estados de carga, error y vacío definidos.

## CSS / Tailwind

- **Solo Tailwind**; no CSS custom salvo tokens globales justificados en `resources/css`.
- **Prohibido `!important`** salvo excepción documentada en el PR (último recurso).
- **Sin estilos inline** en Blade/HTML (`style="..."`).
- Reutilizar componentes y clases del proyecto; no duplicar utilidades arbitrarias.
- Diseño responsivo; operario con botones grandes y áreas táctiles (ver `avicore-ui/references/patrones-mobile-operario.md` y `tokens-componentes.md`).

## JavaScript / Alpine

- Alpine solo para interacciones pequeñas; no reimplementar lógica de negocio.
- Echo/Reverb según `avicore-tiempo-real/references/eventos.md`; canales privados y `empresa_id`.

## Librerías y dependencias

- Usar APIs **oficiales** de Laravel, Livewire, Tailwind y paquetes ya en el stack; no alternativas ad hoc.
- Consultar documentación vigente (Context7 MCP) ante dudas de versión o API.
- No fijar versiones en docs funcionales; en código respetar `composer.lock` / `package-lock.json`.
- No agregar dependencias nuevas sin justificación en la tarea.

## Tests (PHPUnit / Feature)

- Comportamiento crítico con **Feature tests** en `tests/Feature/` (auth, permisos, multiempresa, reglas de negocio, flujos en `avicore-ui/references/pantallas-flujos.md`).
- Componentes UI reutilizables (`x-ui.*`, `x-auth.*`, `x-operario.user-menu`): tests de renderizado/accesibilidad en `tests/Feature/Ui/` cuando el componente expone contrato estable (iconos, inputs, `x-ui.dialog`, `x-ui.sheet`, `support-contact-dialog`, menú cuenta operario, etc.) — p. ej. `DialogComponentTest`, `SheetComponentTest`, `LoginViewTest` (incl. demo `x-ui.select` + `role="listbox"`), `OperarioUserMenuTest`, `LogoComponentTest` (variante `entrance`), `PublicLayoutTest` (shell auth + órbita).
- Datos de configuración expuestos en UI (p. ej. contacto de soporte): validar en Service (`SupportContactService`), no confiar solo en Blade; tests Feature del service y del componente.
- Servicios de auth con ramas de error (`DemoLoginService::resolveUser`): tests Feature en `tests/Feature/Services/` y flujos Livewire en `tests/Feature/Auth/`.
- Servicios operarios con reglas de negocio (`OperarioGalponService`: `galponDisponibleParaUsuario`, `historialCargasQuery`, `historialPaginado`, multiempresa, galpón disponible; `OperarioGalponResumenService`: `resumen`, ventana de acumulado, maples): tests Feature en `tests/Feature/Services/OperarioGalponServiceTest.php`, `tests/Feature/Operario/OperarioHomeResumenTest.php`, `tests/Feature/Operario/OperarioHistorialTest.php` (tipos, vacunaciones mezcladas, filtro fecha validado, paginación, multiempresa) + `tests/Feature/Operario/OperarioCargaHuevosTest.php`, `tests/Feature/Operario/OperarioCargaMuertesTest.php` y `tests/Feature/Operario/OperarioCargaVacunacionTest.php` (flujo hub, Actions, multiempresa, galpón no disponible, deep link `?form=huevos|muertes|vacunacion`) + integración en `tests/Feature/Ui/OperarioBottomNavTest.php` (heroes, nav activa, historial empty/populated HTTP, ilustración `operario-reloj`, diálogos huevos/muertes/vacunación vía deep link, icono `calendar`) y `tests/Feature/Support/OperarioNavTest.php` (pestaña activa y `headerTitle` por ruta) + `tests/Feature/Operario/OperarioHomeTest.php` (`seleccionarGalpon` rechaza galpón ajeno, en mantenimiento o inactivo) + `tests/Feature/Ui/IllustrationComponentTest.php` (contrato `x-ui.illustration`, incl. `operario-vacuna`) + `tests/Feature/Ui/SelectComponentTest.php` (`x-ui.select` listbox, flip `--above`/`--below`, `syncPanelPosition`).
- Snackbar global (`x-ui.snackbar-host`): `tests/Feature/Ui/SnackbarHostTest.php` (layout operario, evento `snackbar-show`, flash `status`).
- Al auditar código de aplicación, revisar también el **test correspondiente** en `tests/` (o marcar gap si falta).
- Tests significativos: flujos reales, no asserts triviales. PostgreSQL vía `avicore_test` (ver `arranque-local.md`).
- Tras correcciones post-auditoría: `php artisan test` debe quedar en verde antes de la PR.

## Escalabilidad y mantenibilidad

- Capas claras (Actions/Services/Policies); sin acoplar UI a consultas pesadas.
- Consultas con scope `empresa_id`; evitar N+1 evidentes en listados.
- Componentes y CSS reutilizables; sin duplicar lógica entre admin y operario si puede compartirse.

## Simplificación y complejidad (Laravel-aware)

**Después** de cumplir negocio, permisos, multiempresa y tests, evaluar si el código puede simplificarse:

1. ¿Hace falta esta abstracción ahora? (YAGNI) — **excepciones AviCore:** Policy, FormRequest, Service/Action y tests Feature **no** son YAGNI por defecto.
2. ¿Laravel o el stack ya lo resuelve? (Eloquent, Collections, validación nativa, componentes Blade existentes).
3. ¿Una dependencia nueva es inevitable? Justificar en la tarea.

Atajos deliberados (MVP, rendimiento provisional): marcar en código con comentario `avicore-defer: <techo>, <disparador para revisar>`. Ledger: skill interno `avicore-deuda-tecnica`.

En **Brecha principal** de la auditoría (mensaje 2), tags opcionales de complejidad (solo si aplican; no sustituyen Negocio/Permisos):

| Tag | Uso |
|-----|-----|
| `yagni:` | Abstracción o capa sin segundo uso real |
| `shrink:` | Misma lógica con menos líneas (mostrar forma más corta) |
| `stdlib:` | Reinventar algo que Laravel/PHP ya ofrece |
| `delete:` | Código muerto o flexibilidad no usada |

Ejemplo: `yagni: Repository con una implementación. Usar Eloquent directo hasta segundo origen de datos.`

## Git y PR

- Conventional Commits: `tipo(scope): descripción`.
- No commitear `.env`, credenciales ni artefactos generados innecesarios.

## Auditoría

Al auditar, contrastar cada archivo contra este documento y las `references/` del skill dueño (`avicore-negocio`, `avicore-ui`, `avicore-modelo-datos`, `avicore-design-system`, `avicore-contexto`, según aplique).

**Dimensiones de la tabla (mensaje 2):** Negocio · Permisos · Código · UI · Tests · Arquitectura — cada una: OK / Parcial / No / N/A.

- **Tests:** para código de app, verificar par en `tests/`; brecha si falta cobertura de reglas críticas del archivo.
- **Arquitectura:** capas, multiempresa, Policies, escalabilidad según `avicore-contexto/references/arquitectura.md`.
