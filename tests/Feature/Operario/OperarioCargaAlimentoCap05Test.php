<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\RegistrarCargaAlimentoAction;
use App\Enums\EmpresaEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Operario\CargarHub;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Support\AlimentoValidacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioCargaAlimentoCap05Test extends TestCase
{
    use RefreshDatabase;

    public function test_two_deliveries_same_day_sum_in_resumen(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioAlimento')
            ->set('alimentoKg', '1000')
            ->call('guardarAlimento');

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioAlimento')
            ->set('alimentoKg', '500,5')
            ->call('guardarAlimento');

        $this->assertSame(2, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Alimento)
            ->count());

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioAlimento')
            ->assertSee('Entregado hoy:', false)
            ->assertSee('1.500,50', false);
    }

    public function test_comma_decimal_input_is_persisted_with_precision(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioAlimento')
            ->set('alimentoKg', '1250,75')
            ->call('guardarAlimento')
            ->assertSet('dialogAlimentoAbierto', false);

        $this->assertDatabaseHas('registros_operativos', [
            'galpon_id' => $galpon->id,
            'tipo' => RegistroOperativoTipo::Alimento->value,
            'alimento_kg' => 1250.75,
        ]);
    }

    public function test_validation_error_preserves_input_and_keeps_dialog_open(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioAlimento')
            ->set('alimentoKg', (string) (AlimentoValidacion::MAX_KG + 1))
            ->call('guardarAlimento')
            ->assertSet('dialogAlimentoAbierto', true)
            ->assertHasErrors(['alimentoKg']);

        $this->assertSame(0, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Alimento)
            ->count());
    }

    public function test_retry_with_same_idempotency_key_does_not_duplicate_alimento(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $clave = (string) Str::uuid();
        $action = app(RegistrarCargaAlimentoAction::class);

        $primero = $action->execute($operario, $galpon, 800.25, null, $clave);
        $segundo = $action->execute($operario, $galpon, 800.25, null, $clave);

        $this->assertSame($primero->id, $segundo->id);
        $this->assertSame(1, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Alimento)
            ->count());
    }

    public function test_form_clarifies_delivery_not_daily_consumption(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioAlimento')
            ->assertSee('No es consumo diario', false)
            ->assertSee('si hoy no llegó ración', false)
            ->set('alimentoKg', '200')
            ->assertSee('Confirmá antes de guardar', false);
    }

    public function test_day_without_delivery_shows_zero_without_blocking_other_loads(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioAlimento')
            ->assertDontSee('Entregado hoy:', false);

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->assertSet('dialogHuevosAbierto', true);
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
        ]);

        return [$operario, $galpon];
    }
}
