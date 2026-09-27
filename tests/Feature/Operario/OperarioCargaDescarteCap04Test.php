<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\AnularRegistroOperativoAction;
use App\Actions\Operacion\RegistrarCargaDescarteAction;
use App\Enums\EmpresaEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Operario\CargarHub;
use App\Livewire\Operario\Home;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioCargaDescarteCap04Test extends TestCase
{
    use RefreshDatabase;

    public function test_sequential_descarte_respects_locked_balance_and_never_go_negative(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 10);
        $action = app(RegistrarCargaDescarteAction::class);

        $action->execute($operario, $galpon, 6);

        try {
            $action->execute($operario, $galpon->fresh(), 6);
            $this->fail('La segunda carga debía rechazarse por saldo insuficiente.');
        } catch (ValidationException) {
            // esperado
        }

        $galpon->refresh();
        $this->assertSame(4, $galpon->aves_actuales);
        $this->assertGreaterThanOrEqual(0, $galpon->aves_actuales);
        $this->assertSame(1, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Descarte)
            ->count());
    }

    public function test_two_valid_descarte_loads_sum_and_reduce_balance(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 100);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioDescarte')
            ->set('descarteAves', '3')
            ->call('guardarDescarte');

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioDescarte')
            ->set('descarteAves', '2')
            ->call('guardarDescarte');

        $galpon->refresh();
        $this->assertSame(95, $galpon->aves_actuales);
        $this->assertSame(2, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Descarte)
            ->count());

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee('5', false)
            ->assertSee('aves descartadas hoy', false);
    }

    public function test_validation_error_preserves_input_and_keeps_dialog_open(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 5);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioDescarte')
            ->set('descarteAves', '8')
            ->call('guardarDescarte')
            ->assertSet('dialogDescarteAbierto', true)
            ->assertSet('descarteAves', '8')
            ->assertHasErrors(['descarteAves']);

        $galpon->refresh();
        $this->assertSame(5, $galpon->aves_actuales);
    }

    public function test_retry_with_same_idempotency_key_does_not_duplicate_descarte(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 50);
        $clave = (string) Str::uuid();
        $action = app(RegistrarCargaDescarteAction::class);

        $primero = $action->execute($operario, $galpon, 4, null, $clave);
        $segundo = $action->execute($operario, $galpon->fresh(), 4, null, $clave);

        $this->assertSame($primero->id, $segundo->id);
        $galpon->refresh();
        $this->assertSame(46, $galpon->aves_actuales);
        $this->assertSame(1, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Descarte)
            ->count());
    }

    public function test_descarte_form_shows_live_balance_and_differentiated_labels(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 120);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioDescarte')
            ->assertSee('Aves vivas en el galpón ahora')
            ->assertSee('No es mortalidad ni huevo descartado', false)
            ->assertSee('120', false)
            ->set('descarteAves', '15')
            ->assertSee('Confirmá antes de guardar')
            ->assertSee('Quedarían', false)
            ->assertSee('105', false);
    }

    public function test_anulacion_restores_balance_and_excludes_from_resumen(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 100);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $registro = app(RegistrarCargaDescarteAction::class)->execute($operario, $galpon, 7);
        $galpon->refresh();
        $this->assertSame(93, $galpon->aves_actuales);

        app(AnularRegistroOperativoAction::class)->execute($operario, $registro, 'Carga equivocada');

        $galpon->refresh();
        $this->assertSame(100, $galpon->aves_actuales);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertDontSee('aves descartadas hoy', false);
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
