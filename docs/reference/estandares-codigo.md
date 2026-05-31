# Referencia — Estándares de código AviCore

**Fuente maestra** para revisión y auditoría de código. Complementa `07-arquitectura-tecnica.md` y `03-guia-visual-ui.md`.

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
- Diseño responsivo; operario con botones grandes y áreas táctiles (ver `03-guia-visual-ui.md`).

## JavaScript / Alpine

- Alpine solo para interacciones pequeñas; no reimplementar lógica de negocio.
- Echo/Reverb según `08-tiempo-real-eventos.md`; canales privados y `empresa_id`.

## Librerías y dependencias

- Usar APIs **oficiales** de Laravel, Livewire, Tailwind y paquetes ya en el stack; no alternativas ad hoc.
- Consultar documentación vigente (Context7 MCP) ante dudas de versión o API.
- No fijar versiones en docs funcionales; en código respetar `composer.lock` / `package-lock.json`.
- No agregar dependencias nuevas sin justificación en la tarea.

## Git y PR

- Conventional Commits: `tipo(scope): descripción`.
- No commitear `.env`, credenciales ni artefactos generados innecesarios.

## Auditoría

Al auditar, contrastar cada archivo contra este documento y la documentación maestra del módulo (`05`, `06`, `02`, etc.).
