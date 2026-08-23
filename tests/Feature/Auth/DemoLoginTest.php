<?php

namespace Tests\Feature\Auth;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Livewire\Auth\Login;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class DemoLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_login_as_dueno_redirects_to_admin(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('demoRole', UserRole::Dueno->value)
            ->call('login')
            ->assertRedirect(route('dueno.home'));

        $this->assertAuthenticated();
        $this->assertSame(UserRole::Dueno, auth()->user()->rol);
    }

    public function test_demo_login_as_admin_avicore_redirects_to_admin(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('demoRole', UserRole::AdminAvicore->value)
            ->call('login')
            ->assertRedirect(route('avicore.home'));
    }

    public function test_demo_login_as_encargado_redirects_to_admin(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('demoRole', UserRole::Encargado->value)
            ->call('login')
            ->assertRedirect(route('encargado.home'));
    }

    public function test_demo_login_as_administrativo_redirects_to_admin(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('demoRole', UserRole::Administrativo->value)
            ->call('login')
            ->assertRedirect(route('administrativo.home'));
    }

    public function test_demo_login_as_operario_redirects_to_operario_home(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('demoRole', UserRole::Operario->value)
            ->call('login')
            ->assertRedirect(route('operario.home'));

        $this->assertAuthenticated();
        $this->assertSame(UserRole::Operario, auth()->user()->rol);

        $this->get(route('operario.home'))
            ->assertOk()
            ->assertSee('avicore-operario-body', false)
            ->assertSee('avicore-operario-home', false)
            ->assertDontSee('Estado inicial');
    }

    public function test_demo_login_rejects_invalid_role_enum(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('demoRole', 'not-a-valid-role')
            ->call('login')
            ->assertHasErrors('demoRole');
    }

    public function test_demo_login_fails_when_seed_user_is_missing(): void
    {
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('demoRole', UserRole::Dueno->value)
            ->call('login')
            ->assertHasErrors('demoRole');
    }

    public function test_demo_login_requires_role(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->set('demoRole', '')
            ->call('login')
            ->assertHasErrors('demoRole');
    }

    public function test_demo_login_rejects_inactive_empresa(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->enableDemoLogin();

        $demoUser = User::query()->where('documento', '000000000')->firstOrFail();
        $demoUser->empresa->update(['estado' => EmpresaEstado::Inactiva]);

        $component = Livewire::test(Login::class)
            ->set('demoRole', UserRole::Dueno->value)
            ->call('login')
            ->assertHasErrors('demoRole');

        $message = $component->errors()->first('demoRole') ?? '';
        $this->assertStringContainsString('empresa no está activa', $message);
        $this->assertGuest();
    }

    public function test_demo_login_does_not_require_documento_or_password(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->enableDemoLogin();

        Livewire::test(Login::class)
            ->assertSet('documento', '')
            ->assertSet('password', '')
            ->set('demoRole', UserRole::Dueno->value)
            ->call('login')
            ->assertRedirect(route('dueno.home'))
            ->assertHasNoErrors(['documento', 'password']);
    }

    public function test_demo_login_works_in_staging_when_flag_enabled(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->enableDemoLogin('staging');

        Livewire::test(Login::class)
            ->assertSet('demoLoginEnabled', true)
            ->set('demoRole', UserRole::Dueno->value)
            ->call('login')
            ->assertRedirect(route('dueno.home'));

        $this->assertAuthenticated();
    }

    public function test_demo_login_is_rate_limited_after_five_failed_attempts(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->enableDemoLogin();

        $demoUser = User::query()->where('documento', '000000000')->firstOrFail();
        $demoUser->empresa->update(['estado' => EmpresaEstado::Inactiva]);

        RateLimiter::clear('000000000|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            Livewire::test(Login::class)
                ->set('demoRole', UserRole::Dueno->value)
                ->call('login')
                ->assertHasErrors('demoRole');
        }

        $component = Livewire::test(Login::class)
            ->set('demoRole', UserRole::Dueno->value)
            ->call('login')
            ->assertHasErrors('demoRole');

        $message = $component->errors()->first('demoRole') ?? '';
        $this->assertStringContainsString('Demasiados intentos', $message);
        $this->assertGuest();
    }

    public function test_demo_login_is_disabled_when_flag_is_false(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->app['env'] = 'local';
        config(['avicore.demo_login.enabled_flag' => false]);

        Livewire::test(Login::class)
            ->assertSet('demoLoginEnabled', false)
            ->set('documento', '000000000')
            ->set('password', 'Avicore2026!')
            ->call('login')
            ->assertRedirect(route('dueno.home'));
    }

    private function enableDemoLogin(string $environment = 'local'): void
    {
        $this->app['env'] = $environment;
        config(['avicore.demo_login.enabled_flag' => true]);
    }
}
