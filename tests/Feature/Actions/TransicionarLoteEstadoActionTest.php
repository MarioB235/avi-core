<?php

namespace Tests\Feature\Actions;

use App\Actions\Lote\TransicionarLoteEstadoAction;
use App\Actions\Lote\UpdateLoteAction;
use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransicionarLoteEstadoActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_lote_action_rejects_estado_field(): void
    {
        [$encargado, $lote] = $this->loteConEncargado(LoteEstado::EnProduccion);

        $this->expectException(ValidationException::class);

        app(UpdateLoteAction::class)->execute($encargado, $lote, [
            'estado' => LoteEstado::Cerrado->value,
        ]);
    }

    public function test_encargado_can_close_lote_with_motivo(): void
    {
        [$encargado, $lote] = $this->loteConEncargado(LoteEstado::EnProduccion);

        $actualizado = app(TransicionarLoteEstadoAction::class)->execute($encargado, $lote, [
            'estado' => LoteEstado::Cerrado->value,
            'motivo' => 'Fin de ciclo productivo',
        ]);

        $this->assertSame(LoteEstado::Cerrado, $actualizado->estado);
        $this->assertCount(1, $actualizado->estado_historial);
        $this->assertSame('Fin de ciclo productivo', $actualizado->estado_historial[0]['motivo']);
    }

    public function test_encargado_cannot_reopen_closed_lote(): void
    {
        [$encargado, $lote] = $this->loteConEncargado(LoteEstado::Cerrado);

        $this->expectException(AuthorizationException::class);

        app(TransicionarLoteEstadoAction::class)->execute($encargado, $lote, [
            'estado' => LoteEstado::Activo->value,
            'motivo' => 'Reapertura solicitada',
        ]);
    }

    public function test_administrativo_can_reopen_closed_lote(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();
        $lote = Lote::factory()->forGalpon($galpon)->create(['estado' => LoteEstado::Cerrado]);

        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'must_change_password' => false,
        ]);

        $actualizado = app(TransicionarLoteEstadoAction::class)->execute($administrativo, $lote, [
            'estado' => LoteEstado::Activo->value,
            'motivo' => 'Reapertura autorizada por administración',
        ]);

        $this->assertSame(LoteEstado::Activo, $actualizado->estado);
    }

    public function test_transition_requires_motivo(): void
    {
        [$encargado, $lote] = $this->loteConEncargado(LoteEstado::Activo);

        $this->expectException(ValidationException::class);

        app(TransicionarLoteEstadoAction::class)->execute($encargado, $lote, [
            'estado' => LoteEstado::Cerrado->value,
            'motivo' => 'abc',
        ]);
    }

    public function test_trasladado_lote_cannot_transition(): void
    {
        [$encargado, $lote] = $this->loteConEncargado(LoteEstado::Trasladado);

        $this->expectException(AuthorizationException::class);

        app(TransicionarLoteEstadoAction::class)->execute($encargado, $lote, [
            'estado' => LoteEstado::Cerrado->value,
            'motivo' => 'Intento inválido',
        ]);
    }

    /**
     * @return array{0: User, 1: Lote}
     */
    private function loteConEncargado(LoteEstado $estado): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();
        $lote = Lote::factory()->forGalpon($galpon)->create(['estado' => $estado]);

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        return [$encargado, $lote];
    }
}
