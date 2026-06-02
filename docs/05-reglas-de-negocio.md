# 05 — Reglas de negocio

## 1. Multiempresa

1. Cada empresa ve solamente sus datos.
2. Toda tabla operativa debe tener empresa_id.
3. El Admin AviCore no accede libremente a datos productivos reales.
4. El modo soporte requiere motivo y auditoría.

---

## 2. Usuarios

1. El login se realiza con documento y contraseña.
2. El documento es único dentro de cada empresa (`empresa_id` + `documento`).
3. Admin AviCore (`empresa_id` null) tiene documento único a nivel global.
4. Los usuarios son creados por administrador o perfil autorizado.
5. Todo usuario nuevo puede tener contraseña temporal (`must_change_password`).
6. El primer ingreso exige cambio obligatorio de contraseña antes de usar el sistema.
7. La nueva contraseña debe cumplir política mínima: 8+ caracteres, letras, mayúsculas/minúsculas y números; no puede repetir la actual.
8. Tras 5 intentos fallidos de login por documento e IP en 60 segundos, se bloquea temporalmente el acceso.
9. Si un documento resuelve más de una cuenta activa con credenciales válidas, se rechaza el login (ambigüedad).
10. Usuario inactivo o empresa no activa impiden el acceso (Admin AviCore exceptuado de validación de empresa).
11. La recuperación de contraseña en MVP la realiza administrador o encargado autorizado. En login y cambio obligatorio de contraseña, el enlace «¿Olvidaste tu contraseña?» abre un diálogo con contacto de soporte (WhatsApp y/o correo desde `config/avicore.php` / `.env`, URLs validadas en `SupportContactService`); no hay reset automático por correo.

---

## 3. Galpones

1. La carga operativa se realiza por galpón.
2. El operario puede elegir cualquier galpón de su empresa.
3. El sistema recuerda el último galpón seleccionado.
4. Un galpón puede tener uno o varios lotes.
5. Si tiene varios lotes, se muestra aviso informativo.
6. El aviso no bloquea la carga.

---

## 4. Lotes

1. El lote conserva información histórica.
2. El lote puede trasladarse.
3. El lote cerrado no permite cargas normales.
4. La reapertura de lote cerrado requiere perfil superior y auditoría.
5. El tipo de huevo se define en el lote.
6. No se debe usar solamente fecha de nacimiento como identificador.

---

## 5. Producción

1. La producción se carga por galpón.
2. Si hay varios lotes en el galpón, la producción se asigna al galpón completo.
3. La unidad principal es el huevo.
4. 1 maple equivale a 30 huevos.
5. El cajón es configurable por empresa.
6. Los reportes del MVP muestran huevos.

---

## 6. Mortalidad

1. Las muertes se cargan por galpón.
2. Si hay varios lotes, la mortalidad se asigna al galpón completo.
3. Las muertes descuentan aves vivas.
4. No se permite que aves vivas quede negativo.

---

## 7. Alimento

1. El MVP no maneja stock de alimento.
2. Solo se registra alimento entregado.
3. La unidad es kilos.
4. Se permiten decimales.
5. El alimento puede cargarse sin huevos ni muertes.

---

## 8. Carga diaria

1. La carga es flexible y en tiempo real.
2. No existe cierre diario automático.
3. El operario no selecciona fecha ni hora.
4. Cada registro usa fecha y hora del momento.
5. Puede haber varias cargas del mismo galpón el mismo día.
6. Para guardar, debe existir al menos un dato cargado.

---

## 9. Anulación

1. Se usa “anular”, no eliminar.
2. El registro anulado no cuenta en cálculos.
3. El registro anulado queda en auditoría.
4. El operario solo anula registros propios del día.
5. Toda anulación requiere motivo obligatorio.

---

## 10. Corrección

1. Toda corrección requiere motivo.
2. Debe guardarse valor anterior y valor nuevo.
3. Encargado o superior puede corregir.
4. Las correcciones deben auditarse.

---

## 11. Aves vivas

1. Se calculan principalmente por galpón.
2. Se actualizan con muertes, salidas, traslados y ajustes.
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
