<?php

namespace Tests\Feature\Auditoria;

use App\Actions\Auditoria\CorregirRegistroOperativoAction;
use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Actions\Lote\TransicionarLoteEstadoAction;
use App\Actions\Operacion\AnularRegistroOperativoAction;
use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Exceptions\AuditoriaCriticaException;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class AuditoriaAtomicidadTest extends TestCase
{
    use RefreshDatabase;

    public function test_fallo_auditoria_en_anulacion_revierte_estado_y_saldo(): void
    {
        [$encargado, $operario, $galpon, $registro] = $this->registroMuertesActivo();

        $avesAntes = $galpon->aves_actuales;

        $this->mock(RegistrarAuditoriaAction::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once()->andThrow(new AuditoriaCriticaException('Simulación'));
        });

        try {
            app(AnularRegistroOperativoAction::class)->execute($encargado, $registro, 'Error de prueba');
            $this->fail('Se esperaba AuditoriaCriticaException.');
        } catch (AuditoriaCriticaException) {
            // esperado
        }

        $registro->refresh();
        $galpon->refresh();

        $this->assertSame(RegistroOperativoEstado::Activo, $registro->estado);
        $this->assertSame($avesAntes, $galpon->aves_actuales);
        $this->assertDatabaseCount('auditorias', 0);
    }

    public function test_fallo_auditoria_en_correccion_revierte_valores_y_saldo(): void
    {
        [$encargado, $operario, $galpon, $registro] = $this->registroMuertesActivo(muertes: 5);

        $avesAntes = $galpon->aves_actuales;

        $this->mock(RegistrarAuditoriaAction::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once()->andThrow(new AuditoriaCriticaException('Simulación'));
        });

        try {
            app(CorregirRegistroOperativoAction::class)->execute(
                $encargado,
                $registro,
                'Corrección de prueba',
                ['muertes' => 2],
            );
            $this->fail('Se esperaba AuditoriaCriticaException.');
        } catch (AuditoriaCriticaException) {
            // esperado
        }

        $registro->refresh();
        $galpon->refresh();

        $this->assertSame(5, $registro->muertes);
        $this->assertSame($avesAntes, $galpon->aves_actuales);
        $this->assertDatabaseCount('correcciones_registro_operativo', 0);
        $this->assertDatabaseCount('auditorias', 0);
    }

    public function test_fallo_auditoria_en_transicion_lote_revierte_estado(): void
    {
        [$encargado, $lote] = $this->encargadoConLote();

        $this->mock(RegistrarAuditoriaAction::class, function (MockInterface $mock): void {
            $mock->shouldReceive('execute')->once()->andThrow(new AuditoriaCriticaException('Simulación'));
        });

        try {
            app(TransicionarLoteEstadoAction::class)->execute($encargado, $lote, [
                'estado' => LoteEstado::Cerrado->value,
                'motivo' => 'Fin de ciclo',
            ]);
            $this->fail('Se esperaba AuditoriaCriticaException.');
        } catch (AuditoriaCriticaException) {
            // esperado
        }

        $lote->refresh();

        $this->assertSame(LoteEstado::EnProduccion, $lote->estado);
        $this->assertNull($lote->estado_historial);
        $this->assertDatabaseCount('auditorias', 0);
    }

    /**
     * @return array{0: User, 1: User, 2: Galpon, 3: RegistroOperativo}
     */
    private function registroMuertesActivo(int $muertes = 3): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 1000]);

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => $muertes,
            ]);

        $galpon->decrement('aves_actuales', $muertes);
        $galpon->refresh();

        return [$encargado, $operario, $galpon, $registro];
    }

    /**
     * @return array{0: User, 1: Lote}
     */
    private function encargadoConLote(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $lote = Lote::factory()->forGalpon($galpon)->create([
            'estado' => LoteEstado::EnProduccion,
            'estado_historial' => null,
        ]);

        return [$encargado, $lote];
    }
}
