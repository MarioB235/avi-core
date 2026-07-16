<?php

namespace Tests\Feature\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\DemoLoginService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DemoLoginServiceTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_resolve_user_rejects_missing_role_document_config(): void
    {
        config(['avicore.demo_login.role_documents.dueno' => '']);

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

    public function test_resolve_user_rejects_role_mismatch(): void
    {
        $this->seed(DatabaseSeeder::class);

        User::query()
            ->where('documento', config('avicore.demo_login.role_documents.dueno'))
            ->update(['rol' => UserRole::Operario]);

        try {
            app(DemoLoginService::class)->resolveUser(UserRole::Dueno->value);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('demoRole', $exception->errors());
            $this->assertStringContainsString('no coincide', $exception->errors()['demoRole'][0]);
        }
    }

    public function test_resolve_user_returns_seeded_user_for_role(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = app(DemoLoginService::class)->resolveUser(UserRole::Encargado->value);

        $this->assertSame(UserRole::Encargado, $user->rol);
        $this->assertSame('400000001', $user->documento);
    }
}
