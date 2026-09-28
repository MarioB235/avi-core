<?php

namespace Tests\Unit\Support;

use App\Enums\RegistroOperativoTipo;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Support\DiaOperativoEmpresa;
use App\Support\EmpresaConfiguracion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DiaOperativoEmpresaTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_logical_day_uses_empresa_timezone_at_midnight(): void
    {
        $empresa = Empresa::factory()->create([
            'configuracion' => [
                'zona_horaria' => 'America/Montevideo',
                'unidades' => [
                    'huevos_por_maple' => 30,
                    'maples_por_cajon' => 12,
                ],
            ],
        ]);

        Carbon::setTestNow(Carbon::parse('2026-09-28 03:30:00', 'UTC'));

        $hoy = DiaOperativoEmpresa::hoyParaEmpresa($empresa);

        $this->assertSame('2026-09-28', $hoy->fechaLogica->toDateString());
        $this->assertTrue($hoy->contieneInstante(Carbon::parse('2026-09-28 04:00:00', 'UTC')));
        $this->assertFalse($hoy->contieneInstante(Carbon::parse('2026-09-28 02:30:00', 'UTC')));
    }

    public function test_defaults_to_montevideo_when_config_missing(): void
    {
        $empresa = Empresa::factory()->create(['configuracion' => null]);

        Carbon::setTestNow(Carbon::parse('2026-06-15 12:00:00', 'UTC'));

        $dia = DiaOperativoEmpresa::hoyParaEmpresa($empresa);

        $this->assertSame(EmpresaConfiguracion::DEFAULT_ZONA_HORARIA, $dia->zonaHoraria);
    }

    public function test_ayer_para_empresa_returns_previous_logical_day(): void
    {
        $empresa = Empresa::factory()->create();
        Carbon::setTestNow(Carbon::parse('2026-09-28 12:00:00', 'UTC'));

        $ayer = DiaOperativoEmpresa::ayerParaEmpresa($empresa);

        $this->assertSame('2026-09-27', $ayer->fechaLogica->toDateString());
    }

    public function test_en_fecha_para_empresa_parses_date_in_empresa_timezone(): void
    {
        $empresa = Empresa::factory()->create([
            'configuracion' => [
                'zona_horaria' => 'America/Montevideo',
                'unidades' => [
                    'huevos_por_maple' => 30,
                    'maples_por_cajon' => 12,
                ],
            ],
        ]);

        $dia = DiaOperativoEmpresa::enFechaParaEmpresa($empresa, '2026-09-15');

        $this->assertSame('2026-09-15', $dia->fechaLogica->toDateString());
        $this->assertSame('America/Montevideo', $dia->zonaHoraria);
    }

    public function test_aplicar_al_query_filters_records_inside_logical_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 03:30:00', 'UTC'));

        $empresa = Empresa::factory()->create([
            'configuracion' => [
                'zona_horaria' => 'America/Montevideo',
                'unidades' => [
                    'huevos_por_maple' => 30,
                    'maples_por_cajon' => 12,
                ],
            ],
        ]);

        $operario = User::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()
            ->forGranja(Granja::factory()->create(['empresa_id' => $empresa->id]))
            ->create(['empresa_id' => $empresa->id]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 100,
                'created_at' => Carbon::parse('2026-09-28 02:30:00', 'UTC'),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 200,
                'created_at' => Carbon::parse('2026-09-28 04:00:00', 'UTC'),
            ]);

        $hoy = DiaOperativoEmpresa::hoyParaEmpresa($empresa);
        $total = (int) RegistroOperativo::query()
            ->tap(fn ($query) => $hoy->aplicarAlQuery($query))
            ->sum('huevos');

        $this->assertSame(200, $total);
    }

    public function test_es_dia_operativo_actual_matches_empresa_timezone_window(): void
    {
        $empresa = Empresa::factory()->create([
            'configuracion' => [
                'zona_horaria' => 'America/Montevideo',
                'unidades' => [
                    'huevos_por_maple' => 30,
                    'maples_por_cajon' => 12,
                ],
            ],
        ]);

        Carbon::setTestNow(Carbon::parse('2026-09-28 03:30:00', 'UTC'));

        $this->assertTrue(DiaOperativoEmpresa::esDiaOperativoActual(
            $empresa->id,
            Carbon::parse('2026-09-28 04:00:00', 'UTC'),
        ));

        $this->assertFalse(DiaOperativoEmpresa::esDiaOperativoActual(
            $empresa->id,
            Carbon::parse('2026-09-28 02:30:00', 'UTC'),
        ));
    }
}
