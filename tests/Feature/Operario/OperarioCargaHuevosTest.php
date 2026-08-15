<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\RegistrarCargaHuevosAction;
use App\Enums\EmpresaEstado;
use App\Enums\GalponEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Operario\CargaHuevos;
use App\Livewire\Operario\CargarHub;
use App\Livewire\Operario\Historial;
use App\Livewire\Operario\Home;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioCargaHuevosTest extends TestCase
{
    use RefreshDatabase;

    public function test_guardar_huevos_cierra_dialogo_y_muestra_snackbar(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpones();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '100')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos')
            ->assertSet('dialogHuevosAbierto', false)
            ->assertDispatched('snackbar-show', message: 'Huevos guardados.', variant: 'success')
            ->assertDontSee('¿Querés registrar otra del mismo tipo?', false);
    }

    public function test_operario_can_select_galpon_register_eggs_and_see_today_loads(): void
    {
        [$operario, $galponA, $galponB] = $this->createOperarioConGalpones();

        $this->actingAs($operario)
            ->get(route('operario.home'))
            ->assertOk()
            ->assertSee('Sin seleccionar');

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->call('seleccionarGalpon', $galponA->id)
            ->assertHasNoErrors();

        $operario->refresh();
        $this->assertSame($galponA->id, $operario->ultimo_galpon_id);

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->assertSet('dialogHuevosAbierto', true)
            ->set('huevos', '1500')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos')
            ->assertSet('dialogHuevosAbierto', false)
            ->assertDispatched('snackbar-show');

        $this->assertDatabaseHas('registros_operativos', [
            'empresa_id' => $operario->empresa_id,
            'galpon_id' => $galponA->id,
            'user_id' => $operario->id,
            'tipo' => RegistroOperativoTipo::Huevos->value,
            'huevos' => 1500,
            'observacion' => null,
        ]);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee('1.500', false)
            ->assertSee('Aptos hoy', false)
            ->assertSee($galponA->displayName());

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->assertSee('1.500 huevos aptos');

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->assertSee('Huevos')
            ->assertSee($galponA->displayName());

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->call('seleccionarGalpon', $galponB->id)
            ->assertHasNoErrors();

        $operario->refresh();
        $this->assertSame($galponB->id, $operario->ultimo_galpon_id);
    }

    public function test_operario_cannot_see_galpones_from_other_empresa(): void
    {
        [$operario] = $this->createOperarioConGalpones();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertDontSee($galponAjeno->displayName());

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->call('seleccionarGalpon', $galponAjeno->id)
            ->assertHasErrors(['galponId']);
    }

    public function test_dueno_can_access_operario_cargar_but_not_admin_as_operario(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get(route('operario.cargar'))
            ->assertOk();

        $this->actingAs($dueno)
            ->get(route('admin.home'))
            ->assertOk();
    }

    public function test_carga_huevos_redirects_to_selector_when_no_galpon_selected(): void
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

        Livewire::actingAs($operario)
            ->test(CargaHuevos::class)
            ->assertRedirect(route('operario.cargar', ['abrir_galpon' => 1]));

        Livewire::actingAs($operario)
            ->withQueryParams(['abrir_galpon' => '1'])
            ->test(CargarHub::class)
            ->assertSet('selectorGalponAbierto', true);
    }

    public function test_home_opens_selector_with_abrir_galpon_query_param(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        Livewire::actingAs($operario)
            ->withQueryParams(['abrir_galpon' => '1'])
            ->test(Home::class)
            ->assertSet('selectorGalponAbierto', true);
    }

    public function test_home_opens_selector_via_http_after_carga_redirect_flash(): void
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

        $this->actingAs($operario)
            ->withSession(['abrirSelectorGalpon' => true])
            ->get(route('operario.home'))
            ->assertOk()
            ->assertSee('operario-galpon-listbox', false)
            ->assertSee('avicore-operario-galpon-selector', false);
    }

    public function test_carga_huevos_route_redirects_to_cargar_sheet_when_galpon_selected(): void
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
            ->test(CargaHuevos::class)
            ->assertRedirect(route('operario.cargar', ['form' => 'huevos']));
    }

    public function test_cargar_hub_opens_huevos_dialog_via_query_param(): void
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
            ->withQueryParams(['form' => 'huevos'])
            ->test(CargarHub::class)
            ->assertSet('dialogHuevosAbierto', true);
    }

    public function test_abrir_formulario_huevos_sin_galpon_abre_selector(): void
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

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->assertSet('selectorGalponAbierto', true)
            ->assertSet('dialogHuevosAbierto', false);
    }

    public function test_carga_huevos_rejects_zero_quantity(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpones();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '0')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos')
            ->assertHasErrors(['huevos']);

        $this->assertSame(0, RegistroOperativo::query()->count());
    }

    public function test_selector_rejects_unavailable_galpon(): void
    {
        [$operario, , , $granja] = $this->createOperarioConGalpones();

        $galponMantenimiento = Galpon::factory()->forGranja($granja)->create([
            'nombre' => 'Galpón en mantenimiento',
            'estado' => GalponEstado::EnMantenimiento,
        ]);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertDontSee($galponMantenimiento->displayName());

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->call('seleccionarGalpon', $galponMantenimiento->id)
            ->assertHasErrors(['galponId']);
    }

    public function test_carga_huevos_redirects_when_galpon_became_unavailable(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpones();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);

        Livewire::actingAs($operario)
            ->test(CargaHuevos::class)
            ->assertRedirect(route('operario.cargar', ['abrir_galpon' => 1]));
    }

    public function test_registrar_carga_huevos_action_rejects_unavailable_galpon(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpones();
        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);
        $galpon->refresh();

        $this->expectException(ValidationException::class);

        app(RegistrarCargaHuevosAction::class)->execute($operario, $galpon, 100);
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon, 3: Granja}
     */
    private function createOperarioConGalpones(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);

        $galponA = Galpon::factory()->forGranja($granja)->create([
            'nombre' => 'Galpón A',
            'codigo' => 'GA',
        ]);

        $galponB = Galpon::factory()->forGranja($granja)->create([
            'nombre' => 'Galpón B',
            'codigo' => 'GB',
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        return [$operario, $galponA, $galponB, $granja];
    }
}
