<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Livewire\Auth\ChangePassword;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_can_change_temporary_password(): void
    {
        $empresa = Empresa::factory()->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'password' => 'Temporal2026!',
            'rol' => UserRole::Operario,
            'must_change_password' => true,
        ]);

        $this->actingAs($operario);

        Livewire::test(ChangePassword::class)
            ->set('current_password', 'Temporal2026!')
            ->set('password', 'NuevaClave2026!')
            ->set('password_confirmation', 'NuevaClave2026!')
            ->call('save')
            ->assertRedirect(route('operario.home'));

        $operario->refresh();

        $this->assertFalse($operario->must_change_password);
    }

    public function test_admin_can_change_temporary_password_and_reach_admin_home(): void
    {
        $empresa = Empresa::factory()->create();

        $admin = User::factory()->create([
            'empresa_id' => $empresa->id,
            'password' => 'Temporal2026!',
            'rol' => UserRole::Dueno,
            'must_change_password' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(ChangePassword::class)
            ->set('current_password', 'Temporal2026!')
            ->set('password', 'NuevaClave2026!')
            ->set('password_confirmation', 'NuevaClave2026!')
            ->call('save')
            ->assertRedirect(route('admin.home'));

        $admin->refresh();

        $this->assertFalse($admin->must_change_password);
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $empresa = Empresa::factory()->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'password' => 'Temporal2026!',
            'rol' => UserRole::Operario,
            'must_change_password' => true,
        ]);

        $this->actingAs($operario);

        Livewire::test(ChangePassword::class)
            ->set('current_password', 'ClaveIncorrecta!')
            ->set('password', 'NuevaClave2026!')
            ->set('password_confirmation', 'NuevaClave2026!')
            ->call('save')
            ->assertHasErrors('current_password');
    }

    public function test_change_password_rejects_weak_password(): void
    {
        $empresa = Empresa::factory()->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'password' => 'Temporal2026!',
            'rol' => UserRole::Operario,
            'must_change_password' => true,
        ]);

        $this->actingAs($operario);

        Livewire::test(ChangePassword::class)
            ->set('current_password', 'Temporal2026!')
            ->set('password', '12345678')
            ->set('password_confirmation', '12345678')
            ->call('save')
            ->assertHasErrors('password');
    }

    public function test_change_password_rejects_same_as_current_password(): void
    {
        $empresa = Empresa::factory()->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'password' => 'Temporal2026!',
            'rol' => UserRole::Operario,
            'must_change_password' => true,
        ]);

        $this->actingAs($operario);

        Livewire::test(ChangePassword::class)
            ->set('current_password', 'Temporal2026!')
            ->set('password', 'Temporal2026!')
            ->set('password_confirmation', 'Temporal2026!')
            ->call('save')
            ->assertHasErrors('password');
    }

    public function test_change_password_rejects_mismatched_confirmation(): void
    {
        $empresa = Empresa::factory()->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'password' => 'Temporal2026!',
            'rol' => UserRole::Operario,
            'must_change_password' => true,
        ]);

        $this->actingAs($operario);

        Livewire::test(ChangePassword::class)
            ->set('current_password', 'Temporal2026!')
            ->set('password', 'NuevaClave2026!')
            ->set('password_confirmation', 'OtraClave2026!')
            ->call('save')
            ->assertHasErrors('password');
    }

    public function test_change_password_screen_shows_security_warning_and_support_dialog(): void
    {
        config([
            'avicore.support.whatsapp' => '+5491123456789',
            'avicore.support.whatsapp_display' => '+54 9 11 2345-6789',
            'avicore.support.email' => 'soporte@avicore.com',
        ]);

        $empresa = Empresa::factory()->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'password' => 'Temporal2026!',
            'rol' => UserRole::Operario,
            'must_change_password' => true,
        ]);

        $this->actingAs($operario);

        $html = Livewire::test(ChangePassword::class)->html();

        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('border-avicore-warning', $html);
        $this->assertMatchesRegularExpression(
            '/Por seguridad.*nueva.*continuar\./u',
            strip_tags($html),
        );
        $this->assertStringContainsString('Contraseña actual', $html);
        $this->assertStringContainsString('Nueva contraseña', $html);
        $this->assertStringContainsString('M2.586 17.414', $html);
        $this->assertStringContainsString('m9 12 2 2 4-4', $html);
        $this->assertStringContainsString('¿Problemas con tu contraseña temporal?', $html);
        $this->assertStringContainsString('wa.me/5491123456789', $html);
        $this->assertStringContainsString('mailto:soporte@avicore.com', $html);
        $this->assertStringContainsString('openDialog()', $html);
    }
}
