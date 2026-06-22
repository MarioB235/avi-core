<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DialogComponentTest extends TestCase
{
    public function test_dialog_renders_accessible_modal_markup(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.dialog title="Título de prueba">
                <x-slot:trigger>
                    <button type="button">Abrir</button>
                </x-slot:trigger>
                <p>Contenido</p>
            </x-ui.dialog>
        BLADE);

        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('aria-modal="true"', $html);
        $this->assertStringContainsString('aria-labelledby=', $html);
        $this->assertStringContainsString('data-dialog-initial-focus', $html);
        $this->assertStringContainsString('tabindex="-1"', $html);
        $this->assertStringContainsString('openDialog()', $html);
        $this->assertStringContainsString('closeDialog()', $html);
        $this->assertStringContainsString('trapTab($event)', $html);
        $this->assertStringContainsString('applyOpenSideEffects', $html);
        $this->assertStringContainsString('x-effect="applyOpenSideEffects(open)"', $html);
        $this->assertStringContainsString('x-on:alpine:destroy', $html);
    }

    public function test_dialog_uses_opacity_transitions_without_scale(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.dialog title="Prueba">
                <x-slot:trigger>
                    <button type="button">Abrir</button>
                </x-slot:trigger>
                <p>Contenido</p>
            </x-ui.dialog>
        BLADE);

        $this->assertStringContainsString('opacity-0', $html);
        $this->assertStringContainsString('opacity-100', $html);
        $this->assertStringNotContainsString('scale-95', $html);
        $this->assertStringNotContainsString('scale-100', $html);
    }
}
