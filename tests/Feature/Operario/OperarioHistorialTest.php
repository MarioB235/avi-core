<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\AnularRegistroOperativoAction;
use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Enums\VacunaTipo;
use App\Livewire\Operario\Historial;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Models\Vacunacion;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioHistorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_historial_lists_all_types_and_dates_newest_first(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 900,
                'created_at' => now()->subDay()->setTime(9, 15),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => 3,
                'created_at' => now()->setTime(11, 30),
            ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->assertSee('Todos los registros', false)
            ->assertSee('avicore-date-picker-trigger', false)
            ->assertSee('entangle', false)
            ->assertDontSee('type="date"', false)
            ->assertSee('3 muertes', false)
            ->assertSee('900 huevos aptos', false)
            ->assertSee('avicore-operario-historial-list__item--muertes', false)
            ->assertDontSee('Huevos ·', false)
            ->assertDontSee('Muertes ·', false)
            ->assertSeeInOrder(['3 muertes', '900 huevos aptos'], false);
    }

    public function test_historial_lists_vacunaciones_mixed_with_registros_newest_first(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $lote = Lote::factory()->forGalpon($galpon)->create([
            'codigo' => 'L-VAC',
            'estado' => LoteEstado::EnProduccion,
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 600,
                'created_at' => now()->subMinutes(30),
            ]);

        Vacunacion::factory()
            ->forLote($lote, $operario)
            ->create([
                'vacuna' => VacunaTipo::Gumboro,
                'created_at' => now(),
            ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->assertSee('Gumboro', false)
            ->assertSee('L-VAC', false)
            ->assertSee('600 huevos aptos', false)
            ->assertSee('avicore-operario-historial-list__item--vacunacion', false)
            ->assertSeeInOrder(['Gumboro', '600 huevos aptos'], false);
    }

    public function test_historial_filters_by_selected_date(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        $ayer = now()->subDay();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 500,
                'created_at' => $ayer->copy()->setTime(8, 0),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => 2,
                'created_at' => now()->setTime(10, 0),
            ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->set('fecha', $ayer->toDateString())
            ->assertSee('Registros del día', false)
            ->assertSee('500 huevos aptos', false)
            ->assertDontSee('2 muertes', false)
            ->call('verTodasLasFechas')
            ->assertSet('fecha', null)
            ->assertSee('Todos los registros', false)
            ->assertSee('2 muertes', false)
            ->assertSee('500 huevos aptos', false);
    }

    public function test_historial_rejects_invalid_and_future_dates(): void
    {
        [$operario] = $this->createOperarioConGalpon();

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->set('fecha', 'no-es-fecha')
            ->assertHasErrors(['fecha'])
            ->assertSee('La fecha seleccionada no es válida.', false)
            ->assertSee('avicore-date-picker-trigger--error', false)
            ->assertSet('fecha', null)
            ->set('fecha', now()->addDay()->toDateString())
            ->assertHasErrors(['fecha'])
            ->assertSee('La fecha no puede ser futura.', false)
            ->assertDontSee('La fecha seleccionada no es válida.', false)
            ->assertSee('avicore-date-picker-trigger--error', false)
            ->assertSet('fecha', null);
    }

    public function test_historial_does_not_show_records_from_other_company(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 400,
            ]);

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();
        $operarioAjeno = User::factory()->create([
            'empresa_id' => $otraEmpresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponAjeno, $operarioAjeno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 9999,
            ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->assertSee('400 huevos aptos', false)
            ->assertDontSee('9.999 huevos aptos', false);
    }

    public function test_historial_paginates_results(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        for ($i = 0; $i < 21; $i++) {
            RegistroOperativo::factory()
                ->forGalponAndUser($galpon, $operario)
                ->create([
                    'tipo' => RegistroOperativoTipo::Huevos,
                    'huevos' => 100 + $i,
                    'created_at' => now()->subMinutes($i),
                ]);
        }

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->assertSee('avicore-operario-historial-pagination', false)
            ->call('gotoPage', 2, 'page')
            ->assertSee('120 huevos aptos', false)
            ->assertDontSee('119 huevos aptos', false);
    }

    public function test_historial_shows_alimento_and_combinado_summaries(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Alimento,
                'huevos' => null,
                'alimento_kg' => 12.5,
                'created_at' => now()->subMinutes(2),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Combinado,
                'huevos' => 300,
                'muertes' => 1,
                'alimento_kg' => 5.25,
                'created_at' => now()->subMinute(),
            ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->assertSee('12,50 kg entregados', false)
            ->assertSee('300 huevos aptos · 1 muertes · 5,25 kg', false)
            ->assertSee('avicore-operario-historial-list__item--muertes', false);
    }

    public function test_historial_opens_detail_dialog_with_breakdown(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 750,
                'huevos_descarte' => 5,
                'created_at' => now()->setTime(7, 30),
            ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->call('abrirDetalle', 'registro-'.$registro->id)
            ->assertSet('dialogDetalleAbierto', true)
            ->assertSee('Detalle del registro', false)
            ->assertSee('750 aptos · 5 descarte', false)
            ->assertSee('Fecha y hora', false)
            ->assertSee('07:30', false);
    }

    public function test_historial_anula_registro_propio_del_dia_con_motivo(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(['aves_actuales' => 500]);

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => 4,
                'created_at' => now()->setTime(11, 0),
            ]);

        $galpon->update(['aves_actuales' => 496]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->call('abrirDetalle', 'registro-'.$registro->id)
            ->call('mostrarAnulacion')
            ->set('motivoAnulacion', 'Me equivoqué en la cantidad')
            ->call('anularRegistro')
            ->assertSet('dialogDetalleAbierto', false)
            ->assertDispatched('snackbar-show');

        $registro->refresh();
        $galpon->refresh();

        $this->assertSame(RegistroOperativoEstado::Anulado, $registro->estado);
        $this->assertSame('Me equivoqué en la cantidad', $registro->motivo_anulacion);
        $this->assertSame(500, $galpon->aves_actuales);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->assertSee('Anulado', false)
            ->assertSee('avicore-operario-historial-list__item--anulado', false);
    }

    public function test_historial_rechaza_anular_registro_de_otro_dia(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 400,
                'created_at' => now()->subDay()->setTime(8, 0),
            ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->call('abrirDetalle', 'registro-'.$registro->id)
            ->assertDontSee('Anular registro', false);
    }

    public function test_historial_rechaza_anulacion_sin_motivo(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 300,
            ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->call('abrirDetalle', 'registro-'.$registro->id)
            ->call('mostrarAnulacion')
            ->set('motivoAnulacion', '   ')
            ->call('anularRegistro')
            ->assertHasErrors(['motivoAnulacion']);
    }

    public function test_historial_anula_vacunacion_propia_del_dia_con_motivo(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $lote = Lote::factory()->forGalpon($galpon)->create([
            'codigo' => 'L-ANUL',
            'estado' => LoteEstado::EnProduccion,
        ]);

        $vacunacion = Vacunacion::factory()
            ->forLote($lote, $operario)
            ->create([
                'vacuna' => VacunaTipo::Newcastle,
                'created_at' => now()->setTime(9, 45),
            ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->call('abrirDetalle', 'vacunacion-'.$vacunacion->id)
            ->call('mostrarAnulacion')
            ->set('motivoAnulacion', 'Vacuna equivocada')
            ->call('anularRegistro')
            ->assertSet('dialogDetalleAbierto', false)
            ->assertDispatched('snackbar-show');

        $vacunacion->refresh();

        $this->assertSame(RegistroOperativoEstado::Anulado, $vacunacion->estado);
        $this->assertSame('Vacuna equivocada', $vacunacion->motivo_anulacion);
    }

    public function test_historial_anula_descarte_restaura_aves_vivas(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(['aves_actuales' => 500]);

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Descarte,
                'huevos' => null,
                'descarte_aves' => 6,
                'created_at' => now()->setTime(10, 15),
            ]);

        $galpon->update(['aves_actuales' => 494]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->call('abrirDetalle', 'registro-'.$registro->id)
            ->call('mostrarAnulacion')
            ->set('motivoAnulacion', 'Cantidad incorrecta')
            ->call('anularRegistro')
            ->assertSet('dialogDetalleAbierto', false)
            ->assertDispatched('snackbar-show');

        $registro->refresh();
        $galpon->refresh();

        $this->assertSame(RegistroOperativoEstado::Anulado, $registro->estado);
        $this->assertSame(500, $galpon->aves_actuales);
    }

    public function test_anular_registro_operativo_rechaza_registro_ya_anulado(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 200,
                'estado' => RegistroOperativoEstado::Anulado,
                'motivo_anulacion' => 'Error previo',
                'anulado_at' => now(),
                'anulado_por' => $operario->id,
            ]);

        $this->assertFalse(Gate::forUser($operario)->allows('anular', $registro));

        $this->expectException(AuthorizationException::class);

        app(AnularRegistroOperativoAction::class)->execute($operario, $registro, 'Intento duplicado');
    }

    public function test_encargado_puede_anular_registro_ajeno_del_dia(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 1000]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => 3,
                'created_at' => now()->setTime(14, 0),
            ]);

        $galpon->update(['aves_actuales' => 997]);

        $this->assertTrue(Gate::forUser($encargado)->allows('anular', $registro));

        app(AnularRegistroOperativoAction::class)->execute($encargado, $registro, 'Corrección de supervisión');

        $registro->refresh();
        $galpon->refresh();

        $this->assertSame(RegistroOperativoEstado::Anulado, $registro->estado);
        $this->assertSame('Corrección de supervisión', $registro->motivo_anulacion);
        $this->assertSame(1000, $galpon->aves_actuales);
    }

    public function test_historial_muestra_aviso_cuando_todos_los_registros_estan_anulados(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 200,
                'estado' => RegistroOperativoEstado::Anulado,
                'motivo_anulacion' => 'Error de carga',
                'anulado_at' => now(),
                'anulado_por' => $operario->id,
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => 1,
                'estado' => RegistroOperativoEstado::Anulado,
                'motivo_anulacion' => 'Duplicado',
                'anulado_at' => now(),
                'anulado_por' => $operario->id,
            ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->assertSee('avicore-operario-historial-notice', false)
            ->assertSee('no cuentan en los totales del galpón', false);
    }

    /**
     * @param  array<string, mixed>  $galponOverrides
     * @return array{0: User, 1: Galpon}
     */
    private function createOperarioConGalpon(array $galponOverrides = []): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create($galponOverrides);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => $galpon->id,
        ]);

        return [$operario, $galpon];
    }
}
