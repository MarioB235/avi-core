# BAS-02 / BAS-03 — Baseline verde

**Estado:** VERIFICADA · **Fecha:** 2026-09-26 · **Revisión:** `b2623ef` + cambios locales sin commit

## BAS-02 — Test menú admin

El test `AdminUserMenuTest::test_admin_home_renders_shared_user_menu_in_sidebar_and_home_nav` ya no espera el copy «Resumen de Avícola Demo»; valida menú compartido, build meta y ausencia de bloques obsoletos (`Accesos rápidos`, `Tu gente en AviCore`).

```bash
php artisan test --compact --filter=AdminUserMenuTest
# exit 0
```

## BAS-03 — Suite, Pint y build

| Comando | Resultado |
|---------|-----------|
| `php artisan test --compact` | Exit 0 · **418** tests · **418** OK · 1773 aserciones |
| `vendor/bin/pint --test` | Exit 0 |
| `pnpm run build` | Exit 0 |

## Nota

El diagnóstico inicial (412 tests, 1 fallida) quedó superado en el estado actual de la rama; no se modificó código en esta verificación.

## Siguiente ID sugerido

**SEG-01** (enum Reparto) o **ORQ-05** (alcance auditoría integral).
