<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Livewire\Auth\Login;
use Database\Seeders\AvicoreAuthSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DemoLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_login_as_dueno_redirects_to_admin(): void
    {
        $this->seedDemoUsers();
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('documento', '000000000')
            ->set('password', 'Avicore2026!')
            ->set('demoRole', UserRole::Dueno->value)
            ->call('login')
            ->assertRedirect(route('admin.home'));

        $this->assertAuthenticated();
        $this->assertSame(UserRole::Dueno, auth()->user()->rol);
        $this->assertSame('100000001', auth()->user()->documento);
    }

    public function test_demo_login_as_admin_avicore_redirects_to_admin(): void
    {
        $this->seedDemoUsers();
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('documento', '000000000')
            ->set('password', 'Avicore2026!')
            ->set('demoRole', UserRole::AdminAvicore->value)
            ->call('login')
            ->assertRedirect(route('admin.home'));

        $this->assertAuthenticated();
        $this->assertSame(UserRole::AdminAvicore, auth()->user()->rol);
        $this->assertSame('900000001', auth()->user()->documento);
    }

    public function test_demo_login_as_encargado_redirects_to_admin(): void
    {
        $this->seedDemoUsers();
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('documento', '000000000')
            ->set('password', 'Avicore2026!')
            ->set('demoRole', UserRole::Encargado->value)
            ->call('login')
            ->assertRedirect(route('admin.home'));

        $this->assertAuthenticated();
        $this->assertSame(UserRole::Encargado, auth()->user()->rol);
        $this->assertSame('400000001', auth()->user()->documento);
    }

    public function test_demo_login_as_administrativo_redirects_to_admin(): void
    {
        $this->seedDemoUsers();
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('documento', '000000000')
            ->set('password', 'Avicore2026!')
            ->set('demoRole', UserRole::Administrativo->value)
            ->call('login')
            ->assertRedirect(route('admin.home'));

        $this->assertAuthenticated();
        $this->assertSame(UserRole::Administrativo, auth()->user()->rol);
        $this->assertSame('300000001', auth()->user()->documento);
    }

    public function test_demo_login_as_operario_redirects_to_operario_home(): void
    {
        $this->seedDemoUsers();
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('documento', '000000000')
            ->set('password', 'Avicore2026!')
            ->set('demoRole', UserRole::Operario->value)
            ->call('login')
            ->assertRedirect(route('operario.home'));

        $this->assertAuthenticated();
        $this->assertSame(UserRole::Operario, auth()->user()->rol);
    }

    public function test_demo_login_rejects_invalid_role_enum(): void
    {
        $this->seedDemoUsers();
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('documento', '000000000')
            ->set('password', 'Avicore2026!')
            ->set('demoRole', 'not-a-valid-role')
            ->call('login')
            ->assertHasErrors('demoRole');

        $this->assertGuest();
    }

    public function test_demo_login_fails_when_seed_user_is_missing(): void
    {
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('documento', '000000000')
            ->set('password', 'Avicore2026!')
            ->set('demoRole', UserRole::Dueno->value)
            ->call('login')
            ->assertHasErrors('demoRole');

        $this->assertGuest();
    }

    public function test_demo_login_requires_role_when_using_demo_credentials(): void
    {
        $this->seedDemoUsers();
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('documento', '000000000')
            ->set('password', 'Avicore2026!')
            ->set('demoRole', '')
            ->call('login')
            ->assertHasErrors('demoRole');

        $this->assertGuest();
    }

    public function test_demo_credentials_do_not_bypass_login_outside_local_environment(): void
    {
        $this->seedDemoUsers();
        config(['avicore.demo_login.enabled_flag' => true]);

        Livewire::test(Login::class)
            ->set('documento', '000000000')
            ->set('password', 'Avicore2026!')
            ->set('demoRole', UserRole::Dueno->value)
            ->call('login')
            ->assertHasErrors('documento');

        $this->assertGuest();
    }

    public function test_demo_credentials_do_not_bypass_login_when_disabled(): void
    {
        $this->seedDemoUsers();
        $this->enableDemoLogin();
        config(['avicore.demo_login.enabled_flag' => false]);

        Livewire::test(Login::class)
            ->set('documento', '000000000')
            ->set('password', 'Avicore2026!')
            ->set('demoRole', UserRole::Dueno->value)
            ->call('login')
            ->assertHasErrors('documento');

        $this->assertGuest();
    }

    public function test_real_document_login_still_works_with_demo_enabled(): void
    {
        $this->seedDemoUsers();
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('documento', '900000001')
            ->set('password', 'Avicore2026!')
            ->call('login')
            ->assertRedirect(route('admin.home'));

        $this->assertAuthenticated();
        $this->assertSame(UserRole::AdminAvicore, auth()->user()->rol);
    }

    private function seedDemoUsers(): void
    {
        $this->seed(AvicoreAuthSeeder::class);
    }

    private function enableDemoLogin(): void
    {
        $this->app['env'] = 'local';
        config(['avicore.demo_login.enabled_flag' => true]);
    }
}
