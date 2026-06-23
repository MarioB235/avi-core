<?php

namespace Tests\Feature\Ui;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Livewire\Operario\CargarHub;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Tests\TestCase;

class SheetComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_sheet_renders_bottom_panel_with_trigger(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.sheet title="Recuperar contraseña">
                <x-slot:trigger>
                    <button type="button">Abrir</button>
                </x-slot:trigger>
                <p>Contenido</p>
            </x-ui.sheet>
        BLADE);

        $this->assertStringContainsString('avicore-sheet', $html);
        $this->assertStringContainsString('avicore-sheet__panel', $html);
        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('openSheet()', $html);
        $this->assertStringContainsString('translate-y-full', $html);
        $this->assertStringContainsString('data-sheet-initial-focus', $html);
    }

    public function test_dialog_renders_centered_panel_with_livewire_model(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => $galpon->id,
        ]);

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->assertSee('avicore-dialog', false)
            ->assertSee('avicore-dialog__panel', false)
            ->assertSee('Cantidad de huevos', false)
            ->assertSee('dialogHuevosAbierto', false);
    }
}
