<?php

namespace Tests\Feature\Admin;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminComercialTest extends TestCase
{
    use RefreshDatabase;

    public function test_dueno_cannot_view_comercial_module_in_v1(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get(route('dueno.comercial.index'))
            ->assertForbidden();
    }

    public function test_encargado_cannot_access_comercial_module(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $this->actingAs($encargado)
            ->get(route('encargado.comercial.index'))
            ->assertForbidden();
    }
}
