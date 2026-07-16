<?php

namespace Tests\Feature\Ui;

use App\Livewire\Auth\Login;
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

    public function test_login_renders_demo_role_select_in_local_environment(): void
    {
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
        $this->assertMatchesRegularExpression('/id="documento"[^>]*disabled="disabled"/s', $html);
        $this->assertMatchesRegularExpression('/id="password"[^>]*disabled="disabled"/s', $html);
        $this->assertMatchesRegularExpression('/type="button"\s+disabled\b/s', $html);
    }

    public function test_login_hides_demo_role_select_outside_local_environment(): void
    {
        config(['avicore.demo_login.enabled_flag' => true]);

        $html = Livewire::test(Login::class)->html();

        $this->assertStringNotContainsString('name="demoRole"', $html);
        $this->assertStringNotContainsString("entangle('demoRole')", $html);
        $this->assertDoesNotMatchRegularExpression('/id="documento"[^>]*disabled="disabled"/s', $html);
        $this->assertDoesNotMatchRegularExpression('/id="password"[^>]*disabled="disabled"/s', $html);
    }
}
