<?php

namespace Tests\Feature\Ui;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DocumentoLabelComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_documento_label_masks_document_for_unauthorized_viewer(): void
    {
        $empresa = Empresa::factory()->create();

        $viewer = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'documento' => '20111222',
        ]);

        $subject = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'documento' => '12345678',
        ]);

        $html = Blade::render(
            '<x-ui.documento-label :user="$subject" :viewer="$viewer" />',
            compact('subject', 'viewer'),
        );

        $this->assertStringContainsString('•••••678', $html);
        $this->assertStringNotContainsString('12345678', $html);
        $this->assertStringContainsString('Documento parcial por política de privacidad operativa', $html);
    }

    public function test_documento_label_shows_full_document_for_authorized_viewer(): void
    {
        $empresa = Empresa::factory()->create();

        $viewer = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'documento' => '30111222',
        ]);

        $subject = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'documento' => '11223344',
        ]);

        $html = Blade::render(
            '<x-ui.documento-label :user="$subject" :viewer="$viewer" />',
            compact('subject', 'viewer'),
        );

        $this->assertStringContainsString('11223344', $html);
        $this->assertStringNotContainsString('política de privacidad operativa', $html);
    }

    public function test_documento_label_shows_full_document_for_self(): void
    {
        $empresa = Empresa::factory()->create();

        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'documento' => '99887766',
        ]);

        $html = Blade::render(
            '<x-ui.documento-label :user="$user" :viewer="$user" />',
            ['user' => $user],
        );

        $this->assertStringContainsString('99887766', $html);
        $this->assertStringNotContainsString('•', $html);
    }
}
