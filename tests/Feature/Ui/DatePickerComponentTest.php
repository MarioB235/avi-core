<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DatePickerComponentTest extends TestCase
{
    public function test_date_picker_renders_trigger_and_calendar_panel(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.date-picker
                label="Fecha"
                name="fecha"
                placeholder="Elegí un día"
                panel-title="Elegí un día"
                max="2026-07-14"
            />
        BLADE);

        $this->assertStringContainsString('Fecha', $html);
        $this->assertStringContainsString('avicore-date-picker-trigger', $html);
        $this->assertStringContainsString('avicore-date-picker-panel', $html);
        $this->assertStringContainsString('avicore-date-picker-grid', $html);
        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('Elegí un día', $html);
        $this->assertStringContainsString('Hoy', $html);
        $this->assertStringContainsString('aria-haspopup="dialog"', $html);
        $this->assertStringContainsString('dayAriaLabel', $html);
        $this->assertStringNotContainsString('type="date"', $html);
    }

    public function test_date_picker_includes_month_navigation_and_bounds(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.date-picker
                name="fecha"
                min="2026-01-01"
                max="2026-07-14"
                today="2026-07-14"
            />
        BLADE);

        $this->assertStringContainsString('prevMonth', $html);
        $this->assertStringContainsString('nextMonth', $html);
        $this->assertStringContainsString('canGoPrev', $html);
        $this->assertStringContainsString('canGoNext', $html);
        $this->assertStringContainsString('2026-07-14', $html);
        $this->assertStringContainsString('2026-01-01', $html);
        $this->assertStringContainsString('m15 18-6-6 6-6', $html);
    }

    public function test_date_picker_renders_error_state(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.date-picker
                label="Día"
                name="fecha"
                error="La fecha no puede ser futura."
            />
        BLADE);

        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('La fecha no puede ser futura.', $html);
        $this->assertStringContainsString('avicore-date-picker-trigger--error', $html);
    }

    public function test_date_picker_renders_error_from_errors_bag(): void
    {
        $this->withViewErrors([
            'fecha' => 'La fecha seleccionada no es válida.',
        ]);

        $html = Blade::render(<<<'BLADE'
            <x-ui.date-picker
                name="fecha"
            />
        BLADE);

        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('La fecha seleccionada no es válida.', $html);
        $this->assertStringContainsString('avicore-date-picker-trigger--error', $html);
    }

    public function test_date_picker_renders_hint_when_no_error(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-ui.date-picker
                name="fecha"
                hint="Solo días con cargas."
            />
        BLADE);

        $this->assertStringContainsString('Solo días con cargas.', $html);
        $this->assertStringNotContainsString('role="alert"', $html);
    }
}
