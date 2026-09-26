<?php

namespace Tests\Feature\Operario;

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
use App\Services\DemoLoginService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_login_is_disabled_in_production_even_when_flag_is_true(): void
    {
        config(['avicore.demo_login.enabled_flag' => true]);
        $this->app['env'] = 'production';

        $this->assertFalse(app(DemoLoginService::class)->isEnabled());
    }

    public function test_encargado_registers_huevos_and_muertes_same_day(): void
    {
        [$encargado, $galpon] = $this->createUsuarioConGalpon(UserRole::Encargado, avesActuales: 200);
        $encargado->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($encargado)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '150')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos')
            ->assertDispatched('snackbar-show', message: 'Huevos guardados.', variant: 'success');

        Livewire::actingAs($encargado)
            ->test(CargarHub::class)
            ->call('abrirFormularioMuertes')
            ->set('muertes', '5')
            ->call('guardarMuertes')
            ->assertDispatched('snackbar-show', message: 'Muertes guardadas.', variant: 'success');

        $this->assertDatabaseHas('registros_operativos', [
            'user_id' => $encargado->id,
            'galpon_id' => $galpon->id,
            'tipo' => RegistroOperativoTipo::Huevos->value,
            'huevos' => 150,
        ]);

        $this->assertDatabaseHas('registros_operativos', [
            'user_id' => $encargado->id,
            'galpon_id' => $galpon->id,
            'tipo' => RegistroOperativoTipo::Muertes->value,
            'muertes' => 5,
        ]);

        $galpon->refresh();
        $this->assertSame(195, $galpon->aves_actuales);

        Livewire::actingAs($encargado)
            ->test(Home::class)
            ->assertSee('150', false)
            ->assertSee('5', false)
            ->assertSee('Murieron hoy', false);
    }

    public function test_multiple_huevos_cargas_accumulate_on_home_today(): void
    {
        [$operario, $galpon] = $this->createUsuarioConGalpon(UserRole::Operario);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '100')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos');

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '50')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos');

        $this->assertSame(2, RegistroOperativo::query()
            ->where('tipo', RegistroOperativoTipo::Huevos)
            ->count());

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee('150', false)
            ->assertSee('Aptos hoy', false);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function createUsuarioConGalpon(UserRole $rol, int $avesActuales = 5000): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);

        $galpon = Galpon::factory()->forGranja($granja)->create([
            'aves_actuales' => $avesActuales,
        ]);

        $usuario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => $rol,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        return [$usuario, $galpon];
    }
}
