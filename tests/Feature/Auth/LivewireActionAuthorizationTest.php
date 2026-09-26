<?php

namespace Tests\Feature\Auth;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Livewire\Admin\Comercial\Index as ComercialIndex;
use App\Livewire\Admin\Equipo\Index as EquipoIndex;
use App\Livewire\Admin\Resumen\Index as ResumenIndex;
use App\Livewire\Admin\Usuarios\Index as UsuariosIndex;
use App\Livewire\Operario\CargarHub;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireActionAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_cannot_mount_admin_resumen_component(): void
    {
        $operario = $this->createUser(UserRole::Operario);

        Livewire::actingAs($operario)
            ->test(ResumenIndex::class)
            ->assertForbidden();
    }

    public function test_encargado_cannot_mount_dueno_equipo_component(): void
    {
        $encargado = $this->createUser(UserRole::Encargado);

        Livewire::actingAs($encargado)
            ->test(EquipoIndex::class)
            ->assertForbidden();
    }

    public function test_administrativo_cannot_mount_dueno_comercial_component(): void
    {
        $administrativo = $this->createUser(UserRole::Administrativo);

        Livewire::actingAs($administrativo)
            ->test(ComercialIndex::class)
            ->assertForbidden();
    }

    public function test_operario_cannot_mount_usuarios_component(): void
    {
        $operario = $this->createUser(UserRole::Operario);

        Livewire::actingAs($operario)
            ->test(UsuariosIndex::class)
            ->assertForbidden();
    }

    public function test_resumen_livewire_update_denied_after_role_downgrade(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        Galpon::factory()->forGranja($granja)->create();

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $component = Livewire::actingAs($encargado)
            ->test(ResumenIndex::class);

        $encargado->update(['rol' => UserRole::Operario]);

        $component
            ->set('filtroGranjaId', (string) $granja->id)
            ->assertForbidden();
    }

    public function test_operario_guardar_lote_livewire_action_is_denied(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => $galpon->id,
        ]);

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('guardarLote')
            ->assertForbidden();
    }

    private function createUser(UserRole $rol): User
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        return User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => $rol,
            'must_change_password' => false,
        ]);
    }
}
