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
use App\Services\ResumenGraficosSemanalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumenGraficosSemanalesServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sin_galpones_devuelve_siete_filas_omision_res08(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $graficos = app(ResumenGraficosSemanalesService::class)->for($dueno);

        $this->assertCount(7, $graficos['tabla']);
        $this->assertSame('—', $graficos['tabla'][0]['huevos_aptos']['display']);
        $this->assertSame('omision', $graficos['tabla'][0]['huevos_aptos']['estado']);
        $this->assertCount(7, $graficos['series']['huevos_aptos']);
    }

    public function test_filtro_granja_limita_totales_res08(): void
    {
        [$dueno, $galponA, $galponB] = $this->duenoConDosGalpones();

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 111,
                'created_at' => now(),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponB, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 999,
                'created_at' => now(),
            ]);

        $granjaAId = (int) $galponA->granja_id;
        $hoy = now()->toDateString();

        $soloA = app(ResumenGraficosSemanalesService::class)->for($dueno, $granjaAId, null);
        $filaA = collect($soloA['tabla'])->firstWhere('date', $hoy);
        $this->assertNotNull($filaA);
        $this->assertSame(111, $filaA['huevos_aptos']['value']);

        $soloB = app(ResumenGraficosSemanalesService::class)->for($dueno, (int) $galponB->granja_id, null);
        $filaB = collect($soloB['tabla'])->firstWhere('date', $hoy);
        $this->assertNotNull($filaB);
        $this->assertSame(999, $filaB['huevos_aptos']['value']);
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon}
     */
    private function duenoConDosGalpones(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaA = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galponA = Galpon::factory()->forGranja($granjaA)->create();
        $galponB = Galpon::factory()->forGranja($granjaB)->create();

        foreach ([$galponA, $galponB] as $galpon) {
            Lote::factory()
                ->forGalpon($galpon)
                ->create([
                    'cantidad_inicial' => 3000,
                    'estado' => LoteEstado::EnProduccion,
                    'fecha_ingreso' => now()->subDays(15)->toDateString(),
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
