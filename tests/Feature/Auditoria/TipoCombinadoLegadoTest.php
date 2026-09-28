<?php

namespace Tests\Feature\Auditoria;

use App\Actions\Operacion\AnularRegistroOperativoAction;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Support\RegistroOperativoCorreccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TipoCombinadoLegadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_anulacion_combinado_restaura_saldo_por_muertes_y_descarte(): void
    {
        [$operario, $galpon, $registro] = $this->combinadoConImpactoAves(muertes: 3, descarteAves: 2);

        $avesAntes = $galpon->aves_actuales;

        app(AnularRegistroOperativoAction::class)->execute($operario, $registro, 'Carga legada incorrecta');

        $galpon->refresh();
        $registro->refresh();

        $this->assertSame($avesAntes + 5, $galpon->aves_actuales);
        $this->assertTrue($registro->estado->value === 'anulado');
    }

    public function test_anulacion_combinado_cero_confirmado_no_restaura_saldo(): void
    {
        [$operario, $galpon, $registro] = $this->combinadoConImpactoAves(muertes: 0, descarteAves: 0, ceroConfirmado: true);

        $avesAntes = $galpon->aves_actuales;

        app(AnularRegistroOperativoAction::class)->execute($operario, $registro, 'Anulación de cero');

        $galpon->refresh();

        $this->assertSame($avesAntes, $galpon->aves_actuales);
    }

    public function test_combinado_es_tipo_legado_sin_captura_nueva(): void
    {
        $this->assertTrue(RegistroOperativoTipo::Combinado->esLegado());
        $this->assertFalse(RegistroOperativoTipo::Combinado->admiteCapturaNueva());
        $this->assertTrue(RegistroOperativoTipo::Muertes->admiteCapturaNueva());
    }

    public function test_correccion_rechazada_en_combinado(): void
    {
        [$operario, , $registro] = $this->combinadoConImpactoAves(muertes: 1);

        $encargado = User::factory()->create([
            'empresa_id' => $operario->empresa_id,
            'rol' => UserRole::Encargado,
        ]);

        $this->expectException(ValidationException::class);

        RegistroOperativoCorreccion::assertTipoCorregible($registro);
    }

    /**
     * @return array{0: User, 1: Galpon, 2: RegistroOperativo}
     */
    private function combinadoConImpactoAves(
        int $muertes = 1,
        int $descarteAves = 0,
        bool $ceroConfirmado = false,
    ): array {
        $empresa = Empresa::factory()->create();
        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 1000]);

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Combinado,
                'huevos' => 200,
                'muertes' => $muertes,
                'descarte_aves' => $descarteAves,
                'alimento_kg' => 4.5,
                'cero_confirmado' => $ceroConfirmado,
            ]);

        $galpon->update(['aves_actuales' => 1000 - $muertes - $descarteAves]);

        return [$operario, $galpon, $registro];
    }
}
