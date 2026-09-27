<?php

namespace Tests\Unit\Support;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use App\Support\DatosPersonales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatosPersonalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_mask_documento_shows_only_last_three_digits(): void
    {
        $this->assertSame('•••••678', DatosPersonales::maskDocumento('12345678'));
    }

    public function test_dueno_cannot_view_full_documento_of_team_member(): void
    {
        $empresa = Empresa::factory()->create();

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'documento' => '87654321',
        ]);

        $this->assertFalse(DatosPersonales::canViewFullDocumento($dueno, $operario));
        $this->assertSame('•••••321', DatosPersonales::documentoParaVista($dueno, $operario));
    }

    public function test_administrativo_can_view_full_documento_of_same_empresa(): void
    {
        $empresa = Empresa::factory()->create();

        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'documento' => '11223344',
        ]);

        $this->assertTrue(DatosPersonales::canViewFullDocumento($administrativo, $operario));
        $this->assertSame('11223344', DatosPersonales::documentoParaVista($administrativo, $operario));
    }

    public function test_user_can_view_own_documento(): void
    {
        $user = User::factory()->create([
            'documento' => '99887766',
        ]);

        $this->assertTrue(DatosPersonales::canViewFullDocumento($user, $user));
        $this->assertSame('99887766', DatosPersonales::documentoParaVista($user, $user));
    }
}
