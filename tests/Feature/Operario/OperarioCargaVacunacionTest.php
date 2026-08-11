<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\RegistrarVacunacionAction;
use App\Enums\EmpresaEstado;
use App\Enums\GalponEstado;
use App\Enums\LoteEstado;
use App\Enums\UserRole;
use App\Enums\VacunaTipo;
use App\Livewire\Operario\CargarHub;
use App\Livewire\Operario\CargaVacunacion;
use App\Livewire\Operario\Historial;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use App\Models\Vacunacion;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioCargaVacunacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_can_register_vaccination_by_lote(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioVacunacion')
            ->assertSet('dialogVacunacionAbierto', true)
            ->assertSet('loteId', (string) $lote->id)
            ->set('vacuna', VacunaTipo::Newcastle->value)
            ->call('guardarVacunacion')
            ->assertSet('dialogVacunacionAbierto', true)
            ->assertSet('vacunacionRecienGuardada', true)
            ->assertDispatched('snackbar-show');

        $this->assertDatabaseHas('vacunaciones', [
            'empresa_id' => $operario->empresa_id,
            'galpon_id' => $galpon->id,
            'lote_id' => $lote->id,
            'user_id' => $operario->id,
            'vacuna' => VacunaTipo::Newcastle->value,
        ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->assertSee('Vacuna Newcastle (La Sota)', false)
            ->assertSee($lote->codigo, false)
            ->assertSee('avicore-operario-historial-list__item--vacunacion', false);
    }

    public function test_carga_vacunacion_requires_lote_and_vacuna(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioVacunacion')
            ->set('loteId', '')
            ->set('vacuna', '')
            ->call('guardarVacunacion')
            ->assertHasErrors(['loteId', 'vacuna']);

        $this->assertSame(0, Vacunacion::query()->count());
    }

    public function test_carga_vacunacion_redirects_to_selector_when_no_galpon_selected(): void
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
            ->test(CargaVacunacion::class)
            ->assertRedirect(route('operario.cargar', ['abrir_galpon' => 1]));

        Livewire::actingAs($operario)
            ->withQueryParams(['abrir_galpon' => '1'])
            ->test(CargarHub::class)
            ->assertSet('selectorGalponAbierto', true);
    }

    public function test_carga_vacunacion_route_redirects_to_cargar_sheet_when_galpon_selected(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargaVacunacion::class)
            ->assertRedirect(route('operario.cargar', ['form' => 'vacunacion']));
    }

    public function test_cargar_hub_opens_vacunacion_dialog_via_query_param(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->withQueryParams(['form' => 'vacunacion'])
            ->test(CargarHub::class)
            ->assertSet('dialogVacunacionAbierto', true)
            ->assertSet('loteId', (string) $lote->id);
    }

    public function test_registrar_vacunacion_action_rejects_lote_from_other_galpon(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();

        $otroGalpon = Galpon::factory()->forGranja(
            Granja::query()->find($galpon->granja_id)
        )->create([
            'empresa_id' => $galpon->empresa_id,
        ]);

        $this->expectException(ValidationException::class);

        app(RegistrarVacunacionAction::class)->execute(
            $operario,
            $otroGalpon,
            $lote,
            VacunaTipo::Gumboro,
        );
    }

    public function test_registrar_vacunacion_action_rejects_closed_lote(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $lote->update(['estado' => LoteEstado::Cerrado]);

        $this->expectException(ValidationException::class);

        app(RegistrarVacunacionAction::class)->execute(
            $operario,
            $galpon,
            $lote->fresh(),
            VacunaTipo::Bronquitis,
        );
    }

    public function test_registrar_vacunacion_action_rejects_galpon_from_other_empresa(): void
    {
        [$operario, , $lote] = $this->createOperarioConGalponYLote();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();

        $this->expectException(AuthorizationException::class);

        app(RegistrarVacunacionAction::class)->execute(
            $operario,
            $galponAjeno,
            $lote,
            VacunaTipo::Pox,
        );
    }

    public function test_registrar_vacunacion_action_rejects_lote_from_other_empresa(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();
        $loteAjeno = Lote::factory()->forGalpon($galponAjeno)->create([
            'estado' => LoteEstado::EnProduccion,
        ]);

        $this->expectException(ValidationException::class);

        app(RegistrarVacunacionAction::class)->execute(
            $operario,
            $galpon,
            $loteAjeno,
            VacunaTipo::Newcastle,
        );
    }

    public function test_registrar_vacunacion_action_rejects_unavailable_galpon(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);
        $galpon->refresh();

        $this->expectException(ValidationException::class);

        app(RegistrarVacunacionAction::class)->execute(
            $operario,
            $galpon,
            $lote->fresh(),
            VacunaTipo::Bronquitis,
        );
    }

    public function test_cargar_hub_rejects_foreign_lote_on_save(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $otroGalpon = Galpon::factory()->forGranja(
            Granja::query()->find($galpon->granja_id)
        )->create([
            'empresa_id' => $galpon->empresa_id,
        ]);

        $loteAjeno = Lote::factory()->forGalpon($otroGalpon)->create([
            'codigo' => 'L-9999',
            'estado' => LoteEstado::EnProduccion,
        ]);

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioVacunacion')
            ->set('loteId', (string) $loteAjeno->id)
            ->set('vacuna', VacunaTipo::Newcastle->value)
            ->call('guardarVacunacion')
            ->assertHasErrors(['lote_id']);

        $this->assertDatabaseMissing('vacunaciones', [
            'lote_id' => $loteAjeno->id,
            'user_id' => $operario->id,
        ]);

        $this->assertDatabaseMissing('vacunaciones', [
            'lote_id' => $lote->id,
            'vacuna' => VacunaTipo::Newcastle->value,
        ]);
    }

    public function test_cargar_hub_rejects_lote_from_other_empresa_on_save(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();
        $loteAjeno = Lote::factory()->forGalpon($galponAjeno)->create([
            'estado' => LoteEstado::EnProduccion,
        ]);

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioVacunacion')
            ->set('loteId', (string) $loteAjeno->id)
            ->set('vacuna', VacunaTipo::Gumboro->value)
            ->call('guardarVacunacion')
            ->assertHasErrors(['loteId']);

        $this->assertSame(0, Vacunacion::query()->count());
    }

    public function test_cargar_vacunacion_redirects_when_galpon_became_unavailable(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);

        Livewire::actingAs($operario)
            ->test(CargaVacunacion::class)
            ->assertRedirect(route('operario.cargar', ['abrir_galpon' => 1]));
    }

    public function test_guardar_vacunacion_abre_selector_when_galpon_became_unavailable(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->set('loteId', (string) $lote->id)
            ->set('vacuna', VacunaTipo::Newcastle->value)
            ->call('guardarVacunacion')
            ->assertSet('selectorGalponAbierto', true)
            ->assertSet('dialogVacunacionAbierto', false);

        $this->assertSame(0, Vacunacion::query()->count());
    }

    public function test_cargar_hub_shows_vacunacion_tile(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->assertSee('Vacunación', false)
            ->assertSee('Registrá por lote', false);
    }

    /**
     * @param  array<string, mixed>  $loteOverrides
     * @return array{0: User, 1: Galpon, 2: Lote}
     */
    private function createOperarioConGalponYLote(array $loteOverrides = []): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);

        $galpon = Galpon::factory()->forGranja($granja)->create([
            'nombre' => 'Galpón A',
            'codigo' => 'GA',
        ]);

        $lote = Lote::factory()
            ->forGalpon($galpon)
            ->create(array_merge([
                'codigo' => 'L-1001',
                'estado' => LoteEstado::EnProduccion,
            ], $loteOverrides));

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        return [$operario, $galpon, $lote];
    }
}
