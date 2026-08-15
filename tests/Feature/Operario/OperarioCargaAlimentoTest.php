<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\RegistrarCargaAlimentoAction;
use App\Enums\EmpresaEstado;
use App\Enums\GalponEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Operario\CargaAlimento;
use App\Livewire\Operario\CargarHub;
use App\Livewire\Operario\Historial;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioCargaAlimentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_can_register_feed_delivery(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioAlimento')
            ->assertSet('dialogAlimentoAbierto', true)
            ->set('alimentoKg', '8500')
            ->call('guardarAlimento')
            ->assertSet('dialogAlimentoAbierto', false)
            ->assertDispatched('snackbar-show');

        $this->assertDatabaseHas('registros_operativos', [
            'empresa_id' => $operario->empresa_id,
            'galpon_id' => $galpon->id,
            'user_id' => $operario->id,
            'tipo' => RegistroOperativoTipo::Alimento->value,
            'alimento_kg' => 8500,
        ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->assertSee('8.500,00 kg entregados', false);
    }

    public function test_carga_alimento_deep_link_opens_dialog(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->withQueryParams(['form' => 'alimento'])
            ->test(CargarHub::class)
            ->assertSet('dialogAlimentoAbierto', true);

        $this->get(route('operario.carga.alimento'))
            ->assertRedirect(route('operario.cargar', ['form' => 'alimento']));
    }

    public function test_carga_alimento_redirects_without_galpon(): void
    {
        [$operario] = $this->createOperarioConGalpon();

        Livewire::actingAs($operario)
            ->test(CargaAlimento::class)
            ->assertRedirect(route('operario.cargar', ['abrir_galpon' => 1]));
    }

    public function test_registrar_carga_alimento_rejects_unavailable_galpon(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);
        $galpon->refresh();

        $this->expectException(ValidationException::class);

        app(RegistrarCargaAlimentoAction::class)->execute($operario, $galpon, 1000);
    }

    public function test_registrar_carga_alimento_rejects_galpon_from_other_empresa(): void
    {
        [$operario] = $this->createOperarioConGalpon();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();

        $this->expectException(AuthorizationException::class);

        app(RegistrarCargaAlimentoAction::class)->execute($operario, $galponAjeno, 1000);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function createOperarioConGalpon(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        return [$operario, $galpon];
    }
}
