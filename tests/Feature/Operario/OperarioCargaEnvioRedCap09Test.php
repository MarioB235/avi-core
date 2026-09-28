<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\RegistrarCargaAlimentoAction;
use App\Actions\Operacion\RegistrarCargaDescarteAction;
use App\Actions\Operacion\RegistrarCargaHuevosAction;
use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Actions\Operacion\RegistrarVacunacionAction;
use App\Enums\EmpresaEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Enums\VacunaTipo;
use App\Livewire\Operario\CargarHub;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class OperarioCargaEnvioRedCap09Test extends TestCase
{
    use RefreshDatabase;

    public function test_network_failure_keeps_form_open_and_idempotency_key(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $calls = 0;
        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->make([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 120,
            ]);

        $this->mock(RegistrarCargaHuevosAction::class, function ($mock) use (&$calls, $registro): void {
            $mock->shouldReceive('execute')
                ->twice()
                ->andReturnUsing(function () use (&$calls, $registro) {
                    $calls++;

                    if ($calls === 1) {
                        throw new RuntimeException('Connection timeout');
                    }

                    $registro->save();

                    return $registro->fresh();
                });
        });

        $component = Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '120')
            ->set('huevosDescarte', '0');

        $clave = $component->get('huevosIdempotenciaClave');

        $component->call('guardarHuevos')
            ->assertSet('dialogHuevosAbierto', true)
            ->assertSet('huevos', '120')
            ->assertNotSet('cargaEnvioError', null)
            ->assertSet('huevosIdempotenciaClave', $clave);

        $component->call('guardarHuevos')
            ->assertSet('dialogHuevosAbierto', false)
            ->assertSet('cargaEnvioError', null);

        $this->assertSame(1, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Huevos)
            ->count());
    }

    #[DataProvider('capturaGuardarMethodsProvider')]
    public function test_network_failure_shows_error_without_closing_dialog(
        string $abrirMethod,
        string $guardarMethod,
        array $formState,
        string $actionClass,
    ): void {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        if ($guardarMethod === 'guardarVacunacion') {
            $lote = Lote::query()->where('galpon_id', $galpon->id)->firstOrFail();
            $formState['loteId'] = (string) $lote->id;
        }

        $this->mock($actionClass, function ($mock): void {
            $mock->shouldReceive('execute')->once()->andThrow(new RuntimeException('Network error'));
        });

        $component = Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call($abrirMethod);

        foreach ($formState as $property => $value) {
            $component->set($property, $value);
        }

        $component->call($guardarMethod)
            ->assertNotSet('cargaEnvioError', null)
            ->assertSee('No pudimos confirmar el guardado', false)
            ->assertSee('Reintentar', false);
    }

    public static function capturaGuardarMethodsProvider(): array
    {
        return [
            'alimento' => [
                'abrirFormularioAlimento',
                'guardarAlimento',
                ['alimentoKg' => '500'],
                RegistrarCargaAlimentoAction::class,
            ],
            'muertes' => [
                'abrirFormularioMuertes',
                'guardarMuertes',
                ['muertes' => '5'],
                RegistrarCargaMuertesAction::class,
            ],
            'descarte' => [
                'abrirFormularioDescarte',
                'guardarDescarte',
                ['descarteAves' => '3'],
                RegistrarCargaDescarteAction::class,
            ],
            'vacunacion' => [
                'abrirFormularioVacunacion',
                'guardarVacunacion',
                ['vacuna' => VacunaTipo::Newcastle->value],
                RegistrarVacunacionAction::class,
            ],
        ];
    }

    public function test_validation_error_does_not_show_network_banner(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 5);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioMuertes')
            ->set('muertes', '20')
            ->call('guardarMuertes')
            ->assertSet('dialogMuertesAbierto', true)
            ->assertSet('cargaEnvioError', null)
            ->assertHasErrors(['muertes']);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function createOperarioConGalpon(int $avesActuales = 5000): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->conLoteActivo()->create([
            'aves_actuales' => $avesActuales,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        return [$operario, $galpon];
    }
}
