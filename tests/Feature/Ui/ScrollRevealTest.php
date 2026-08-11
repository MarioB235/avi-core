<?php

namespace Tests\Feature\Ui;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrollRevealTest extends TestCase
{
    use RefreshDatabase;

    public function test_scroll_reveal_assets_are_wired(): void
    {
        $appJs = file_get_contents(resource_path('js/app.js'));
        $js = file_get_contents(resource_path('js/scroll-reveal.js'));
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertNotFalse($appJs);
        $this->assertNotFalse($js);
        $this->assertNotFalse($css);
        $this->assertStringContainsString('./scroll-reveal', $appJs);
        $this->assertStringContainsString('rescanAvicoreReveal', $js);
        $this->assertStringContainsString('prefers-reduced-motion', $js);
        $this->assertStringContainsString('[data-avicore-reveal]', $css);
        $this->assertStringContainsString('.avicore-reveal--visible', $css);
    }

    public function test_operario_home_without_galpon_renders_empty_state_reveal(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        $response = $this->actingAs($operario)->get(route('operario.home'));

        $response
            ->assertOk()
            ->assertSee('data-avicore-reveal', false)
            ->assertSee('avicore-operario-home-summary--empty', false)
            ->assertSee('Seleccioná un galpón para ver el estado.', false)
            ->assertDontSee('style="transition-delay', false);
    }

    public function test_operario_home_renders_section_reveal_markers(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        Lote::factory()
            ->forGalpon($galpon)
            ->create([
                'estado' => LoteEstado::EnProduccion,
            ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => $galpon->id,
        ]);

        $response = $this->actingAs($operario)->get(route('operario.home'));

        $response
            ->assertOk()
            ->assertSee('data-avicore-reveal', false)
            ->assertSee('avicore-operario-home-summary', false)
            ->assertSee('avicore-operario-home-lotes', false)
            ->assertDontSee('style="transition-delay', false);
    }

    public function test_operario_cargar_hub_renders_section_reveal_marker(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($operario)->get(route('operario.cargar'));

        $response
            ->assertOk()
            ->assertSee('data-avicore-reveal', false)
            ->assertSee('avicore-operario-cargar-types', false)
            ->assertDontSee('style="transition-delay', false);
    }

    public function test_operario_navigate_handles_livewire_navigation(): void
    {
        $js = file_get_contents(resource_path('js/operario-navigate.js'));

        $this->assertNotFalse($js);
        $this->assertStringContainsString('avicore-operario-shell--navigating', $js);
        $this->assertStringNotContainsString('content-under', $js);
        $this->assertStringNotContainsString('syncHomeNavScrollState', $js);
    }

    public function test_galpon_selector_contract_avoids_live_entangle_and_teleport(): void
    {
        $blade = file_get_contents(resource_path('views/livewire/operario/partials/galpon-chip-selector.blade.php'));

        $this->assertNotFalse($blade);
        $this->assertStringContainsString("@entangle('selectorGalponAbierto')", $blade);
        $this->assertStringNotContainsString("@entangle('selectorGalponAbierto').live", $blade);
        $this->assertStringNotContainsString('x-teleport="body"', $blade);
        $this->assertStringContainsString('wire:click="seleccionarGalpon', $blade);
    }

    public function test_operario_hero_shell_uses_inner_sheet_scroll_on_mobile(): void
    {
        $css = file_get_contents(resource_path('css/operario.css'));

        $this->assertNotFalse($css);
        $this->assertStringContainsString('.avicore-operario-body:has(.avicore-operario-shell--home)', $css);
        $this->assertStringContainsString('.avicore-operario-home-sheet', $css);
        $this->assertStringContainsString('overflow-y-auto', $css);
        $this->assertStringContainsString(':has(.avicore-operario-galpon-selector--open)', $css);
        $this->assertStringNotContainsString('avicore-operario-shell__workspace::before', $css);
        $this->assertStringContainsString('.avicore-operario-cargar-types', $css);
        $this->assertStringContainsString('flex-1 flex-col gap-3', $css);
    }
}
