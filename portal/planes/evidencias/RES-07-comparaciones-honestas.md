# RES-07 — Comparaciones honestas (pulso Inicio)

**ID:** RES-07 · **Estado:** VERIFICADA  
**Fecha:** 2026-09-29 · **Rama:** `fix/demo-login-seed-readiness`

## Objetivo

Evitar % engañoso hoy vs ayer: día D03 incompleto, sin base de ayer o sin huevos en ambos días.

## Resultado observable

- `delta_huevos_pct` solo con ayer > 0 y capturas D03 cerradas en todos los galpones del pulso.
- `delta_huevos_pct_motivo` expone `dia_en_curso`, `sin_base_ayer` o `sin_huevos_ambos`.
- Copy Inicio: «día en curso; % al cerrar capturas» cuando aplica.

## Archivos

- `app/Support/ComparacionHonestaPulso.php`
- `app/Services/AdminResumenService.php`, `AdminHomeService.php`
- Tests: `ComparacionHonestaPulsoTest`, `AdminResumenServiceTest` (RES-07)
- Contrato: `reglas.md` §23, `metricas-resumen.md`

## Verificación

```bash
php artisan test tests/Unit/Support/ComparacionHonestaPulsoTest.php tests/Feature/Services/AdminResumenServiceTest.php
php artisan test
```

**Resultado 2026-09-29:** 994/994 · Pint OK · `check:agent-docs` OK.

## Siguiente ID

RES-08 — gráficos útiles (postura semanal / ausencia vs cero).
