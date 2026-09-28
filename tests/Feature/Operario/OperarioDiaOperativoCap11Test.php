<?php

namespace Tests\Feature\Operario;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Operario\Historial;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\OperarioGalponResumenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioDiaOperativoCap11Test extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_resumen_hoy_uses_logical_day_in_empresa_timezone(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 03:30:00', 'UTC'));

        [$operario, $galpon] = $this->createOperarioConGalponYLote();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 111,
                'created_at' => Carbon::parse('2026-09-28 02:30:00', 'UTC'),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 222,
                'created_at' => Carbon::parse('2026-09-28 04:00:00', 'UTC'),
            ]);

        $resumen = app(OperarioGalponResumenService::class)->resumen($galpon);

        $this->assertSame(222, $resumen['huevos_hoy']);
    }

    public function test_anulacion_rechaza_registro_de_dia_logico_anterior(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 03:30:00', 'UTC'));

        [$operario, $galpon] = $this->createOperarioConGalpon();

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 400,
                'created_at' => Carbon::parse('2026-09-28 02:30:00', 'UTC'),
            ]);

        $this->assertFalse(Gate::forUser($operario)->allows('anular', $registro));

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->call('abrirDetalle', 'registro-'.$registro->id)
            ->assertDontSee('Anular registro', false);
    }

    public function test_anulacion_permite_registro_del_dia_logico_actual(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 05:00:00', 'UTC'));

        [$operario, $galpon] = $this->createOperarioConGalpon();

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 300,
                'created_at' => Carbon::parse('2026-09-28 04:30:00', 'UTC'),
            ]);

        $this->assertTrue(Gate::forUser($operario)->allows('anular', $registro));
    }

    public function test_historial_filtra_por_fecha_logica_de_empresa(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 03:30:00', 'UTC'));

        [$operario, $galpon] = $this->createOperarioConGalpon();

        $registroAyerLogico = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 100,
                'created_at' => Carbon::parse('2026-09-28 02:30:00', 'UTC'),
            ]);

        $registroHoyLogico = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 200,
                'created_at' => Carbon::parse('2026-09-28 04:00:00', 'UTC'),
            ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->set('fecha', '2026-09-27')
            ->assertSee((string) $registroAyerLogico->cantidadResumen(), false)
            ->assertDontSee((string) $registroHoyLogico->cantidadResumen(), false);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->set('fecha', '2026-09-28')
            ->assertSee((string) $registroHoyLogico->cantidadResumen(), false)
            ->assertDontSee((string) $registroAyerLogico->cantidadResumen(), false);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function createOperarioConGalpon(): array
    {
        $empresa = Empresa::factory()->create([
            'estado' => EmpresaEstado::Activa,
            'configuracion' => [
                'zona_horaria' => 'America/Montevideo',
                'unidades' => [
                    'huevos_por_maple' => 30,
                    'maples_por_cajon' => 12,
                ],
            ],
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->conLoteActivo()->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        return [$operario, $galpon];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Lote}
     */
    private function createOperarioConGalponYLote(): array
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $lote = Lote::query()->where('galpon_id', $galpon->id)->firstOrFail();
        $lote->forceFill(['estado' => LoteEstado::EnProduccion])->save();

        return [$operario, $galpon, $lote];
    }
}
