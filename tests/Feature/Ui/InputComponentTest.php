<?php

namespace Tests\Feature\Ui;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InputComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_password_field_renders_accessible_toggle_without_negative_tabindex(): void
    {
        $html = Livewire::test(Login::class)->html();

        $this->assertStringContainsString('avicore-password-input', $html);
        $this->assertStringContainsString('aria-pressed', $html);
        $this->assertStringContainsString('Mostrar contraseña', $html);
        $this->assertStringNotContainsString('tabindex="-1"', $html);
    }

    public function test_login_validation_marks_inputs_invalid_and_shows_alerts(): void
    {
        $html = Livewire::test(Login::class)
            ->set('documento', '')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['documento', 'password'])
            ->html();

        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('El documento es obligatorio.', $html);
    }

    public function test_login_failed_credentials_mark_document_field_invalid(): void
    {
        User::factory()->create([
            'documento' => '44444444',
            'password' => 'Secret123!',
        ]);

        $html = Livewire::test(Login::class)
            ->set('documento', '44444444')
            ->set('password', 'WrongPassword!')
            ->call('login')
            ->assertHasErrors('documento')
            ->html();

        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('Credenciales incorrectas.', $html);
    }
}
