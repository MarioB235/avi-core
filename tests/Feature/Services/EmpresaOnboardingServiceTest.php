<?php

namespace Tests\Feature\Services;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\TipoHuevo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use App\Services\EmpresaOnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaOnboardingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_lists_pending_steps_for_empty_company(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'activo' => true,
            'must_change_password' => false,
        ]);

        $panel = app(EmpresaOnboardingService::class)->panelFor($administrativo);

        $this->assertTrue($panel['show']);
        $this->assertSame(4, $panel['pending_count']);
        $this->assertSame(6, $panel['total_count']);
        $this->assertSame(
            ['empresa', 'administrador', 'granja', 'galpon', 'lote', 'operario'],
            array_column($panel['items'], 'key'),
        );
        $this->assertSame('Listo', $panel['items'][0]['status']);
        $this->assertSame('Listo', $panel['items'][1]['status']);
        $this->assertSame('Pendiente', $panel['items'][2]['status']);
        $this->assertNotNull($panel['items'][2]['href']);
        $this->assertStringContainsString('/estructura', (string) $panel['items'][2]['href']);
    }

    public function test_panel_hides_when_company_is_fully_onboarded(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'activo' => true,
            'must_change_password' => false,
        ]);

        $granja = Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'activa' => true,
        ]);

        $galpon = Galpon::factory()->create([
            'empresa_id' => $empresa->id,
            'granja_id' => $granja->id,
            'activo' => true,
            'aves_actuales' => 1200,
        ]);

        Lote::factory()->create([
            'empresa_id' => $empresa->id,
            'galpon_id' => $galpon->id,
            'cantidad_inicial' => 1200,
            'tipo_huevo' => TipoHuevo::Blanco,
            'estado' => LoteEstado::Activo,
        ]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
        ]);

        $panel = app(EmpresaOnboardingService::class)->panelFor($administrativo);

        $this->assertFalse($panel['show']);
        $this->assertTrue(app(EmpresaOnboardingService::class)->isCompleteForEmpresa($empresa));
    }

    public function test_dueno_gets_lote_link_to_operario_cargar(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'activo' => true,
            'must_change_password' => false,
        ]);

        Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'activa' => true,
        ]);

        Galpon::factory()->create([
            'empresa_id' => $empresa->id,
            'granja_id' => Granja::query()->where('empresa_id', $empresa->id)->value('id'),
            'activo' => true,
            'aves_actuales' => 0,
        ]);

        $panel = app(EmpresaOnboardingService::class)->panelFor($dueno);
        $loteItem = collect($panel['items'])->firstWhere('key', 'lote');

        $this->assertNotNull($loteItem);
        $this->assertSame('Pendiente', $loteItem['status']);
        $this->assertStringContainsString('form=lote', (string) $loteItem['href']);
    }

    public function test_admin_avicore_does_not_see_onboarding_panel(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $panel = app(EmpresaOnboardingService::class)->panelFor($admin);

        $this->assertFalse($panel['show']);
    }
}
