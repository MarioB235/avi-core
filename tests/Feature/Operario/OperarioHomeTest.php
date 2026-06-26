<?php

namespace Tests\Feature\Operario;

use App\Enums\EmpresaEstado;
use App\Enums\GalponEstado;
use App\Enums\UserRole;
use App\Livewire\Operario\Home;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_rejects_galpon_from_other_empresa(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        Galpon::factory()->forGranja($granja)->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->call('seleccionarGalpon', $galponAjeno->id)
            ->assertHasErrors(['galponId']);

        $operario->refresh();
        $this->assertNull($operario->ultimo_galpon_id);
    }

    public function test_home_rejects_galpon_in_maintenance(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);

        $galponMantenimiento = Galpon::factory()->forGranja($granja)->create([
            'estado' => GalponEstado::EnMantenimiento,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->call('seleccionarGalpon', $galponMantenimiento->id)
            ->assertHasErrors(['galponId']);

        $operario->refresh();
        $this->assertNull($operario->ultimo_galpon_id);
    }

    public function test_home_rejects_inactive_galpon(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);

        $galponInactivo = Galpon::factory()->forGranja($granja)->create([
            'activo' => false,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->call('seleccionarGalpon', $galponInactivo->id)
            ->assertHasErrors(['galponId']);

        $operario->refresh();
        $this->assertNull($operario->ultimo_galpon_id);
    }
}
