<?php

namespace Tests\Feature\Services;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\AdminHomeService;
use App\Services\OperarioGalponResumenService;
use App\Support\HuevosUnidad;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaUnidadesConsistenciaTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_resumen_uses_empresa_unit_configuration(): void
    {
        $empresa = Empresa::factory()->create([
            'estado' => EmpresaEstado::Activa,
            'configuracion' => [
                'unidades' => [
                    'huevos_por_maple' => 25,
                    'maples_por_cajon' => 10,
                ],
            ],
        ]);

        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();
        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
        ]);

        Lote::factory()->create([
            'empresa_id' => $empresa->id,
            'galpon_id' => $galpon->id,
            'estado' => LoteEstado::Activo,
            'fecha_ingreso' => now()->subDays(3),
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 1000,
            ]);

        $resumen = app(OperarioGalponResumenService::class)->resumen($galpon);

        $this->assertSame(40, $resumen['maples_hoy']);
        $this->assertSame(0, $resumen['huevos_hoy_desglose']['huevos']);
    }

    public function test_admin_home_pulso_uses_empresa_unit_configuration(): void
    {
        $empresa = Empresa::factory()->create([
            'estado' => EmpresaEstado::Activa,
            'configuracion' => [
                'unidades' => [
                    'huevos_por_maple' => 25,
                    'maples_por_cajon' => 10,
                ],
            ],
        ]);

        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();
        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        Lote::factory()->create([
            'empresa_id' => $empresa->id,
            'galpon_id' => $galpon->id,
            'estado' => LoteEstado::Activo,
            'fecha_ingreso' => now()->subDays(2),
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 65,
            ]);

        $pulso = app(AdminHomeService::class)->pulsoPanel($dueno);

        $this->assertStringContainsString('2 maples', $pulso['unidades_cajas_maples']);
        $this->assertStringContainsString('15 huevos', $pulso['unidades_hoy']);
    }

    public function test_huevos_unidad_facade_matches_empresa_service(): void
    {
        $empresa = Empresa::factory()->create([
            'configuracion' => [
                'unidades' => [
                    'huevos_por_maple' => 20,
                    'maples_por_cajon' => 8,
                ],
            ],
        ]);

        $huevos = 175;

        $this->assertSame(
            HuevosUnidad::para($empresa)->etiquetaSoloCajasMaples($huevos),
            HuevosUnidad::etiquetaSoloCajasMaples($huevos, $empresa),
        );

        $this->assertSame('1 caja + 15 huevos sueltos', HuevosUnidad::etiquetaSoloCajasMaples($huevos, $empresa));
    }
}
