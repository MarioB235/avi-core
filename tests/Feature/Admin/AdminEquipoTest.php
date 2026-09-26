<?php

namespace Tests\Feature\Admin;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Livewire\Admin\Equipo\Index as EquipoIndex;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminEquipoTest extends TestCase
{
    use RefreshDatabase;

    public function test_equipo_filter_limits_visible_members(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'activo' => true,
            'must_change_password' => false,
            'name' => 'Dueño Demo',
        ]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
            'name' => 'Operario Campo',
        ]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'activo' => true,
            'must_change_password' => false,
            'name' => 'Encargado Super',
        ]);

        Livewire::actingAs($dueno)
            ->test(EquipoIndex::class)
            ->call('filtrarEquipo', 'campo')
            ->assertSet('filtroSegmento', 'campo')
            ->assertSee('Operario Campo')
            ->assertDontSee('Encargado Super');
    }

    public function test_encargado_cannot_access_equipo_module(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $this->actingAs($encargado)
            ->get(route('encargado.equipo.index'))
            ->assertForbidden();
    }

    public function test_equipo_list_scopes_members_to_own_company(): void
    {
        $empresaA = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresaA->id,
            'rol' => UserRole::Dueno,
            'activo' => true,
            'must_change_password' => false,
            'name' => 'Dueño Empresa A',
        ]);

        User::factory()->create([
            'empresa_id' => $empresaA->id,
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
            'name' => 'Operario Empresa A',
        ]);

        User::factory()->create([
            'empresa_id' => $empresaB->id,
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
            'name' => 'Operario Empresa B',
        ]);

        $this->actingAs($dueno)
            ->get(route('dueno.equipo.index'))
            ->assertOk()
            ->assertSee('Operario Empresa A')
            ->assertDontSee('Operario Empresa B');
    }
}
