<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\RegistrarCargaDescarteAction;
use App\Enums\EmpresaEstado;
use App\Enums\GalponEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Operario\CargarHub;
use App\Livewire\Operario\Historial;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioCargaDescarteTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_can_register_bird_culling(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(['aves_actuales' => 500]);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioDescarte')
            ->assertSet('dialogDescarteAbierto', true)
            ->set('descarteAves', '5')
            ->call('guardarDescarte')
            ->assertSet('dialogDescarteAbierto', true)
            ->assertSet('descarteRecienGuardado', true)
            ->assertDispatched('snackbar-show');

        $this->assertDatabaseHas('registros_operativos', [
            'galpon_id' => $galpon->id,
            'tipo' => RegistroOperativoTipo::Descarte->value,
            'descarte_aves' => 5,
        ]);

        $galpon->refresh();
        $this->assertSame(495, $galpon->aves_actuales);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->assertSee('5 descarte de aves', false);
    }

    public function test_carga_descarte_deep_link_opens_dialog(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->withQueryParams(['form' => 'descarte'])
            ->test(CargarHub::class)
            ->assertSet('dialogDescarteAbierto', true);

        $this->get(route('operario.carga.descarte'))
            ->assertRedirect(route('operario.cargar', ['form' => 'descarte']));
    }

    public function test_registrar_carga_descarte_rejects_unavailable_galpon(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);
        $galpon->refresh();

        $this->expectException(ValidationException::class);

        app(RegistrarCargaDescarteAction::class)->execute($operario, $galpon, 1);
    }

    /**
     * @param  array<string, mixed>  $galponOverrides
     * @return array{0: User, 1: Galpon}
     */
    private function createOperarioConGalpon(array $galponOverrides = []): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create($galponOverrides);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        return [$operario, $galpon];
    }
}
