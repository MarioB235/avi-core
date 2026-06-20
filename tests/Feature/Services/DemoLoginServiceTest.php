<?php

namespace Tests\Feature\Services;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use App\Services\DemoLoginService;
use Database\Seeders\AvicoreAuthSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DemoLoginServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_user_throws_for_invalid_role_value(): void
    {
        $this->expectException(ValidationException::class);

        try {
            app(DemoLoginService::class)->resolveUser('not-a-valid-role');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('demoRole', $exception->errors());
            $this->assertStringContainsString('perfil válido', $exception->errors()['demoRole'][0]);

            throw $exception;
        }
    }

    public function test_resolve_user_throws_when_role_document_is_not_configured(): void
    {
        config(['avicore.demo_login.role_documents.dueno' => '']);

        $this->expectException(ValidationException::class);

        try {
            app(DemoLoginService::class)->resolveUser(UserRole::Dueno->value);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('demoRole', $exception->errors());
            $this->assertStringContainsString('no hay usuario demo configurado', strtolower($exception->errors()['demoRole'][0]));

            throw $exception;
        }
    }

    public function test_resolve_user_throws_when_seed_user_is_missing(): void
    {
        $this->expectException(ValidationException::class);

        try {
            app(DemoLoginService::class)->resolveUser(UserRole::Dueno->value);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('demoRole', $exception->errors());
            $this->assertStringContainsString('no encontrado', $exception->errors()['demoRole'][0]);

            throw $exception;
        }
    }

    public function test_resolve_user_throws_when_user_role_does_not_match_selected_profile(): void
    {
        $empresa = Empresa::factory()->create();

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => config('avicore.demo_login.role_documents.dueno'),
            'password' => 'Avicore2026!',
            'rol' => UserRole::Operario,
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(DemoLoginService::class)->resolveUser(UserRole::Dueno->value);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('demoRole', $exception->errors());
            $this->assertStringContainsString('no coincide', $exception->errors()['demoRole'][0]);

            throw $exception;
        }
    }

    public function test_resolve_user_returns_seeded_user_for_valid_role(): void
    {
        $this->seed(AvicoreAuthSeeder::class);

        $user = app(DemoLoginService::class)->resolveUser(UserRole::Encargado->value);

        $this->assertSame(UserRole::Encargado, $user->rol);
        $this->assertSame('400000001', $user->documento);
    }
}
