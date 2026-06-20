<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UserAvatarComponentTest extends TestCase
{
    public function test_renders_initials_from_full_name(): void
    {
        $html = Blade::render('<x-ui.user-avatar name="María González" />');

        $this->assertStringContainsString('MG', $html);
        $this->assertStringContainsString('role="img"', $html);
        $this->assertStringContainsString('Avatar de María González', $html);
    }

    public function test_renders_question_mark_for_empty_name(): void
    {
        $html = Blade::render('<x-ui.user-avatar name="   " />');

        $this->assertStringContainsString('?', $html);
    }

    public function test_decorative_variant_hides_from_accessibility_tree(): void
    {
        $html = Blade::render('<x-ui.user-avatar name="Ana López" decorative />');

        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringNotContainsString('role="img"', $html);
        $this->assertStringContainsString('AL', $html);
    }

    public function test_size_sm_applies_modifier_class(): void
    {
        $html = Blade::render('<x-ui.user-avatar name="Pedro Ruiz" size="sm" />');

        $this->assertStringContainsString('avicore-user-avatar--sm', $html);
        $this->assertStringContainsString('PR', $html);
    }
}
