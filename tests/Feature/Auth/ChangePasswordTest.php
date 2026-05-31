<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
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

        Livewire::test(\App\Livewire\Auth\ChangePassword::class)
            ->set('current_password', 'Temporal2026!')
            ->set('password', 'NuevaClave2026!')
            ->set('password_confirmation', 'NuevaClave2026!')
            ->call('save')
            ->assertRedirect(route('operario.home'));

        $operario->refresh();

        $this->assertFalse($operario->must_change_password);
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

        Livewire::test(\App\Livewire\Auth\ChangePassword::class)
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

        Livewire::test(\App\Livewire\Auth\ChangePassword::class)
            ->set('current_password', 'Temporal2026!')
            ->set('password', '12345678')
            ->set('password_confirmation', '12345678')
            ->call('save')
            ->assertHasErrors('password');
    }
}
