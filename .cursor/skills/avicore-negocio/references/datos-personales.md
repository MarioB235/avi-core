# Datos personales (EMP-08)

**Alcance:** política operativa MVP verificable en código y docs. **No** constituye asesoramiento legal ni certificación de cumplimiento normativo (Ley 18.331 u otras).

## 1. Inventario

| Categoría | Tabla / origen | Datos | Finalidad operativa |
|-----------|----------------|-------|---------------------|
| Identidad usuario | `users` | nombre, documento, correo, rol, empresa | autenticación, autorización, contacto laboral |
| Sesión web | `sessions` | id usuario, IP, user agent | seguridad y revocación (SEG-10) |
| Soporte auditado | `soporte_sesiones` | actor, empresa, motivo, acciones | trazabilidad Admin AviCore (EMP-06/07) |
| Carga operativa | `registros_operativos`, `vacunaciones` | `user_id`, timestamps | quién registró cada operación |
| Perfil propio | `/perfil`, `/operario/perfil` | nombre, correo editables | autogestión limitada (reglas §2.14) |

Exportaciones PDF/Excel (REP): deben reutilizar `DatosPersonales::documentoParaVista()` cuando incluyan documento; hoy no hay export con documento en MVP.

## 2. Acceso

| Rol | Documento completo de terceros |
|-----|--------------------------------|
| Propio usuario | Sí (perfil, menú cuenta) |
| Administrativo / Admin Avicore (módulo Usuarios) | Sí, misma empresa o multiempresa admin |
| Dueño / Encargado (módulo Equipo, solo lectura) | **No** — enmascarado (`x-ui.documento-label`) |
| Operario | No ve listados de equipo |

Implementación: `App\Support\DatosPersonales` + `config/avicore.php` → `datos_personales.documento_visible_digitos` (default 3).

## 3. Retención (acordada, pendiente OPS/AUD)

| Dato | Criterio MVP |
|------|----------------|
| Usuarios activos/inactivos | Mientras dure la relación contractual; backups según OPS-06 / D06 |
| Historial operativo y anulaciones | Sin borrado físico; retención y corrección según D07 (AUD-08) |
| Sesiones de soporte | Registro en `soporte_sesiones` + `acciones`; sin PII adicional en logs de aplicación |

## 4. Minimización en UI

- Equipo (dueño): documento enmascarado (`•••••678`).
- Usuarios (gestión): documento completo para quien puede administrar usuarios.
- Login y formularios de alta: documento como dato de entrada, no listado público.
- Recuperación de acceso: mensajes genéricos sin exponer documentos de otros usuarios.

## 5. Verificación

```bash
php artisan test --compact tests/Unit/Support/DatosPersonalesTest.php tests/Feature/Ui/DocumentoLabelComponentTest.php tests/Feature/Admin/AdminEquipoTest.php
```
