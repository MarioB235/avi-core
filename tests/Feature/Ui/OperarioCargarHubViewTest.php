<?php

namespace Tests\Feature\Ui;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperarioCargarHubViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_cargar_hub_opens_huevos_dialog_via_http_query_param(): void
    {
        [$operario, $galpon] = $this->createUsuarioConGalpon(UserRole::Operario);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $this->actingAs($operario)
            ->get(route('operario.cargar', ['form' => 'huevos']))
            ->assertOk()
            ->assertSee('Huevos de hoy', false)
            ->assertSee('Huevos aptos (comerciales)', false)
            ->assertSee('wire:model="huevos"', false);
    }

    public function test_cargar_hub_opens_muertes_dialog_via_http_query_param(): void
    {
        [$operario, $galpon] = $this->createUsuarioConGalpon(UserRole::Operario);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $this->actingAs($operario)
            ->get(route('operario.cargar', ['form' => 'muertes']))
            ->assertOk()
            ->assertSee('Muertes de hoy', false)
            ->assertSee('¿Cuántas aves murieron?', false)
            ->assertSee('wire:model="muertes"', false);
    }

    public function test_cargar_hub_without_galpon_opens_selector_via_http_abrir_galpon(): void
    {
        $operario = $this->createUsuarioSinGalpon(UserRole::Operario);

        $this->actingAs($operario)
            ->get(route('operario.cargar', ['abrir_galpon' => 1]))
            ->assertOk()
            ->assertSee('operario-galpon-listbox', false)
            ->assertSee('avicore-operario-galpon-selector', false)
            ->assertDontSee('Huevos de hoy', false);
    }

    public function test_encargado_can_open_cargar_hub_with_huevos_dialog_via_http(): void
    {
        [$encargado, $galpon] = $this->createUsuarioConGalpon(UserRole::Encargado);
        $encargado->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $this->actingAs($encargado)
            ->get(route('operario.cargar', ['form' => 'huevos']))
            ->assertOk()
            ->assertSee('avicore-operario-cargar', false)
            ->assertSee('Huevos de hoy', false);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function createUsuarioConGalpon(UserRole $rol): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        $usuario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => $rol,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        return [$usuario, $galpon];
    }

    private function createUsuarioSinGalpon(UserRole $rol): User
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        Galpon::factory()->forGranja($granja)->create();

        return User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => $rol,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);
    }
}
