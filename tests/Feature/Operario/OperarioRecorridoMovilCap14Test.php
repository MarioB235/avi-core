<?php

namespace Tests\Feature\Operario;

use App\Enums\EmpresaEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Auth\Login;
use App\Livewire\Operario\CargarHub;
use App\Livewire\Operario\Historial;
use App\Livewire\Operario\Home;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioRecorridoMovilCap14Test extends TestCase
{
    use RefreshDatabase;

    public function test_recorrido_movil_login_galpon_capturas_historial_y_anulacion(): void
    {
        [$operario, $galpon] = $this->createOperarioSinGalponRecordado(avesActuales: 500);

        $this->get(route('operario.home'))
            ->assertRedirect(route('login'));

        Livewire::test(Login::class)
            ->set('documento', $operario->documento)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('operario.home'));

        $this->assertAuthenticatedAs($operario);

        $this->assertOperarioMobileShell(
            $this->get(route('operario.home')),
            activeTab: 'Inicio',
        )->assertSee('Sin seleccionar', false);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->call('seleccionarGalpon', $galpon->id)
            ->assertSet('galponId', $galpon->id)
            ->assertDispatched('snackbar-show', message: 'Galpón actualizado.', variant: 'success');

        $operario->refresh();
        $this->assertSame($galpon->id, $operario->ultimo_galpon_id);

        $this->assertOperarioMobileShell(
            $this->get(route('operario.cargar')),
            activeTab: 'Cargar',
        )->assertSee('Huevos', false)
            ->assertSee('Muertes', false);

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '120')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos')
            ->assertDispatched('snackbar-show', message: 'Huevos guardados.', variant: 'success');

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioMuertes')
            ->set('muertes', '3')
            ->call('guardarMuertes')
            ->assertDispatched('snackbar-show', message: 'Muertes guardadas.', variant: 'success');

        $galpon->refresh();
        $this->assertSame(497, $galpon->aves_actuales);

        $this->assertOperarioMobileShell(
            $this->get(route('operario.home')),
            activeTab: 'Inicio',
        )->assertSee('120', false)
            ->assertSee('3', false);

        $muertes = RegistroOperativo::query()
            ->where('user_id', $operario->id)
            ->where('tipo', RegistroOperativoTipo::Muertes)
            ->firstOrFail();

        $this->assertOperarioMobileShell(
            $this->get(route('operario.historial')),
            activeTab: 'Historial',
        )->assertSee('120 huevos aptos', false)
            ->assertSee('3 muertes', false);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->call('abrirDetalle', 'registro-'.$muertes->id)
            ->call('mostrarAnulacion')
            ->set('motivoAnulacion', 'Conté mal en el galpón')
            ->call('anularRegistro')
            ->assertSet('dialogDetalleAbierto', false)
            ->assertDispatched('snackbar-show', message: 'Registro anulado.', variant: 'success');

        $muertes->refresh();
        $galpon->refresh();

        $this->assertSame(RegistroOperativoEstado::Anulado, $muertes->estado);
        $this->assertSame('Conté mal en el galpón', $muertes->motivo_anulacion);
        $this->assertSame(500, $galpon->aves_actuales);

        $this->assertOperarioMobileShell(
            $this->get(route('operario.historial')),
            activeTab: 'Historial',
        )->assertSee('Anulado', false)
            ->assertSee('120 huevos aptos', false);
    }

    public function test_recorrido_movil_formularios_usan_teclado_numerico(): void
    {
        [$operario, $galpon] = $this->createOperarioSinGalponRecordado();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->assertSet('dialogHuevosAbierto', true)
            ->assertSee('inputmode="numeric"', false);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function createOperarioSinGalponRecordado(int $avesActuales = 5000): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Granja recorrido CAP-14',
        ]);

        $galpon = Galpon::factory()->forGranja($granja)->conLoteActivo()->create([
            'nombre' => 'Galpón recorrido CAP-14',
            'codigo' => 'CAP14',
            'aves_actuales' => $avesActuales,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        return [$operario, $galpon];
    }

    private function assertOperarioMobileShell(TestResponse $response, string $activeTab): TestResponse
    {
        return $response
            ->assertOk()
            ->assertSee('viewport-fit=cover', false)
            ->assertSee('avicore-operario-body', false)
            ->assertSee('avicore-operario-shell--home', false)
            ->assertSee('aria-label="Navegación operario"', false)
            ->assertSee('avicore-operario-tab-bar lg:hidden', false)
            ->assertSee('wire:navigate', false)
            ->assertSee('avicore-snackbar-host', false)
            ->assertSee('Inicio', false)
            ->assertSee('Cargar', false)
            ->assertSee('Historial', false)
            ->assertSee('avicore-operario-tab-bar__item--active', false)
            ->assertSee($activeTab, false);
    }
}
