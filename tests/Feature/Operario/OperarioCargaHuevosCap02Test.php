<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\RegistrarCargaHuevosAction;
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
use Livewire\Livewire;
use Tests\TestCase;

class OperarioCargaHuevosCap02Test extends TestCase
{
    use RefreshDatabase;

    public function test_two_new_huevos_loads_sum_on_home(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '100')
            ->set('huevosDescarte', '5')
            ->call('guardarHuevos');

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '40')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos');

        $this->assertSame(2, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Huevos)
            ->count());

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee('140', false)
            ->assertSee('5', false);
    }

    public function test_retry_with_same_idempotency_key_does_not_duplicate(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $clave = (string) Str::uuid();
        $action = app(RegistrarCargaHuevosAction::class);

        $primero = $action->execute($operario, $galpon, 250, 0, null, $clave);
        $segundo = $action->execute($operario, $galpon, 250, 0, null, $clave);

        $this->assertSame($primero->id, $segundo->id);
        $this->assertSame(1, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Huevos)
            ->count());
    }

    public function test_livewire_retry_with_same_form_key_does_not_duplicate(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $component = Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '180')
            ->set('huevosDescarte', '0');

        $clave = $component->get('huevosIdempotenciaClave');

        $component->call('guardarHuevos');
        $component->set('huevos', '180')->call('guardarHuevos');

        $this->assertSame(1, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('idempotencia_clave', $clave)
            ->count());
    }

    public function test_new_dialog_intent_with_same_quantity_creates_second_record(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '90')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos');

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '90')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos');

        $this->assertSame(2, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Huevos)
            ->count());
    }

    public function test_huevos_form_shows_confirmation_and_today_accumulated(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 300,
                'huevos_descarte' => 10,
            ]);

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->assertSee('Llevás hoy en este galpón')
            ->assertSee('300', false)
            ->set('huevos', '50')
            ->set('huevosDescarte', '0')
            ->assertSee('Confirmá antes de guardar')
            ->assertSee('maple', false);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function createOperarioConGalpon(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->conLoteActivo()->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        return [$operario, $galpon];
    }
}
