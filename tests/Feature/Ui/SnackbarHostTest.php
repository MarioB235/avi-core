<?php

namespace Tests\Feature\Ui;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Livewire\Operario\Home;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SnackbarHostTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_layout_includes_snackbar_host(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('operario.home'))
            ->assertOk()
            ->assertSee('avicore-snackbar-host', false)
            ->assertSee('avicore-snackbar-host--operario', false)
            ->assertSee('role="status"', false)
            ->assertSee('Cerrar notificación', false)
            ->assertSee('scheduleClose', false)
            ->assertSee('4500', false);
    }

    public function test_snackbar_host_css_anchors_desktop_bottom_right(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertNotFalse($css);
        $this->assertStringContainsString('.avicore-snackbar-host--operario', $css);
        $this->assertStringContainsString('@media (min-width: 1024px)', $css);
        $this->assertStringContainsString('right-6', $css);
        $this->assertStringContainsString('bottom-6', $css);
        $this->assertStringContainsString('justify-end', $css);
    }

    public function test_operario_home_dispatches_snackbar_after_galpon_selection(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->call('seleccionarGalpon', $galpon->id)
            ->assertDispatched('snackbar-show', message: 'Galpón actualizado.', variant: 'success');
    }

    public function test_session_status_renders_snackbar_payload_on_operario_home(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($operario)
            ->withSession([
                'status' => 'Carga de huevos guardada.',
                'status_variant' => 'success',
            ])
            ->get(route('operario.home'));

        $response
            ->assertOk()
            ->assertSee('Carga de huevos guardada.', false)
            ->assertSee('avicore-snackbar-host', false);
    }
}
