<?php

namespace Tests\Unit\Services\Auth;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use App\Services\Auth\LoginCandidateResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginCandidateResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_unique_eligible_account_when_password_disambiguates(): void
    {
        $empresaA = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $userA = User::factory()->create([
            'empresa_id' => $empresaA->id,
            'documento' => '12345678',
            'password' => 'SecretA123!',
        ]);

        User::factory()->create([
            'empresa_id' => $empresaB->id,
            'documento' => '12345678',
            'password' => 'SecretB456!',
        ]);

        $resolved = app(LoginCandidateResolver::class)->resolveUniqueUser('12345678', 'SecretA123!');

        $this->assertTrue($resolved->is($userA));
    }

    public function test_rejects_ambiguous_eligible_accounts_with_same_password(): void
    {
        $password = 'SharedSecret1!';
        $empresaA = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        User::factory()->create([
            'empresa_id' => $empresaA->id,
            'documento' => '87654321',
            'password' => $password,
        ]);

        User::factory()->create([
            'empresa_id' => $empresaB->id,
            'documento' => '87654321',
            'password' => $password,
        ]);

        $this->expectException(ValidationException::class);

        app(LoginCandidateResolver::class)->resolveUniqueUser('87654321', $password);
    }

    public function test_allows_login_when_only_one_account_is_eligible_despite_blocked_duplicate(): void
    {
        $password = 'SharedSecret1!';
        $empresaActiva = Empresa::factory()->create([
            'nombre' => 'Empresa Visible',
            'estado' => EmpresaEstado::Activa,
        ]);
        $empresaSuspendida = Empresa::factory()->create([
            'nombre' => 'Empresa Bloqueada',
            'estado' => EmpresaEstado::Suspendida,
        ]);

        $eligible = User::factory()->create([
            'empresa_id' => $empresaActiva->id,
            'documento' => '11223344',
            'password' => $password,
            'rol' => UserRole::Operario,
        ]);

        User::factory()->create([
            'empresa_id' => $empresaSuspendida->id,
            'documento' => '11223344',
            'password' => $password,
        ]);

        $resolved = app(LoginCandidateResolver::class)->resolveUniqueUser('11223344', $password);

        $this->assertTrue($resolved->is($eligible));
    }

    public function test_wrong_password_uses_generic_message(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '55667788',
            'password' => 'RealSecret1!',
        ]);

        try {
            app(LoginCandidateResolver::class)->resolveUniqueUser('55667788', 'WrongPass1!');
            $this->fail('Expected ValidationException');
        } catch (ValidationException $exception) {
            $message = $exception->errors()['documento'][0];
            $this->assertSame('Credenciales incorrectas.', $message);
            $this->assertStringNotContainsString($empresa->nombre, $message);
        }
    }
}
