<?php

namespace Tests\Unit\Services;

use App\Enums\LoteEstado;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Services\EstructuraFichaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstructuraFichaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_galpon_ficha_warns_when_multiple_active_lotes(): void
    {
        $empresa = Empresa::factory()->create();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 5000]);

        Lote::factory()->forGalpon($galpon)->create([
            'estado' => LoteEstado::EnProduccion,
            'codigo' => 'L-1',
        ]);
        Lote::factory()->forGalpon($galpon)->create([
            'estado' => LoteEstado::Activo,
            'codigo' => 'L-2',
        ]);

        $ficha = app(EstructuraFichaService::class)->galpon($galpon);

        $this->assertSame(2, $ficha['lotes_activos']);
        $this->assertStringContainsString('varios lotes activos', $ficha['saldo_nota']);
    }

    public function test_lote_ficha_shows_metrics_when_single_active_lote(): void
    {
        $empresa = Empresa::factory()->create();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->conLoteActivo([
            'codigo' => 'LOTE-UNICO',
            'estado' => LoteEstado::EnProduccion,
        ])->create();

        $lote = $galpon->lotes()->first();
        $this->assertNotNull($lote);

        $ficha = app(EstructuraFichaService::class)->lote($lote);

        $this->assertTrue($ficha['metricas_atribuibles']);
        $this->assertNotNull($ficha['metricas']);
        $this->assertNull($ficha['metricas_aviso']);
    }

    public function test_lote_ficha_shows_aviso_when_multiple_active_lotes(): void
    {
        $empresa = Empresa::factory()->create();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 3000]);

        $lote = Lote::factory()->forGalpon($galpon)->create([
            'codigo' => 'LOTE-A',
            'estado' => LoteEstado::EnProduccion,
        ]);
        Lote::factory()->forGalpon($galpon)->create([
            'codigo' => 'LOTE-B',
            'estado' => LoteEstado::Activo,
        ]);

        $ficha = app(EstructuraFichaService::class)->lote($lote);

        $this->assertFalse($ficha['metricas_atribuibles']);
        $this->assertNull($ficha['metricas']);
        $this->assertStringContainsString('varios lotes activos', (string) $ficha['metricas_aviso']);
    }

    public function test_lote_ficha_no_cuenta_lotes_activos_de_otra_empresa(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $granjaA = Granja::factory()->create(['empresa_id' => $empresaA->id]);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresaB->id]);
        $galponA = Galpon::factory()->forGranja($granjaA)->create(['aves_actuales' => 1000]);
        $galponB = Galpon::factory()->forGranja($granjaB)->create(['aves_actuales' => 2000]);

        $loteA = Lote::factory()->forGalpon($galponA)->create([
            'codigo' => 'LOTE-A',
            'estado' => LoteEstado::EnProduccion,
        ]);
        Lote::factory()->forGalpon($galponB)->create([
            'codigo' => 'LOTE-B',
            'estado' => LoteEstado::Activo,
        ]);

        $ficha = app(EstructuraFichaService::class)->lote($loteA);

        $this->assertTrue($ficha['metricas_atribuibles']);
        $this->assertNotNull($ficha['metricas']);
        $this->assertNull($ficha['metricas_aviso']);
    }
}
