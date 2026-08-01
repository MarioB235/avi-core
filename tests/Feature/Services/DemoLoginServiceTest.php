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

    public function test_is_enabled_when_flag_is_true(): void
    {
        config(['avicore.demo_login.enabled_flag' => true]);
        $this->app['env'] = 'staging';

        $this->assertTrue(app(DemoLoginService::class)->isEnabled());
    }

    public function test_is_disabled_when_flag_is_false(): void
    {
        config(['avicore.demo_login.enabled_flag' => false]);
        $this->app['env'] = 'local';

        $this->assertFalse(app(DemoLoginService::class)->isEnabled());
    }

    public function test_resolve_user_rejects_invalid_role(): void
    {
        try {
            app(DemoLoginService::class)->resolveUser('not-a-valid-role');
            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('demoRole', $exception->errors());
            $this->assertStringContainsString('perfil válido', $exception->errors()['demoRole'][0]);
        }
    }

    public function test_resolve_user_rejects_missing_documento_config(): void
    {
        config(['avicore.demo_login.documento' => '']);

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
            $this->assertStringContainsString('no encontrado', $exception->errors()['demoRole'][0]);
        }
    }

    public function test_resolve_user_applies_role_to_single_demo_user(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = app(DemoLoginService::class)->resolveUser(UserRole::Encargado->value);

        $this->assertSame(UserRole::Encargado, $user->rol);
        $this->assertSame('000000000', $user->documento);

        $persisted = User::query()->where('documento', '000000000')->firstOrFail();
        $this->assertSame(UserRole::Encargado, $persisted->rol);
    }

    public function test_resolve_user_rejects_missing_demo_empresa(): void
    {
        $this->seed(DatabaseSeeder::class);

        $demoUser = User::query()->where('documento', '000000000')->firstOrFail();
        Empresa::query()->where('id', $demoUser->empresa_id)->update(['codigo' => 'NOT-DEMO']);

        try {
            app(DemoLoginService::class)->resolveUser(UserRole::Dueno->value);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('demoRole', $exception->errors());
            $this->assertStringContainsString('Empresa demo no encontrada', $exception->errors()['demoRole'][0]);
        }
    }

    public function test_resolve_user_sets_admin_avicore_without_empresa(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = app(DemoLoginService::class)->resolveUser(UserRole::AdminAvicore->value);

        $this->assertSame(UserRole::AdminAvicore, $user->rol);
        $this->assertNull($user->empresa_id);
    }
}
