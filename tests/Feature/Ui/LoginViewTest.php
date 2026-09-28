<?php

namespace Tests\Feature\Ui;

use App\Enums\UserRole;
use App\Livewire\Auth\Login;
use App\Models\Empresa;
use App\Services\DemoLoginService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_renders_document_and_password_field_icons(): void
    {
        $html = Livewire::test(Login::class)->html();

        $this->assertStringContainsString('name="documento"', $html);
        $this->assertStringContainsString('name="password"', $html);
        $this->assertStringContainsString('M16 10h2', $html);
        $this->assertStringContainsString('M7 10V7', $html);
        $this->assertStringContainsString('avicore-input--leading-icon', $html);
    }

    public function test_login_renders_remember_me_checkbox(): void
    {
        $html = Livewire::test(Login::class)->html();

        $this->assertStringContainsString('Recordarme', $html);
        $this->assertStringContainsString('avicore-checkbox', $html);
        $this->assertStringContainsString('wire:model="remember"', $html);
    }

    public function test_login_shows_forgot_password_link_and_support_dialog(): void
    {
        config([
            'avicore.support.whatsapp' => '+5491123456789',
            'avicore.support.whatsapp_display' => '+54 9 11 2345-6789',
            'avicore.support.email' => 'soporte@avicore.com',
        ]);

        $html = Livewire::test(Login::class)->html();

        $this->assertStringContainsString('¿Olvidaste tu contraseña?', $html);
        $this->assertStringContainsString('Recuperar contraseña', $html);
        $this->assertStringContainsString('soporte@avicore.com', $html);
        $this->assertStringContainsString('+54 9 11 2345-6789', $html);
        $this->assertStringContainsString('wa.me/5491123456789', $html);
        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('avicore-sheet__backdrop', $html);
        $this->assertStringContainsString('openSheet()', $html);
        $this->assertStringContainsString('translate-y-full', $html);
    }

    public function test_login_renders_demo_role_select_when_flag_enabled(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->app['env'] = 'local';
        config(['avicore.demo_login.enabled_flag' => true]);

        $html = Livewire::test(Login::class)
            ->assertSet('documento', '')
            ->assertSet('password', '')
            ->html();

        $this->assertStringContainsString('name="demoRole"', $html);
        $this->assertStringContainsString("entangle('demoRole').live", $html);
        $this->assertStringContainsString('Perfil', $html);
        $this->assertStringContainsString('avicore-select-trigger', $html);
        $this->assertStringContainsString('role="listbox"', $html);
        $this->assertStringContainsString('Operario', $html);
        $this->assertStringNotContainsString('name="documento"', $html);
        $this->assertStringNotContainsString('name="password"', $html);
    }

    public function test_login_hides_demo_role_select_when_flag_disabled(): void
    {
        config(['avicore.demo_login.enabled_flag' => false]);

        $html = Livewire::test(Login::class)->html();

        $this->assertStringNotContainsString('name="demoRole"', $html);
        $this->assertStringNotContainsString("entangle('demoRole')", $html);
        $this->assertDoesNotMatchRegularExpression('/id="documento"[^>]*disabled="disabled"/s', $html);
        $this->assertDoesNotMatchRegularExpression('/id="password"[^>]*disabled="disabled"/s', $html);
    }

    public function test_login_hides_demo_role_select_in_production_even_when_flag_enabled(): void
    {
        config(['avicore.demo_login.enabled_flag' => true]);
        $this->app['env'] = 'production';

        $html = Livewire::test(Login::class)->html();

        $this->assertStringNotContainsString('name="demoRole"', $html);
        $this->assertDoesNotMatchRegularExpression('/id="documento"[^>]*disabled="disabled"/s', $html);
        $this->assertDoesNotMatchRegularExpression('/id="password"[^>]*disabled="disabled"/s', $html);
    }

    public function test_login_hides_demo_setup_pending_alert_when_flag_disabled(): void
    {
        config(['avicore.demo_login.enabled_flag' => false]);

        $html = Livewire::test(Login::class)
            ->assertSet('demoSetupPending', false)
            ->html();

        $this->assertStringNotContainsString('Todavía no podés entrar', $html);
    }

    public function test_login_shows_demo_setup_pending_alert_when_seed_users_are_missing(): void
    {
        $this->app['env'] = 'staging';
        Empresa::factory()->create(['codigo' => 'DEMO']);
        config(['avicore.demo_login.enabled_flag' => true]);

        $html = Livewire::test(Login::class)
            ->assertSet('demoSetupPending', true)
            ->html();

        $this->assertStringContainsString('Todavía no podés entrar', $html);
        $this->assertStringContainsString('usuarios de demostración', $html);
        $this->assertStringNotContainsString('migrate --seed', $html);
        $this->assertStringContainsString('name="demoRole"', $html);
        $this->assertStringNotContainsString('name="documento"', $html);
    }

    public function test_login_shows_developer_seed_hint_in_local_when_demo_setup_pending(): void
    {
        $this->app['env'] = 'local';
        Empresa::factory()->create(['codigo' => 'DEMO']);
        config(['avicore.demo_login.enabled_flag' => true]);

        $html = Livewire::test(Login::class)
            ->assertSet('demoSetupPending', true)
            ->html();

        $this->assertStringContainsString('migrate --seed', $html);
    }

    public function test_login_disables_submit_and_blocks_login_when_demo_setup_pending(): void
    {
        $this->app['env'] = 'staging';
        Empresa::factory()->create(['codigo' => 'DEMO']);
        config(['avicore.demo_login.enabled_flag' => true]);

        $component = Livewire::test(Login::class)->assertSet('demoSetupPending', true);

        $html = $component->html();
        $this->assertMatchesRegularExpression('/type="submit"[^>]*\sdisabled(?:="[^"]*")?/s', $html);

        $component
            ->set('demoRole', UserRole::Dueno->value)
            ->call('login')
            ->assertHasErrors('demoRole');

        $this->assertGuest();

        $this->assertSame(
            DemoLoginService::MESSAGE_DEMO_SEED_MISSING,
            $component->errors()->first('demoRole')
        );
    }
}
