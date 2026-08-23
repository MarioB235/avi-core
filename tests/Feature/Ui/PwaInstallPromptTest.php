<?php

namespace Tests\Feature\Ui;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PwaInstallPromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_includes_pwa_meta_without_install_prompt_when_guest(): void
    {
        $response = $this->withPwaEnabled(installPrompt: true)
            ->get(route('login'))
            ->assertOk();

        $this->assertPwaMeta($response);
        $response
            ->assertSee('avicore-pwa-clear-session-dismiss', false)
            ->assertDontSee('avicore-pwa-install', false)
            ->assertDontSee('Instalá AviCore en tu celular', false);
    }

    public function test_login_hides_pwa_ui_when_disabled(): void
    {
        $this->withPwaEnabled(enabled: false, installPrompt: false)
            ->get(route('login'))
            ->assertOk()
            ->assertDontSee('manifest.webmanifest', false)
            ->assertDontSee('apple-touch-icon', false)
            ->assertDontSee('avicore-pwa-install', false);
    }

    public function test_login_shows_manifest_without_install_prompt_when_prompt_disabled(): void
    {
        $response = $this->withPwaEnabled(enabled: true, installPrompt: false)
            ->get(route('login'))
            ->assertOk();

        $this->assertPwaMeta($response);
        $response
            ->assertDontSee('avicore-pwa-install', false)
            ->assertDontSee('Instalá AviCore en tu celular', false);
    }

    public function test_operario_home_includes_pwa_meta_and_install_prompt_when_enabled(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $response = $this->withPwaEnabled(installPrompt: true)
            ->actingAs($operario)
            ->get(route('operario.home'))
            ->assertOk();

        $this->assertPwaMetaAndInstallPrompt($response);
    }

    public function test_admin_home_includes_pwa_meta_and_install_prompt_when_enabled(): void
    {
        $empresa = Empresa::factory()->create([
            'estado' => EmpresaEstado::Activa,
            'nombre' => 'Avícola Demo',
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $response = $this->withPwaEnabled(installPrompt: true)
            ->actingAs($dueno)
            ->get(route('dueno.home'))
            ->assertOk();

        $this->assertPwaMetaAndInstallPrompt($response);
    }

    public function test_operario_home_shows_meta_without_install_prompt_when_prompt_disabled(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $response = $this->withPwaEnabled(enabled: true, installPrompt: false)
            ->actingAs($operario)
            ->get(route('operario.home'))
            ->assertOk();

        $this->assertPwaMeta($response);
        $response
            ->assertDontSee('avicore-pwa-install', false)
            ->assertDontSee('Instalá AviCore en tu celular', false);
    }

    public function test_admin_home_shows_meta_without_install_prompt_when_prompt_disabled(): void
    {
        $empresa = Empresa::factory()->create([
            'estado' => EmpresaEstado::Activa,
            'nombre' => 'Avícola Demo',
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $response = $this->withPwaEnabled(enabled: true, installPrompt: false)
            ->actingAs($dueno)
            ->get(route('dueno.home'))
            ->assertOk();

        $this->assertPwaMeta($response);
        $response
            ->assertDontSee('avicore-pwa-install', false)
            ->assertDontSee('Instalá AviCore en tu celular', false);
    }

    private function withPwaEnabled(bool $enabled = true, bool $installPrompt = true): static
    {
        config([
            'avicore.pwa.enabled' => $enabled,
            'avicore.pwa.install_prompt' => $installPrompt,
        ]);

        return $this;
    }

    private function assertPwaMeta(TestResponse $response): void
    {
        $response
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('apple-touch-icon', false)
            ->assertSee('apple-mobile-web-app-status-bar-style', false)
            ->assertSee('images/brand/pwa-180.png', false);
    }

    private function assertPwaMetaAndInstallPrompt(TestResponse $response): void
    {
        $this->assertPwaMeta($response);

        $response
            ->assertSee('avicore-pwa-install', false)
            ->assertSee('Instalá AviCore en tu celular', false)
            ->assertSee('avicore:pwa-install-ready', false)
            ->assertSee('__avicorePwaInstall', false)
            ->assertSee('shouldShowBanner', false)
            ->assertSee('clearLegacyDismissKeys', false)
            ->assertSee('images/brand/pwa-180.png', false);
    }
}
