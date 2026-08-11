<?php

namespace Tests\Feature\Services;

use App\Enums\EmpresaEstado;
use App\Enums\GalponEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Enums\VacunaTipo;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Models\Vacunacion;
use App\Services\OperarioGalponResumenService;
use App\Services\OperarioGalponService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OperarioGalponServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_historial_cargas_query_scopes_by_user_and_company(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        $propio = RegistroOperativo::factory()
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

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();
        $operarioAjeno = User::factory()->create([
            'empresa_id' => $otraEmpresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponAjeno, $operarioAjeno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 1200,
            ]);

        $registros = app(OperarioGalponService::class)
            ->historialCargasQuery($operario)
            ->get();

        $this->assertCount(1, $registros);
        $this->assertTrue($registros->contains('id', $propio->id));
    }

    public function test_historial_cargas_query_filters_by_fecha(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $ayer = now()->subDay()->toDateString();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 500,
                'created_at' => now()->subDay()->setTime(8, 0),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 700,
                'created_at' => now()->setTime(10, 0),
            ]);

        $registros = app(OperarioGalponService::class)
            ->historialCargasQuery($operario, $ayer)
            ->get();

        $this->assertCount(1, $registros);
        $this->assertSame(500, $registros->first()->huevos);
    }

    public function test_historial_paginado_merges_vacunaciones_newest_first(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $lote = Lote::factory()->forGalpon($galpon)->create([
            'codigo' => 'L-VAC',
            'estado' => LoteEstado::EnProduccion,
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 900,
                'created_at' => now()->subHour(),
            ]);

        Vacunacion::factory()
            ->forLote($lote, $operario)
            ->create([
                'vacuna' => VacunaTipo::Gumboro,
                'created_at' => now(),
            ]);

        $items = app(OperarioGalponService::class)
            ->historialPaginado($operario)
            ->items();

        $this->assertCount(2, $items);
        $this->assertTrue($items[0]->esVacunacion);
        $this->assertStringContainsString('Gumboro', $items[0]->label);
        $this->assertFalse($items[1]->esVacunacion);
        $this->assertStringContainsString('900 huevos aptos', $items[1]->label);
    }

    public function test_historial_paginado_filters_vacunaciones_by_fecha(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $lote = Lote::factory()->forGalpon($galpon)->create([
            'estado' => LoteEstado::EnProduccion,
        ]);
        $ayer = now()->subDay()->toDateString();

        Vacunacion::factory()
            ->forLote($lote, $operario)
            ->create([
                'vacuna' => VacunaTipo::Newcastle,
                'created_at' => now()->subDay()->setTime(9, 0),
            ]);

        Vacunacion::factory()
            ->forLote($lote, $operario)
            ->create([
                'vacuna' => VacunaTipo::Pox,
                'created_at' => now()->setTime(11, 0),
            ]);

        $items = app(OperarioGalponService::class)
            ->historialPaginado($operario, $ayer)
            ->items();

        $this->assertCount(1, $items);
        $this->assertStringContainsString('Newcastle', $items[0]->label);
    }

    public function test_historial_paginado_excludes_vacunaciones_from_other_company(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $lote = Lote::factory()->forGalpon($galpon)->create([
            'estado' => LoteEstado::EnProduccion,
        ]);

        Vacunacion::factory()
            ->forLote($lote, $operario)
            ->create(['vacuna' => VacunaTipo::Bronquitis]);

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();
        $operarioAjeno = User::factory()->create([
            'empresa_id' => $otraEmpresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);
        $loteAjeno = Lote::factory()->forGalpon($galponAjeno)->create([
            'estado' => LoteEstado::EnProduccion,
        ]);

        Vacunacion::factory()
            ->forLote($loteAjeno, $operarioAjeno)
            ->create(['vacuna' => VacunaTipo::Pox]);

        $items = app(OperarioGalponService::class)
            ->historialPaginado($operario)
            ->items();

        $this->assertCount(1, $items);
        $this->assertStringContainsString('Bronquitis', $items[0]->label);
    }

    public function test_historial_paginado_limits_rows_per_page(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        for ($i = 0; $i < 25; $i++) {
            RegistroOperativo::factory()
                ->forGalponAndUser($galpon, $operario)
                ->create([
                    'tipo' => RegistroOperativoTipo::Huevos,
                    'huevos' => 100 + $i,
                    'created_at' => now()->subMinutes($i),
                ]);
        }

        $paginator = app(OperarioGalponService::class)->historialPaginado($operario, perPage: 20);

        $this->assertSame(25, $paginator->total());
        $this->assertCount(20, $paginator->items());
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

    public function test_galpon_disponible_para_usuario_returns_null_for_other_company(): void
    {
        [$operario] = $this->createOperarioConGalpon();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();

        $this->assertNull(
            app(OperarioGalponService::class)->galponDisponibleParaUsuario($operario, $galponAjeno->id)
        );
    }

    public function test_galpon_disponible_para_usuario_returns_null_when_unavailable(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);

        $this->assertNull(
            app(OperarioGalponService::class)->galponDisponibleParaUsuario($operario, $galpon->id)
        );
    }

    public function test_galpon_disponible_para_usuario_returns_scoped_galpon(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();

        $resolved = app(OperarioGalponService::class)->galponDisponibleParaUsuario($operario, $galpon->id);

        $this->assertNotNull($resolved);
        $this->assertSame($galpon->id, $resolved->id);
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

    public function test_operario_galpon_services_are_scoped_per_request(): void
    {
        $this->assertSame(
            app(OperarioGalponService::class),
            app(OperarioGalponService::class),
        );

        $this->assertSame(
            app(OperarioGalponResumenService::class),
            app(OperarioGalponResumenService::class),
        );
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
