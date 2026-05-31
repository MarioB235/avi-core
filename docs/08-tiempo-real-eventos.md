# 08 — Tiempo real y eventos

## 1. Objetivo

Definir qué eventos en tiempo real tendrá AviCore y qué partes del sistema deben actualizar.

---

## 2. Tecnologías

- Laravel Reverb.
- Laravel Echo.
- Events de Laravel.
- Canales privados por empresa.

---

## 3. Regla principal

Todo evento en tiempo real debe respetar empresa_id.

Un usuario de una empresa no puede recibir eventos de otra empresa.

---

## 4. Canales sugeridos

```text
empresa.{empresa_id}
granja.{granja_id}
galpon.{galpon_id}
dashboard.empresa.{empresa_id}
```

---

## 5. Eventos iniciales

| Evento | Cuándo ocurre | Actualiza |
|---|---|---|
| RegistroOperativoCreado | Se guarda carga de huevos, muertes o alimento | Dashboard, últimas cargas, alertas |
| RegistroAnulado | Se anula un registro | Dashboard, auditoría, alertas |
| RegistroCorregido | Se corrige un registro | Dashboard, auditoría |
| AvesVivasAjustadas | Se ajustan aves vivas | Dashboard, alertas |
| LoteCerrado | Se cierra un lote | Lotes, dashboard |
| SalidaParcialRegistrada | Salen aves del galpón | Aves vivas, dashboard |
| AlertaGenerada | Se detecta una alerta | Panel de alertas |
| AlertaRevisada | Un usuario marca alerta como revisada | Panel de alertas |

---

## 6. Flujo técnico

```text
Operario carga huevos desde celular
        ↓
Livewire valida y ejecuta acción
        ↓
Laravel guarda registro
        ↓
Laravel recalcula indicadores
        ↓
Laravel dispara evento
        ↓
Reverb transmite por WebSocket
        ↓
Echo recibe en navegador
        ↓
Dashboard se actualiza sin recargar
```

---

## 7. Eventos por carga

### Huevos

Evento:

```text
RegistroOperativoCreado
```

Payload mínimo:

- empresa_id.
- galpon_id.
- tipo = huevos.
- cantidad.
- fecha_hora.
- usuario.

### Muertes

Payload:

- empresa_id.
- galpon_id.
- tipo = muertes.
- cantidad.
- aves_vivas_actuales.
- usuario.

### Alimento

Payload:

- empresa_id.
- galpon_id.
- tipo = alimento.
- kilos.
- usuario.

---

## 8. Seguridad

Usar canales privados.

Validar:

- Usuario autenticado.
- Usuario pertenece a empresa.
- Usuario tiene permiso para escuchar ese canal.

---

## 9. Recomendación

Comenzar con eventos para:

1. Registro creado.
2. Registro anulado.
3. Alerta generada.

Luego agregar eventos más específicos.
