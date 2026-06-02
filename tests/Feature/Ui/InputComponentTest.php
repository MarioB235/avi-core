<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class InputComponentTest extends TestCase
{
    public function test_input_renders_hint_and_error_states_in_isolation(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.input
                label="Campo demo"
                name="demo"
                hint="Texto de ayuda"
                error="Mensaje de error"
            />
        BLADE);

        $this->assertStringContainsString('Campo demo', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('Mensaje de error', $html);
        $this->assertStringNotContainsString('Texto de ayuda', $html);
    }

    public function test_password_input_renders_accessible_toggle(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.input
                label="Contraseña"
                name="password"
                toggle-password
            />
        BLADE);

        $this->assertStringContainsString('avicore-password-input', $html);
        $this->assertStringContainsString('aria-pressed', $html);
        $this->assertStringContainsString('Mostrar contraseña', $html);
        $this->assertStringNotContainsString('tabindex="-1"', $html);
    }
}
