<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\AnularVacunacionAction;
use App\Actions\Operacion\RegistrarVacunacionAction;
use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\UserRole;
use App\Enums\VacunaTipo;
use App\Livewire\Operario\CargarHub;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use App\Models\Vacunacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioCargaVacunacionCap06Test extends TestCase
{
    use RefreshDatabase;

    public function test_two_vaccinations_same_day_are_allowed_and_counted(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioVacunacion')
            ->set('vacuna', VacunaTipo::Newcastle->value)
            ->call('guardarVacunacion');

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioVacunacion')
            ->set('vacuna', VacunaTipo::Gumboro->value)
            ->call('guardarVacunacion');

        $this->assertSame(2, Vacunacion::query()->where('galpon_id', $galpon->id)->count());

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioVacunacion')
            ->assertSee('Vacunaciones hoy:', false)
            ->assertSee('2', false);
    }

    public function test_observacion_is_saved_and_shown_in_detail(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioVacunacion')
            ->set('vacuna', VacunaTipo::Bronquitis->value)
            ->set('observacionVacunacion', 'Vía agua en bebederos')
            ->call('guardarVacunacion');

        $this->assertDatabaseHas('vacunaciones', [
            'lote_id' => $lote->id,
            'vacuna' => VacunaTipo::Bronquitis->value,
            'observacion' => 'Vía agua en bebederos',
        ]);
    }

    public function test_retry_with_same_idempotency_key_does_not_duplicate_vacunacion(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $clave = (string) Str::uuid();
        $action = app(RegistrarVacunacionAction::class);

        $primero = $action->execute($operario, $galpon, $lote, VacunaTipo::Pox, 'Primera', $clave);
        $segundo = $action->execute($operario, $galpon, $lote, VacunaTipo::Pox, 'Primera', $clave);

        $this->assertSame($primero->id, $segundo->id);
        $this->assertSame(1, Vacunacion::query()->where('galpon_id', $galpon->id)->count());
    }

    public function test_validation_error_preserves_dialog_and_maps_lote_field(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $otroGalpon = Galpon::factory()->forGranja(
            Granja::query()->find($galpon->granja_id)
        )->create([
            'empresa_id' => $galpon->empresa_id,
        ]);

        $loteAjeno = Lote::factory()->forGalpon($otroGalpon)->create([
            'estado' => LoteEstado::EnProduccion,
        ]);

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioVacunacion')
            ->set('loteId', (string) $loteAjeno->id)
            ->set('vacuna', VacunaTipo::Newcastle->value)
            ->call('guardarVacunacion')
            ->assertSet('dialogVacunacionAbierto', true)
            ->assertHasErrors(['loteId']);

        $this->assertSame(0, Vacunacion::query()->count());
    }

    public function test_form_shows_confirmation_and_no_calendar_message(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioVacunacion')
            ->assertSee('No hay calendario ni receta automática', false)
            ->set('vacuna', VacunaTipo::Encefalomielitis->value)
            ->assertSee('Confirmá antes de guardar', false)
            ->assertSee($lote->codigo, false)
            ->assertSee('Encefalomielitis aviar', false);
    }

    public function test_anulacion_excludes_vacunacion_from_today_count(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $vacunacion = app(RegistrarVacunacionAction::class)->execute(
            $operario,
            $galpon,
            $lote,
            VacunaTipo::Newcastle,
        );

        app(AnularVacunacionAction::class)->execute($operario, $vacunacion, 'Registro duplicado');

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioVacunacion')
            ->assertDontSee('Vacunaciones hoy:', false);
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Lote}
     */
    private function createOperarioConGalponYLote(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();
        $lote = Lote::factory()->forGalpon($galpon)->create([
            'estado' => LoteEstado::EnProduccion,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        return [$operario, $galpon, $lote];
    }
}
