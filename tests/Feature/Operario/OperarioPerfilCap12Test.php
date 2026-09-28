<?php

namespace Tests\Feature\Operario;

use App\Actions\User\UpdateProfileAction;
use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Livewire\Profile\Edit as ProfileEdit;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioPerfilCap12Test extends TestCase
{
    use RefreshDatabase;

    public function test_ayuda_tab_shows_real_support_contact(): void
    {
        config([
            'avicore.support.whatsapp' => '+5491123456789',
            'avicore.support.whatsapp_display' => '+54 9 11 2345-6789',
            'avicore.support.email' => 'soporte@avicore.com',
        ]);

        [$operario] = $this->createOperario();

        $this->actingAs($operario)
            ->get(route('operario.perfil', ['seccion' => 'ayuda']))
            ->assertOk()
            ->assertSee('Ayuda', false)
            ->assertSee('Contacto de soporte y canales para pedir asistencia', false)
            ->assertSee('Contacto de soporte', false)
            ->assertSee('soporte@avicore.com', false)
            ->assertSee('+54 9 11 2345-6789', false)
            ->assertSee('wa.me/5491123456789', false)
            ->assertSee('no podés cambiarlos desde acá', false);
    }

    public function test_update_profile_ignores_documento_rol_and_empresa(): void
    {
        [$operario, $empresa] = $this->createOperario();

        $originalDocumento = $operario->documento;
        $originalRol = $operario->rol;
        $originalEmpresaId = $operario->empresa_id;

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        app(UpdateProfileAction::class)->execute($operario, [
            'name' => 'Nombre actualizado',
            'email' => 'nuevo@demo.test',
            'documento' => '99999999',
            'rol' => UserRole::Dueno->value,
            'empresa_id' => $otraEmpresa->id,
        ]);

        $operario->refresh();

        $this->assertSame('Nombre actualizado', $operario->name);
        $this->assertSame('nuevo@demo.test', $operario->email);
        $this->assertSame($originalDocumento, $operario->documento);
        $this->assertSame($originalRol, $operario->rol);
        $this->assertSame($originalEmpresaId, $operario->empresa_id);
        $this->assertSame($empresa->id, $operario->empresa_id);
    }

    public function test_perfil_datos_form_has_no_editable_documento_rol_or_empresa_fields(): void
    {
        [$operario, $empresa] = $this->createOperario();

        Livewire::actingAs($operario)
            ->test(ProfileEdit::class)
            ->assertSee($operario->documento, false)
            ->assertSee($empresa->nombre, false)
            ->assertSee($operario->rol->label(), false)
            ->assertDontSee('wire:model="documento"', false)
            ->assertDontSee('wire:model="rol"', false)
            ->assertDontSee('wire:model="empresa', false);
    }

    /**
     * @return array{0: User, 1: Empresa}
     */
    private function createOperario(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'name' => 'Operario Demo',
            'email' => 'operario@demo.test',
        ]);

        return [$operario, $empresa];
    }
}
