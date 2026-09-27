<?php

namespace Tests\Feature\Operario;

use App\Enums\EmpresaEstado;
use App\Enums\GalponEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Operario\CargarHub;
use App\Livewire\Operario\Historial;
use App\Livewire\Operario\Home;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioGalponSelectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_remembered_galpon_is_restored_on_mount(): void
    {
        [$operario, $galponA] = $this->createOperarioConDosGalpones();
        $operario->forceFill(['ultimo_galpon_id' => $galponA->id])->save();

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSet('galponId', $galponA->id)
            ->assertSee($galponA->displayName())
            ->assertSee($galponA->granja->nombre);
    }

    public function test_unavailable_remembered_galpon_shows_sin_seleccionar(): void
    {
        [$operario, $galponA] = $this->createOperarioConDosGalpones();
        $operario->forceFill(['ultimo_galpon_id' => $galponA->id])->save();
        $galponA->update(['estado' => GalponEstado::EnMantenimiento]);

        Livewire::actingAs($operario->fresh())
            ->test(Home::class)
            ->assertSet('galponId', null)
            ->assertSee('Sin seleccionar');
    }

    public function test_guardar_after_changing_galpon_saves_to_new_galpon_not_previous(): void
    {
        [$operario, $galponA, $galponB] = $this->createOperarioConDosGalpones();
        $operario->forceFill(['ultimo_galpon_id' => $galponA->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '120')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos')
            ->call('seleccionarGalpon', $galponB->id)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '80')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos');

        $this->assertDatabaseHas('registros_operativos', [
            'galpon_id' => $galponA->id,
            'huevos' => 120,
            'tipo' => RegistroOperativoTipo::Huevos->value,
        ]);

        $this->assertDatabaseHas('registros_operativos', [
            'galpon_id' => $galponB->id,
            'huevos' => 80,
            'tipo' => RegistroOperativoTipo::Huevos->value,
        ]);

        $this->assertSame(1, RegistroOperativo::query()->where('galpon_id', $galponA->id)->count());
        $this->assertSame(1, RegistroOperativo::query()->where('galpon_id', $galponB->id)->count());
    }

    public function test_guardar_with_open_dialog_after_changing_galpon_uses_new_galpon(): void
    {
        [$operario, $galponA, $galponB] = $this->createOperarioConDosGalpones();
        $operario->forceFill(['ultimo_galpon_id' => $galponA->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->assertSet('dialogHuevosAbierto', true)
            ->call('seleccionarGalpon', $galponB->id)
            ->set('huevos', '200')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos');

        $this->assertDatabaseHas('registros_operativos', [
            'galpon_id' => $galponB->id,
            'huevos' => 200,
        ]);

        $this->assertDatabaseMissing('registros_operativos', [
            'galpon_id' => $galponA->id,
            'huevos' => 200,
        ]);
    }

    public function test_hydrate_clears_stale_galpon_id_when_galpon_becomes_unavailable(): void
    {
        [$operario, $galponA] = $this->createOperarioConDosGalpones();
        $operario->forceFill(['ultimo_galpon_id' => $galponA->id])->save();

        $component = Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->assertSet('galponId', $galponA->id);

        $galponA->update(['estado' => GalponEstado::EnMantenimiento]);

        $component
            ->call('$refresh')
            ->assertSet('galponId', null);
    }

    public function test_galpon_context_visible_on_home_cargar_and_historial(): void
    {
        [$operario, $galponA] = $this->createOperarioConDosGalpones();
        $operario->forceFill(['ultimo_galpon_id' => $galponA->id])->save();

        $granjaNombre = $galponA->granja->nombre;

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee($granjaNombre);

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->assertSee($granjaNombre);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->assertSee($granjaNombre);
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon}
     */
    private function createOperarioConDosGalpones(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Granja selector CAP-01',
        ]);

        $galponA = Galpon::factory()->forGranja($granja)->conLoteActivo()->create([
            'nombre' => 'Galpón selector A',
            'codigo' => 'SEL-A',
        ]);

        $galponB = Galpon::factory()->forGranja($granja)->conLoteActivo()->create([
            'nombre' => 'Galpón selector B',
            'codigo' => 'SEL-B',
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
