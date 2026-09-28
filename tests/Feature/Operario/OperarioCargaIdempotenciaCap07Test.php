<?php

namespace Tests\Feature\Operario;

use App\Actions\Operacion\RegistrarCargaAlimentoAction;
use App\Actions\Operacion\RegistrarCargaDescarteAction;
use App\Actions\Operacion\RegistrarCargaHuevosAction;
use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Actions\Operacion\RegistrarVacunacionAction;
use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Enums\VacunaTipo;
use App\Livewire\Operario\CargarHub;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Models\Vacunacion;
use App\Support\IdempotenciaCaptura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OperarioCargaIdempotenciaCap07Test extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('registroOperativoActionsProvider')]
    public function test_registro_retry_with_same_key_returns_persisted_result(
        string $actionClass,
        RegistroOperativoTipo $tipo,
        callable $execute,
    ): void {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 200);
        $clave = (string) Str::uuid();
        $action = app($actionClass);

        $primero = $execute($action, $operario, $galpon, $clave);
        $segundo = $execute($action, $operario, $galpon, $clave);

        $this->assertSame($primero->id, $segundo->id);
        $this->assertSame(1, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', $tipo)
            ->count());
    }

    public static function registroOperativoActionsProvider(): array
    {
        return [
            'huevos' => [
                RegistrarCargaHuevosAction::class,
                RegistroOperativoTipo::Huevos,
                fn ($action, $operario, $galpon, $clave) => $action->execute($operario, $galpon, 120, 5, null, $clave),
            ],
            'muertes' => [
                RegistrarCargaMuertesAction::class,
                RegistroOperativoTipo::Muertes,
                fn ($action, $operario, $galpon, $clave) => $action->execute($operario, $galpon, 4, null, $clave),
            ],
            'descarte' => [
                RegistrarCargaDescarteAction::class,
                RegistroOperativoTipo::Descarte,
                fn ($action, $operario, $galpon, $clave) => $action->execute($operario, $galpon, 3, null, $clave),
            ],
            'alimento' => [
                RegistrarCargaAlimentoAction::class,
                RegistroOperativoTipo::Alimento,
                fn ($action, $operario, $galpon, $clave) => $action->execute($operario, $galpon, 750.5, null, $clave),
            ],
        ];
    }

    public function test_muertes_retry_with_same_key_does_not_double_decrement(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon(avesActuales: 80);
        $clave = (string) Str::uuid();
        $action = app(RegistrarCargaMuertesAction::class);

        $action->execute($operario, $galpon, 7, null, $clave);
        $action->execute($operario, $galpon, 7, null, $clave);

        $galpon->refresh();
        $this->assertSame(73, $galpon->aves_actuales);
    }

    public function test_vacunacion_retry_with_same_key_returns_persisted_result(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();
        $clave = (string) Str::uuid();
        $action = app(RegistrarVacunacionAction::class);

        $primero = $action->execute($operario, $galpon, $lote, VacunaTipo::Newcastle, null, $clave);
        $segundo = $action->execute($operario, $galpon, $lote, VacunaTipo::Newcastle, null, $clave);

        $this->assertSame($primero->id, $segundo->id);
        $this->assertSame(1, Vacunacion::query()->where('galpon_id', $galpon->id)->count());
    }

    public function test_same_key_in_different_empresas_creates_independent_records(): void
    {
        [$operarioA, $galponA] = $this->createOperarioConGalpon();
        [$operarioB, $galponB] = $this->createOperarioConGalpon();
        $clave = (string) Str::uuid();
        $action = app(RegistrarCargaHuevosAction::class);

        $registroA = $action->execute($operarioA, $galponA, 50, 0, null, $clave);
        $registroB = $action->execute($operarioB, $galponB, 50, 0, null, $clave);

        $this->assertNotSame($registroA->id, $registroB->id);
        $this->assertSame(1, RegistroOperativo::query()->where('empresa_id', $operarioA->empresa_id)->count());
        $this->assertSame(1, RegistroOperativo::query()->where('empresa_id', $operarioB->empresa_id)->count());
    }

    public function test_new_dialog_open_generates_fresh_key_for_same_quantity(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '60')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos');

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '60')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos');

        $this->assertSame(2, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Huevos)
            ->count());
    }

    public function test_livewire_double_submit_with_same_form_key_does_not_duplicate(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $component = Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioAlimento')
            ->set('alimentoKg', '400');

        $clave = $component->get('alimentoIdempotenciaClave');

        $component->call('guardarAlimento');
        $component->set('alimentoKg', '400')->call('guardarAlimento');

        $this->assertSame(1, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('idempotencia_clave', $clave)
            ->count());
    }

    public function test_idempotencia_captura_generates_unique_keys(): void
    {
        $primera = IdempotenciaCaptura::generarClave();
        $segunda = IdempotenciaCaptura::generarClave();

        $this->assertNotSame($primera, $segunda);
        $this->assertSame(36, strlen($primera));
    }

    public function test_blank_idempotency_key_is_treated_as_absent(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $action = app(RegistrarCargaHuevosAction::class);

        $action->execute($operario, $galpon, 10, 0, null, '   ');
        $action->execute($operario, $galpon, 10, 0, null, null);

        $this->assertSame(2, RegistroOperativo::query()
            ->where('galpon_id', $galpon->id)
            ->where('tipo', RegistroOperativoTipo::Huevos)
            ->count());
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
