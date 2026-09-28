<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\RegistrarCargaAlimentoAction;
use App\Actions\Operacion\RegistrarCargaDescarteAction;
use App\Actions\Operacion\RegistrarCargaHuevosAction;
use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Actions\Operacion\RegistrarVacunacionAction;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OperarioCargaEstadoBajoLockCap08Test extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('capturaActionsConLoteActivoProvider')]
    public function test_rejects_stale_galpon_when_db_state_changed_under_lock(
        string $actionClass,
        callable $execute,
        RegistroOperativoTipo $tipo,
    ): void {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 200);
        $galponStale = Galpon::query()->with('granja')->findOrFail($galpon->id);

        DB::table('galpones')->where('id', $galpon->id)->update([
            'estado' => GalponEstado::EnMantenimiento->value,
        ]);

        try {
            $execute(app($actionClass), $operario, $galponStale);
            $this->fail('La carga debía rechazarse tras cambio concurrente del galpón.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('galpon_id', $exception->errors());
        }

        $this->assertSame(0, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', $tipo)
            ->count());
    }

    public static function capturaActionsConLoteActivoProvider(): array
    {
        return [
            'huevos' => [
                RegistrarCargaHuevosAction::class,
                fn ($action, $operario, $galpon) => $action->execute($operario, $galpon, 100, 0),
                RegistroOperativoTipo::Huevos,
            ],
            'muertes' => [
                RegistrarCargaMuertesAction::class,
                fn ($action, $operario, $galpon) => $action->execute($operario, $galpon, 5),
                RegistroOperativoTipo::Muertes,
            ],
            'descarte' => [
                RegistrarCargaDescarteAction::class,
                fn ($action, $operario, $galpon) => $action->execute($operario, $galpon, 3),
                RegistroOperativoTipo::Descarte,
            ],
        ];
    }

    public function test_alimento_rejects_stale_galpon_when_db_state_changed_under_lock(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $galponStale = Galpon::query()->with('granja')->findOrFail($galpon->id);

        DB::table('galpones')->where('id', $galpon->id)->update([
            'activo' => false,
        ]);

        try {
            app(RegistrarCargaAlimentoAction::class)->execute($operario, $galponStale, 500);
            $this->fail('La entrega debía rechazarse tras desactivar el galpón.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('galpon_id', $exception->errors());
        }

        $this->assertSame(0, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Alimento)
            ->count());
    }

    public function test_huevos_rejects_when_active_lote_was_closed_under_lock(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $galponStale = Galpon::query()->with('granja')->findOrFail($galpon->id);

        DB::table('lotes')->where('id', $lote->id)->update([
            'estado' => LoteEstado::Cerrado->value,
        ]);

        try {
            app(RegistrarCargaHuevosAction::class)->execute($operario, $galponStale, 80, 0);
            $this->fail('La carga debía rechazarse tras cerrar el lote.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('galpon_id', $exception->errors());
        }

        $this->assertSame(0, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Huevos)
            ->count());
    }

    public function test_muertes_rejects_when_stale_balance_exceeds_locked_stock(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 50);
        $galponStale = Galpon::query()->with('granja')->findOrFail($galpon->id);

        DB::table('galpones')->where('id', $galpon->id)->update([
            'aves_actuales' => 4,
        ]);

        try {
            app(RegistrarCargaMuertesAction::class)->execute($operario, $galponStale, 10);
            $this->fail('Las muertes debían rechazarse por saldo insuficiente bajo lock.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('muertes', $exception->errors());
        }

        $galpon->refresh();
        $this->assertSame(4, $galpon->aves_actuales);
        $this->assertSame(0, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Muertes)
            ->count());
    }

    public function test_vacunacion_rejects_when_lote_was_closed_under_lock(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $galponStale = Galpon::query()->with('granja')->findOrFail($galpon->id);
        $loteStale = Lote::query()->findOrFail($lote->id);

        DB::table('lotes')->where('id', $lote->id)->update([
            'estado' => LoteEstado::Cerrado->value,
        ]);

        try {
            app(RegistrarVacunacionAction::class)->execute(
                $operario,
                $galponStale,
                $loteStale,
                VacunaTipo::Newcastle,
            );
            $this->fail('La vacunación debía rechazarse tras cerrar el lote.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('lote_id', $exception->errors());
        }

        $this->assertSame(0, Vacunacion::query()->where('galpon_id', $galpon->id)->count());
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function createOperarioConGalpon(int $avesActuales = 5000): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->conLoteActivo()->create([
            'aves_actuales' => $avesActuales,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        return [$operario, $galpon];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Lote}
     */
    private function createOperarioConGalponYLote(): array
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $lote = Lote::query()->where('galpon_id', $galpon->id)->firstOrFail();
        $lote->forceFill(['estado' => LoteEstado::EnProduccion])->save();

        return [$operario, $galpon, $lote];
    }
}
