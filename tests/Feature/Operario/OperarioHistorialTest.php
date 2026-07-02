<?php

namespace Tests\Feature\Operario;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('3 muertes', false)
            ->assertSee('900 huevos', false)
            ->assertSee('avicore-operario-historial-list__item--muertes', false)
            ->assertDontSee('Huevos ·', false)
            ->assertDontSee('Muertes ·', false)
            ->assertSeeInOrder(['3 muertes', '900 huevos'], false);
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
            ->assertSee('600 huevos', false)
            ->assertSee('avicore-operario-historial-list__item--vacunacion', false)
            ->assertSeeInOrder(['Gumboro', '600 huevos'], false);
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
            ->assertSee('500 huevos', false)
            ->assertDontSee('2 muertes', false)
            ->call('verTodasLasFechas')
            ->assertSet('fecha', null)
            ->assertSee('2 muertes', false)
            ->assertSee('500 huevos', false);
    }

    public function test_historial_rejects_invalid_and_future_dates(): void
    {
        [$operario] = $this->createOperarioConGalpon();

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->set('fecha', 'no-es-fecha')
            ->assertHasErrors(['fecha'])
            ->assertSet('fecha', null)
            ->set('fecha', now()->addDay()->toDateString())
            ->assertHasErrors(['fecha'])
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
            ->assertSee('400 huevos', false)
            ->assertDontSee('9.999 huevos', false);
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
            ->assertSee('120 huevos', false)
            ->assertDontSee('119 huevos', false);
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
            ->assertSee('12,50 kg', false)
            ->assertSee('300 huevos · 1 muertes · 5,25 kg', false)
            ->assertSee('avicore-operario-historial-list__item--muertes', false);
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
            'ultimo_galpon_id' => $galpon->id,
        ]);

        return [$operario, $galpon];
    }
}
