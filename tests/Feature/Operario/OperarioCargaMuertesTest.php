<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Enums\EmpresaEstado;
use App\Enums\GalponEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Operario\CargaMuertes;
use App\Livewire\Operario\CargarHub;
use App\Livewire\Operario\Historial;
use App\Livewire\Operario\Home;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioCargaMuertesTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_can_register_deaths_and_see_today_summary(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 5000);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioMuertes')
            ->assertSet('dialogMuertesAbierto', true)
            ->set('muertes', '8')
            ->call('guardarMuertes')
            ->assertSet('dialogMuertesAbierto', false)
            ->assertDispatched('snackbar-show');

        $this->assertDatabaseHas('registros_operativos', [
            'empresa_id' => $operario->empresa_id,
            'galpon_id' => $galpon->id,
            'user_id' => $operario->id,
            'tipo' => RegistroOperativoTipo::Muertes->value,
            'muertes' => 8,
            'observacion' => null,
        ]);

        $galpon->refresh();
        $this->assertSame(4992, $galpon->aves_actuales);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee('8', false)
            ->assertSee('Murieron hoy', false)
            ->assertSee('4.992', false);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->assertSee('8 muertes')
            ->assertSee('avicore-operario-historial-list__item--muertes', false);
    }

    public function test_carga_muertes_rejects_zero_quantity(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioMuertes')
            ->set('muertes', '0')
            ->call('guardarMuertes')
            ->assertHasErrors(['muertes']);

        $this->assertSame(0, RegistroOperativo::query()->count());
    }

    public function test_carga_muertes_rejects_when_exceeds_live_birds(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 10);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioMuertes')
            ->set('muertes', '11')
            ->call('guardarMuertes')
            ->assertHasErrors(['muertes']);

        $this->assertSame(0, RegistroOperativo::query()->count());
        $galpon->refresh();
        $this->assertSame(10, $galpon->aves_actuales);
    }

    public function test_carga_muertes_redirects_to_selector_when_no_galpon_selected(): void
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
            ->test(CargaMuertes::class)
            ->assertRedirect(route('operario.home'));

        $this->actingAs($operario);
        session(['abrirSelectorGalpon' => true]);

        Livewire::test(Home::class)
            ->assertSet('selectorGalponAbierto', true);
    }

    public function test_carga_muertes_route_redirects_to_cargar_sheet_when_galpon_selected(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargaMuertes::class)
            ->assertRedirect(route('operario.cargar', ['form' => 'muertes']));
    }

    public function test_cargar_hub_opens_muertes_dialog_via_query_param(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->withQueryParams(['form' => 'muertes'])
            ->test(CargarHub::class)
            ->assertSet('dialogMuertesAbierto', true);
    }

    public function test_abrir_formulario_muertes_sin_galpon_redirige_y_flashea_selector(): void
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
            ->call('abrirFormularioMuertes')
            ->assertRedirect(route('operario.home'))
            ->assertSessionHas('abrirSelectorGalpon', true);
    }

    public function test_registrar_carga_muertes_action_rejects_unavailable_galpon(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);
        $galpon->refresh();

        $this->expectException(ValidationException::class);

        app(RegistrarCargaMuertesAction::class)->execute($operario, $galpon, 5);
    }

    public function test_registrar_carga_muertes_action_rejects_exceeding_live_birds(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 3);

        $this->expectException(ValidationException::class);

        app(RegistrarCargaMuertesAction::class)->execute($operario, $galpon, 4);
    }

    public function test_registrar_carga_muertes_action_rejects_galpon_from_other_empresa(): void
    {
        [$operario] = $this->createOperarioConGalpon();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create([
            'aves_actuales' => 100,
        ]);

        $this->expectException(AuthorizationException::class);

        app(RegistrarCargaMuertesAction::class)->execute($operario, $galponAjeno, 5);
    }

    public function test_carga_muertes_redirects_when_galpon_became_unavailable(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);

        Livewire::actingAs($operario)
            ->test(CargaMuertes::class)
            ->assertRedirect(route('operario.home'));
    }

    public function test_guardar_muertes_redirects_when_galpon_became_unavailable(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $component = Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioMuertes')
            ->assertSet('dialogMuertesAbierto', true)
            ->set('muertes', '3');

        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);

        $component
            ->call('guardarMuertes')
            ->assertRedirect(route('operario.home'))
            ->assertSessionHas('abrirSelectorGalpon', true);

        $this->assertSame(0, RegistroOperativo::query()->count());
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function createOperarioConGalpon(int $avesActuales = 5000): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);

        $galpon = Galpon::factory()->forGranja($granja)->create([
            'nombre' => 'Galpón A',
            'codigo' => 'GA',
            'aves_actuales' => $avesActuales,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        return [$operario, $galpon];
    }
}
