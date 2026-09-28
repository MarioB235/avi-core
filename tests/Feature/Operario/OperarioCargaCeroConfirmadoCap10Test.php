<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\RegistrarCargaDescarteAction;
use App\Actions\Operacion\RegistrarCargaHuevosAction;
use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Operario\CargarHub;
use App\Livewire\Operario\Home;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\OperarioGalponResumenService;
use App\Support\CapturaCeroEstado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioCargaCeroConfirmadoCap10Test extends TestCase
{
    use RefreshDatabase;

    public function test_confirmar_cero_huevos_persists_flag_without_quantities(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote();
        $action = app(RegistrarCargaHuevosAction::class);

        $registro = $action->execute($operario, $galpon, 0, 0, null, (string) Str::uuid(), true);

        $this->assertTrue($registro->cero_confirmado);
        $this->assertSame(0, (int) $registro->huevos);
        $this->assertSame(0, (int) $registro->huevos_descarte);
    }

    public function test_confirmar_cero_muertes_does_not_decrement_aves(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote(['aves_actuales' => 5000]);
        $action = app(RegistrarCargaMuertesAction::class);

        $registro = $action->execute($operario, $galpon, 0, null, (string) Str::uuid(), true);

        $galpon->refresh();

        $this->assertTrue($registro->cero_confirmado);
        $this->assertSame(0, (int) $registro->muertes);
        $this->assertSame(5000, (int) $galpon->aves_actuales);
    }

    public function test_confirmar_cero_descarte_does_not_decrement_aves(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote(['aves_actuales' => 4800]);
        $action = app(RegistrarCargaDescarteAction::class);

        $registro = $action->execute($operario, $galpon, 0, null, (string) Str::uuid(), true);

        $galpon->refresh();

        $this->assertTrue($registro->cero_confirmado);
        $this->assertSame(0, (int) $registro->descarte_aves);
        $this->assertSame(4800, (int) $galpon->aves_actuales);
    }

    public function test_resumen_distinguishes_omision_from_cero_confirmado(): void
    {
        [$operario, $galponOmision] = array_values(array_slice($this->createOperarioConGalponYLote(), 0, 2));

        $granja = $galponOmision->granja;
        $galponCero = Galpon::factory()->forGranja($granja)->conLoteActivo()->create();

        RegistroOperativo::factory()
            ->forGalponAndUser($galponCero, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'cero_confirmado' => true,
                'huevos' => 0,
                'huevos_descarte' => 0,
            ]);

        $service = app(OperarioGalponResumenService::class);

        $resumenOmision = $service->resumen($galponOmision);
        $resumenCero = $service->resumen($galponCero);

        $this->assertSame(CapturaCeroEstado::OMISION, $resumenOmision['huevos_estado_hoy']);
        $this->assertSame(CapturaCeroEstado::CERO_CONFIRMADO, $resumenCero['huevos_estado_hoy']);
        $this->assertSame(0, $resumenCero['huevos_hoy']);
    }

    public function test_livewire_confirmar_cero_huevos_closes_dialog(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->call('confirmarCeroHuevos')
            ->assertSet('dialogHuevosAbierto', false)
            ->assertDispatched('snackbar-show');

        $registro = RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Huevos)
            ->first();

        $this->assertNotNull($registro);
        $this->assertTrue($registro->cero_confirmado);
    }

    public function test_livewire_confirmar_cero_muertes_closes_dialog(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote(['aves_actuales' => 5000]);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioMuertes')
            ->call('confirmarCeroMuertes')
            ->assertSet('dialogMuertesAbierto', false)
            ->assertDispatched('snackbar-show');

        $registro = RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Muertes)
            ->first();

        $this->assertNotNull($registro);
        $this->assertTrue($registro->cero_confirmado);
        $this->assertSame(5000, (int) $galpon->fresh()->aves_actuales);
    }

    public function test_livewire_confirmar_cero_descarte_closes_dialog(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote(['aves_actuales' => 4800]);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioDescarte')
            ->call('confirmarCeroDescarte')
            ->assertSet('dialogDescarteAbierto', false)
            ->assertDispatched('snackbar-show');

        $registro = RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Descarte)
            ->first();

        $this->assertNotNull($registro);
        $this->assertTrue($registro->cero_confirmado);
        $this->assertSame(4800, (int) $galpon->fresh()->aves_actuales);
    }

    public function test_home_shows_sin_registro_and_cero_confirmado(): void
    {
        [$operario, $galponOmision, $loteOmision] = $this->createOperarioConGalponYLote();
        $operario->forceFill(['ultimo_galpon_id' => $galponOmision->id])->save();

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee('Sin registro hoy', false);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponOmision, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'cero_confirmado' => true,
                'muertes' => 0,
            ]);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee('0 confirmado hoy', false)
            ->assertSee($loteOmision->codigo, false);
    }

    public function test_cero_confirmado_is_idempotent_with_same_key(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote();
        $clave = (string) Str::uuid();
        $action = app(RegistrarCargaHuevosAction::class);

        $primero = $action->execute($operario, $galpon, 0, 0, null, $clave, true);
        $segundo = $action->execute($operario, $galpon, 0, 0, null, $clave, true);

        $this->assertSame($primero->id, $segundo->id);
        $this->assertSame(1, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Huevos)
            ->count());
    }

    /**
     * @param  array<string, mixed>  $galponOverrides
     * @return array{0: User, 1: Galpon, 2: Lote}
     */
    private function createOperarioConGalponYLote(array $galponOverrides = []): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->conLoteActivo()->create($galponOverrides);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => $galpon->id,
        ]);

        $lote = Lote::query()->where('galpon_id', $galpon->id)->firstOrFail();
        $lote->forceFill(['estado' => LoteEstado::EnProduccion])->save();

        return [$operario, $galpon, $lote];
    }
}
