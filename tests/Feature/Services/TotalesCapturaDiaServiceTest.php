<?php

namespace Tests\Feature\Services;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\TotalesCapturaDiaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TotalesCapturaDiaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_sin_permiso_resumen_devuelve_totales_vacios(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        Lote::factory()->forGalpon($galpon)->create([
            'estado' => LoteEstado::EnProduccion,
            'fecha_ingreso' => now()->subDays(5)->toDateString(),
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create(['tipo' => RegistroOperativoTipo::Huevos, 'huevos' => 999]);

        $totales = app(TotalesCapturaDiaService::class)->paraUsuario($operario);

        $this->assertSame([
            'huevos' => 0,
            'huevos_descarte' => 0,
            'muertes' => 0,
            'descarte_aves' => 0,
            'alimento_kg' => 0.0,
        ], $totales);
    }

    public function test_filtro_galpon_id_restringe_agregacion(): void
    {
        [$dueno, $galponA, $galponB] = $this->duenoConDosGalpones();

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $dueno)
            ->create(['tipo' => RegistroOperativoTipo::Huevos, 'huevos' => 80]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponB, $dueno)
            ->create(['tipo' => RegistroOperativoTipo::Huevos, 'huevos' => 400]);

        $service = app(TotalesCapturaDiaService::class);

        $this->assertSame(480, $service->paraUsuario($dueno)['huevos']);
        $this->assertSame(80, $service->paraUsuario($dueno, galponId: $galponA->id)['huevos']);
    }

    public function test_anulados_excluidos_en_totales_canonicos(): void
    {
        [$dueno, $galpon] = $this->duenoConGalpon();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create(['tipo' => RegistroOperativoTipo::Muertes, 'muertes' => 5]);

        $anulado = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create(['tipo' => RegistroOperativoTipo::Muertes, 'muertes' => 50]);

        $anulado->update([
            'estado' => RegistroOperativoEstado::Anulado,
            'anulado_at' => now(),
            'anulado_por' => $dueno->id,
        ]);

        $totales = app(TotalesCapturaDiaService::class)->paraUsuario($dueno);

        $this->assertSame(5, $totales['muertes']);
    }

    public function test_galpones_en_scope_respeta_multiempresa(): void
    {
        $empresaA = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $galponA = Galpon::factory()->forGranja(
            Granja::factory()->create(['empresa_id' => $empresaA->id]),
        )->create();

        Galpon::factory()->forGranja(
            Granja::factory()->create(['empresa_id' => $empresaB->id]),
        )->create();

        $dueno = User::factory()->create([
            'empresa_id' => $empresaA->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $scope = app(TotalesCapturaDiaService::class)->galponesEnScope($dueno, null, null);

        $this->assertCount(1, $scope);
        $this->assertSame($galponA->id, $scope->first()->id);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function duenoConGalpon(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        Lote::factory()->forGalpon($galpon)->create([
            'cantidad_inicial' => 2_000,
            'estado' => LoteEstado::EnProduccion,
            'fecha_ingreso' => now()->subDays(10)->toDateString(),
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        return [$dueno, $galpon];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon}
     */
    private function duenoConDosGalpones(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galponA = Galpon::factory()->forGranja($granja)->create();
        $galponB = Galpon::factory()->forGranja($granja)->create();

        foreach ([$galponA, $galponB] as $galpon) {
            Lote::factory()->forGalpon($galpon)->create([
                'cantidad_inicial' => 1_000,
                'estado' => LoteEstado::EnProduccion,
                'fecha_ingreso' => now()->subDays(7)->toDateString(),
            ]);
        }

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        return [$dueno, $galponA, $galponB];
    }
}
