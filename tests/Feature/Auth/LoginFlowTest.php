<?php

namespace Tests\Feature\Auth;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Livewire\Auth\Login;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class LoginFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin(): void
    {
        $this->get(route('admin.home'))->assertRedirect(route('login'));
    }

    public function test_admin_avicore_can_login_and_reach_admin_panel(): void
    {
        $user = User::factory()->adminAvicore()->create([
            'documento' => '900000002',
            'password' => 'Avicore2026!',
            'must_change_password' => false,
        ]);

        Livewire::test(Login::class)
            ->set('documento', '900000002')
            ->set('password', 'Avicore2026!')
            ->call('login')
            ->assertRedirect(route('admin.home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_dueno_can_login_and_reach_admin_panel(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '55555555',
            'password' => 'Secret123!',
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        Livewire::test(Login::class)
            ->set('documento', '55555555')
            ->set('password', 'Secret123!')
            ->call('login')
            ->assertRedirect(route('admin.home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_operario_with_temporary_password_must_change_password(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '77777777',
            'password' => 'Temporal2026!',
            'rol' => UserRole::Operario,
            'must_change_password' => true,
        ]);

        Livewire::test(Login::class)
            ->set('documento', '77777777')
            ->set('password', 'Temporal2026!')
            ->call('login')
            ->assertRedirect(route('password.change'));
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '11111111',
            'password' => 'Secret123!',
        ]);

        RateLimiter::clear('11111111|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            Livewire::test(Login::class)
                ->set('documento', '11111111')
                ->set('password', 'WrongPassword!')
                ->call('login')
                ->assertHasErrors('documento');
        }

        $component = Livewire::test(Login::class)
            ->set('documento', '11111111')
            ->set('password', 'WrongPassword!')
            ->call('login')
            ->assertHasErrors('documento');

        $message = $component->errors()->first('documento') ?? '';
        $this->assertStringContainsString('Demasiados intentos', $message);
    }

    public function test_authenticated_user_is_redirected_from_login(): void
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route($user->homeRouteName()));
    }

    public function test_duplicate_document_with_same_password_is_rejected(): void
    {
        $empresaA = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $password = 'Secret123!';

        User::factory()->create([
            'empresa_id' => $empresaA->id,
            'documento' => '33333333',
            'password' => $password,
        ]);

        User::factory()->create([
            'empresa_id' => $empresaB->id,
            'documento' => '33333333',
            'password' => $password,
        ]);

        $component = Livewire::test(Login::class)
            ->set('documento', '33333333')
            ->set('password', $password)
            ->call('login')
            ->assertHasErrors('documento');

        $message = $component->errors()->first('documento') ?? '';
        $this->assertStringContainsString('administrador', $message);

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $empresa = Empresa::factory()->create();

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '88888888',
            'password' => 'Secret123!',
            'activo' => false,
        ]);

        Livewire::test(Login::class)
            ->set('documento', '88888888')
            ->set('password', 'Secret123!')
            ->call('login')
            ->assertHasErrors('documento');
    }

    public function test_inactive_empresa_blocks_login(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Inactiva]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '66666666',
            'password' => 'Secret123!',
        ]);

        Livewire::test(Login::class)
            ->set('documento', '66666666')
            ->set('password', 'Secret123!')
            ->call('login')
            ->assertHasErrors('documento');
    }

    public function test_suspended_empresa_blocks_login(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Suspendida]);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '99999999',
            'password' => 'Secret123!',
        ]);

        Livewire::test(Login::class)
            ->set('documento', '99999999')
            ->set('password', 'Secret123!')
            ->call('login')
            ->assertHasErrors('documento');
    }

    public function test_user_with_must_change_password_is_redirected_from_admin(): void
    {
        $empresa = Empresa::factory()->create();

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => true,
        ]);

        $this->actingAs($dueno)
            ->get(route('admin.home'))
            ->assertRedirect(route('password.change'));
    }

    public function test_operario_cannot_access_admin_panel(): void
    {
        $empresa = Empresa::factory()->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('admin.home'))
            ->assertRedirect(route('operario.home'));
    }

    public function test_dev_routes_are_unavailable_outside_local_environment(): void
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($user)
            ->get('/dev/admin-layout')
            ->assertNotFound();
    }
}
