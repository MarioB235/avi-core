<?php

namespace Tests\Unit\Support;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use App\Support\AdminNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePanelModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_nav_tabs_match_role_modules(): void
    {
        $empresa = Empresa::factory()->create();

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
        ]);

        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
        ]);

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
        ]);

        $adminAvicore = User::factory()->adminAvicore()->create();

        $this->assertSame(
            ['Inicio', 'Resumen', 'Historial', 'Auditoría', 'Movimientos', 'Equipo'],
            collect(AdminNav::tabs($dueno))->pluck('label')->all(),
        );

        $this->assertSame(
            ['Inicio', 'Resumen', 'Historial', 'Auditoría', 'Movimientos', 'Estructura', 'Usuarios'],
            collect(AdminNav::tabs($administrativo))->pluck('label')->all(),
        );

        $this->assertSame(
            ['Inicio', 'Resumen', 'Historial', 'Auditoría', 'Movimientos', 'Estructura', 'Usuarios'],
            collect(AdminNav::tabs($encargado))->pluck('label')->all(),
        );

        $this->assertSame(
            ['Inicio', 'Empresas', 'Usuarios'],
            collect(AdminNav::tabs($adminAvicore))->pluck('label')->all(),
        );
    }
}
