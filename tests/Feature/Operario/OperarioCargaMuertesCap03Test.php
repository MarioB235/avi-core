<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\RegistrarCargaMuertesAction;
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

class OperarioCargaMuertesCap03Test extends TestCase
{
    use RefreshDatabase;

    public function test_sequential_muertes_respect_locked_balance_and_never_go_negative(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 10);
        $action = app(RegistrarCargaMuertesAction::class);

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
            ->where('tipo', RegistroOperativoTipo::Muertes)
            ->count());
    }

    public function test_two_valid_muertes_loads_sum_and_reduce_balance(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 100);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioMuertes')
            ->set('muertes', '3')
            ->call('guardarMuertes');

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioMuertes')
            ->set('muertes', '2')
            ->call('guardarMuertes');

        $galpon->refresh();
        $this->assertSame(95, $galpon->aves_actuales);
        $this->assertSame(2, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Muertes)
            ->count());

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee('5', false)
            ->assertSee('Murieron hoy', false);
    }

    public function test_validation_error_preserves_input_and_keeps_dialog_open(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 5);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioMuertes')
            ->set('muertes', '8')
            ->call('guardarMuertes')
            ->assertSet('dialogMuertesAbierto', true)
            ->assertSet('muertes', '8')
            ->assertHasErrors(['muertes']);

        $galpon->refresh();
        $this->assertSame(5, $galpon->aves_actuales);
    }

    public function test_retry_with_same_idempotency_key_does_not_duplicate_muertes(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 50);
        $clave = (string) Str::uuid();
        $action = app(RegistrarCargaMuertesAction::class);

        $primero = $action->execute($operario, $galpon, 4, null, $clave);
        $segundo = $action->execute($operario, $galpon->fresh(), 4, null, $clave);

        $this->assertSame($primero->id, $segundo->id);
        $galpon->refresh();
        $this->assertSame(46, $galpon->aves_actuales);
        $this->assertSame(1, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Muertes)
            ->count());
    }

    public function test_muertes_form_shows_live_balance_and_confirmation(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 120);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioMuertes')
            ->assertSee('Aves vivas en el galpón ahora')
            ->assertSee('120', false)
            ->set('muertes', '15')
            ->assertSee('Confirmá antes de guardar')
            ->assertSee('Quedarían', false)
            ->assertSee('105', false);
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
