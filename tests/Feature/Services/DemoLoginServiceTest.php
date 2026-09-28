<?php

namespace Tests\Feature\Services;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use App\Services\DemoLoginService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DemoLoginServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_enabled_when_flag_is_true_and_demo_empresa_exists(): void
    {
        $this->seed(DatabaseSeeder::class);
        config(['avicore.demo_login.enabled_flag' => true]);
        $this->app['env'] = 'staging';

        $this->assertTrue(app(DemoLoginService::class)->isEnabled());
    }

    public function test_is_disabled_when_flag_is_false(): void
    {
        $this->seed(DatabaseSeeder::class);
        config(['avicore.demo_login.enabled_flag' => false]);
        $this->app['env'] = 'local';

        $service = app(DemoLoginService::class);

        $this->assertFalse($service->isEnabled());
        $this->assertFalse($service->isRequestedButUnavailable());
    }

    public function test_is_disabled_in_production_even_when_flag_is_true(): void
    {
        $this->seed(DatabaseSeeder::class);
        config(['avicore.demo_login.enabled_flag' => true]);
        $this->app['env'] = 'production';

        $this->assertFalse(app(DemoLoginService::class)->isEnabled());
    }

    public function test_setup_is_pending_when_demo_empresa_is_missing(): void
    {
        config(['avicore.demo_login.enabled_flag' => true]);
        $this->app['env'] = 'staging';

        $service = app(DemoLoginService::class);

        $this->assertTrue($service->isEnabled());
        $this->assertTrue($service->isRequestedButUnavailable());
    }

    public function test_setup_is_pending_when_demo_users_are_missing(): void
    {
        Empresa::factory()->create(['codigo' => 'DEMO']);
        config(['avicore.demo_login.enabled_flag' => true]);
        $this->app['env'] = 'staging';

        $service = app(DemoLoginService::class);

        $this->assertTrue($service->isEnabled());
        $this->assertTrue($service->isRequestedButUnavailable());
    }

    public function test_setup_is_pending_when_partial_seed_users_exist(): void
    {
        $this->seed(DatabaseSeeder::class);
        User::query()->where('documento', '11111111')->delete();
        config(['avicore.demo_login.enabled_flag' => true]);
        $this->app['env'] = 'staging';

        $service = app(DemoLoginService::class);

        $this->assertTrue($service->isEnabled());
        $this->assertTrue($service->isRequestedButUnavailable());
    }

    public function test_resolve_user_rejects_invalid_role(): void
    {
        $this->seed(DatabaseSeeder::class);

        try {
            app(DemoLoginService::class)->resolveUser('not-a-valid-role');
            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('demoRole', $exception->errors());
            $this->assertStringContainsString('perfil válido', $exception->errors()['demoRole'][0]);
        }
    }

    public function test_resolve_user_rejects_missing_role_documento_config(): void
    {
        $this->seed(DatabaseSeeder::class);
        config(['avicore.demo_login.role_documentos.dueno' => '']);

        try {
            app(DemoLoginService::class)->resolveUser(UserRole::Dueno->value);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('demoRole', $exception->errors());
            $this->assertStringContainsString('no hay usuario demo configurado', strtolower($exception->errors()['demoRole'][0]));
        }
    }

    public function test_resolve_user_rejects_missing_seed_user(): void
    {
        try {
            app(DemoLoginService::class)->resolveUser(UserRole::Dueno->value);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('demoRole', $exception->errors());
            $this->assertStringContainsString('faltan datos demo', strtolower($exception->errors()['demoRole'][0]));
            $this->assertStringContainsString('migrate --seed', $exception->errors()['demoRole'][0]);
        }
    }

    public function test_resolve_user_returns_dedicated_user_per_role_without_mutating_others(): void
    {
        $this->seed(DatabaseSeeder::class);

        $duenoBefore = User::query()->where('documento', '000000000')->firstOrFail();
        $this->assertSame(UserRole::Dueno, $duenoBefore->rol);

        $encargado = app(DemoLoginService::class)->resolveUser(UserRole::Encargado->value);

        $this->assertSame(UserRole::Encargado, $encargado->rol);
        $this->assertSame('55555555', $encargado->documento);

        $duenoAfter = User::query()->where('documento', '000000000')->firstOrFail();
        $this->assertSame(UserRole::Dueno, $duenoAfter->rol);
    }

    public function test_resolve_user_rejects_user_outside_demo_empresa(): void
    {
        $this->seed(DatabaseSeeder::class);
        config(['avicore.demo_login.role_documentos.dueno' => '88888888']);

        $realEmpresa = Empresa::factory()->create(['codigo' => 'REAL']);
        User::factory()->create([
            'empresa_id' => $realEmpresa->id,
            'documento' => '88888888',
            'rol' => UserRole::Dueno,
            'activo' => true,
        ]);

        try {
            app(DemoLoginService::class)->resolveUser(UserRole::Dueno->value);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('demoRole', $exception->errors());
            $this->assertStringContainsString('Avícola Demo', $exception->errors()['demoRole'][0]);
        }
    }

    public function test_resolve_user_sets_admin_avicore_without_empresa(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = app(DemoLoginService::class)->resolveUser(UserRole::AdminAvicore->value);

        $this->assertSame(UserRole::AdminAvicore, $user->rol);
        $this->assertSame('900000000', $user->documento);
        $this->assertNull($user->empresa_id);
    }

    public function test_resolve_user_rejects_admin_avicore_with_empresa(): void
    {
        $this->seed(DatabaseSeeder::class);

        $demoEmpresa = Empresa::query()->where('codigo', 'DEMO')->firstOrFail();
        User::query()->where('documento', '900000000')->update([
            'empresa_id' => $demoEmpresa->id,
        ]);

        try {
            app(DemoLoginService::class)->resolveUser(UserRole::AdminAvicore->value);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('demoRole', $exception->errors());
            $this->assertStringContainsString('no es válido', $exception->errors()['demoRole'][0]);
        }
    }
}
