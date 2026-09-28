<?php

namespace Tests\Feature\Operario;

use App\Enums\EmpresaEstado;
use App\Enums\GalponEstado;
use App\Enums\UserRole;
use App\Livewire\Operario\CargarHub;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioFormulariosObsoletosCap13Test extends TestCase
{
    use RefreshDatabase;

    public function test_changing_galpon_with_open_dialog_resets_form_and_shows_warning(): void
    {
        [$operario, $galponA, $galponB] = $this->createOperarioConDosGalpones();
        $operario->forceFill(['ultimo_galpon_id' => $galponA->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '150')
            ->set('huevosDescarte', '5')
            ->call('seleccionarGalpon', $galponB->id)
            ->assertSet('dialogHuevosAbierto', false)
            ->assertSet('huevos', '')
            ->assertSet('huevosDescarte', '0')
            ->assertDispatched('snackbar-show', message: 'Cambió el galpón: reiniciamos el formulario abierto.', variant: 'warning');
    }

    public function test_galpon_unavailable_on_hydrate_closes_open_capture_dialog(): void
    {
        [$operario, $galponA] = $this->createOperarioConDosGalpones();
        $operario->forceFill(['ultimo_galpon_id' => $galponA->id])->save();

        $component = Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioMuertes')
            ->set('muertes', '3')
            ->assertSet('dialogMuertesAbierto', true);

        $galponA->update(['estado' => GalponEstado::EnMantenimiento]);

        $component
            ->call('$refresh')
            ->assertSet('galponId', null)
            ->assertSet('dialogMuertesAbierto', false)
            ->assertSet('muertes', '')
            ->assertDispatched('snackbar-show', message: 'El galpón ya no está disponible: reiniciamos el formulario abierto.', variant: 'warning');
    }

    public function test_role_change_on_hydrate_closes_lote_dialog(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->conLoteActivo()->create();

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
            'ultimo_galpon_id' => $galpon->id,
        ]);

        $component = Livewire::actingAs($encargado)
            ->test(CargarHub::class)
            ->call('abrirFormularioLote')
            ->set('tipoBlanco', true)
            ->set('cantidadBlanco', '500')
            ->assertSet('dialogLoteAbierto', true);

        $encargado->update(['rol' => UserRole::Operario]);

        $component
            ->call('$refresh')
            ->assertSet('dialogLoteAbierto', false)
            ->assertSet('tipoBlanco', false)
            ->assertSet('cantidadBlanco', '')
            ->assertDispatched('snackbar-show', message: 'Tu acceso cambió: reiniciamos el formulario abierto.', variant: 'warning');

        $this->assertSame(0, RegistroOperativo::query()->where('empresa_id', $empresa->id)->count());
    }

    public function test_guardar_after_context_change_does_not_persist_stale_data(): void
    {
        [$operario, $galponA, $galponB] = $this->createOperarioConDosGalpones();
        $operario->forceFill(['ultimo_galpon_id' => $galponA->id])->save();

        $component = Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->assertSet('capturaContextoGalponId', $galponA->id)
            ->set('huevos', '90')
            ->set('huevosDescarte', '0');

        $operario->forceFill(['ultimo_galpon_id' => $galponB->id])->save();

        $component
            ->call('guardarHuevos')
            ->assertSet('dialogHuevosAbierto', false)
            ->assertDispatched('snackbar-show', variant: 'warning');

        $this->assertSame(0, RegistroOperativo::query()->where('empresa_id', $operario->empresa_id)->count());
    }

    /**
     * @return array{0: User, 1: Galpon, 2?: Galpon}
     */
    private function createOperarioConDosGalpones(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Granja CAP-13',
        ]);

        $galponA = Galpon::factory()->forGranja($granja)->conLoteActivo()->create([
            'nombre' => 'Galpón CAP-13 A',
            'codigo' => 'C13-A',
        ]);

        $galponB = Galpon::factory()->forGranja($granja)->conLoteActivo()->create([
            'nombre' => 'Galpón CAP-13 B',
            'codigo' => 'C13-B',
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        return [$operario, $galponA, $galponB];
    }
}
