<?php

namespace Tests\Feature\Services;

use App\Enums\EmpresaEstado;
use App\Enums\GalponEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\OperarioGalponService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OperarioGalponServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_maples_producidos_hoy_divides_total_eggs_by_thirty(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 1500,
            ]);

        $this->assertSame(
            50,
            app(OperarioGalponService::class)->maplesProducidosHoy($operario)
        );
    }

    public function test_galpon_actual_returns_null_for_galpon_from_other_company(): void
    {
        [$operario] = $this->createOperarioConGalpon();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();

        $operario->forceFill(['ultimo_galpon_id' => $galponAjeno->id])->save();

        $this->assertNull(
            app(OperarioGalponService::class)->galponActual($operario->fresh())
        );
    }

    public function test_galpon_actual_returns_null_when_galpon_is_unavailable(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();
        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);

        $this->assertNull(
            app(OperarioGalponService::class)->galponActual($operario->fresh())
        );
    }

    public function test_seleccionar_galpon_rejects_unavailable_galpon(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);
        $galpon->refresh();

        $this->expectException(ValidationException::class);

        app(OperarioGalponService::class)->seleccionarGalpon($operario, $galpon);
    }

    public function test_seleccionar_galpon_rejects_galpon_from_other_company(): void
    {
        [$operario] = $this->createOperarioConGalpon();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();

        $this->expectException(AuthorizationException::class);

        app(OperarioGalponService::class)->seleccionarGalpon($operario, $galponAjeno);
    }

    public function test_galpones_disponibles_only_lists_company_galpones(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        Galpon::factory()->forGranja($otraGranja)->create(['nombre' => 'Galpón ajeno']);

        $galpones = app(OperarioGalponService::class)->galponesDisponibles($operario);

        $this->assertCount(1, $galpones);
        $this->assertTrue($galpones->contains('id', $galpon->id));
        $this->assertFalse($galpones->contains('nombre', 'Galpón ajeno'));
    }

    public function test_ultimas_cargas_del_dia_scopes_by_user_and_company(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 900,
            ]);

        $otroOperario = User::factory()->create([
            'empresa_id' => $operario->empresa_id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $otroOperario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 300,
            ]);

        $cargas = app(OperarioGalponService::class)->ultimasCargasDelDia($operario);

        $this->assertCount(1, $cargas);
        $this->assertSame(900, $cargas->first()->huevos);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function createOperarioConGalpon(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        return [$operario, $galpon];
    }
}
