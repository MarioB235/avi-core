<?php

namespace Tests\Feature\Auth;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Livewire\Auth\Login;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MultiEmpresaLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_selects_correct_company_when_document_is_shared_but_passwords_differ(): void
    {
        $empresaA = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $userB = User::factory()->create([
            'empresa_id' => $empresaB->id,
            'documento' => '13579246',
            'password' => 'PasswordB1!',
            'rol' => UserRole::Administrativo,
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresaA->id,
            'documento' => '13579246',
            'password' => 'PasswordA1!',
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        Livewire::test(Login::class)
            ->set('documento', '13579246')
            ->set('password', 'PasswordB1!')
            ->call('login')
            ->assertRedirect(route('administrativo.home'));

        $this->assertAuthenticatedAs($userB);
    }

    public function test_login_succeeds_when_blocked_duplicate_shares_document_and_password(): void
    {
        $empresaActiva = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $empresaSuspendida = Empresa::factory()->create(['estado' => EmpresaEstado::Suspendida]);
        $password = 'SharedOk123!';

        $operario = User::factory()->create([
            'empresa_id' => $empresaActiva->id,
            'documento' => '24681357',
            'password' => $password,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        User::factory()->create([
            'empresa_id' => $empresaSuspendida->id,
            'documento' => '24681357',
            'password' => $password,
            'rol' => UserRole::Operario,
        ]);

        Livewire::test(Login::class)
            ->set('documento', '24681357')
            ->set('password', $password)
            ->call('login')
            ->assertRedirect(route('operario.home'));

        $this->assertAuthenticatedAs($operario);
    }

    public function test_ambiguous_login_error_does_not_reveal_company_names(): void
    {
        $password = 'SamePass123!';
        $empresaA = Empresa::factory()->create([
            'nombre' => 'Granja Norte SRL',
            'estado' => EmpresaEstado::Activa,
        ]);
        $empresaB = Empresa::factory()->create([
            'nombre' => 'Avícola Sur SA',
            'estado' => EmpresaEstado::Activa,
        ]);

        User::factory()->create([
            'empresa_id' => $empresaA->id,
            'documento' => '99887766',
            'password' => $password,
        ]);

        User::factory()->create([
            'empresa_id' => $empresaB->id,
            'documento' => '99887766',
            'password' => $password,
        ]);

        $component = Livewire::test(Login::class)
            ->set('documento', '99887766')
            ->set('password', $password)
            ->call('login')
            ->assertHasErrors('documento');

        $message = $component->errors()->first('documento') ?? '';

        $this->assertStringNotContainsString('Granja Norte', $message);
        $this->assertStringNotContainsString('Avícola Sur', $message);
        $this->assertGuest();
    }
}
