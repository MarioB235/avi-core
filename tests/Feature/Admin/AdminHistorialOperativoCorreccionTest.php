<?php

namespace Tests\Feature\Admin;

use App\Actions\Auditoria\CorregirRegistroOperativoAction;
use App\Enums\EmpresaEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Admin\HistorialOperativo\Index as HistorialOperativoIndex;
use App\Models\CorreccionRegistroOperativo;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\OperarioGalponResumenService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class AdminHistorialOperativoCorreccionTest extends TestCase
{
    use RefreshDatabase;

    public function test_encargado_corrige_muertes_y_ajusta_saldo_una_vez(): void
    {
        [$encargado, $operario, $galpon] = $this->supervisorConOperario([
            'aves_actuales' => 1000,
        ]);

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => 5,
            ]);

        $galpon->decrement('aves_actuales', 5);
        $galpon->refresh();
        $this->assertSame(995, $galpon->aves_actuales);

        Livewire::actingAs($encargado)
            ->test(HistorialOperativoIndex::class)
            ->call('abrirDetalle', 'registro-'.$registro->id)
            ->call('mostrarCorreccion')
            ->set('corregirMuertes', '2')
            ->set('motivoCorreccion', 'Conteo erróneo en campo')
            ->call('guardarCorreccion')
            ->assertHasNoErrors()
            ->assertSet('mostrarFormularioCorreccion', false);

        $registro->refresh();
        $galpon->refresh();

        $this->assertSame(2, $registro->muertes);
        $this->assertSame(998, $galpon->aves_actuales);

        $correccion = CorreccionRegistroOperativo::query()->sole();
        $this->assertSame($registro->id, $correccion->registro_operativo_id);
        $this->assertSame($encargado->id, $correccion->corregido_por);
        $this->assertSame('Conteo erróneo en campo', $correccion->motivo);
        $this->assertSame(['muertes' => 5, 'cero_confirmado' => false], $correccion->valores_anteriores);
        $this->assertSame(['muertes' => 2, 'cero_confirmado' => false], $correccion->valores_nuevos);
        $this->assertNotNull($correccion->fecha_efectiva);

        $resumen = app(OperarioGalponResumenService::class)->resumen($galpon);
        $this->assertSame(2, $resumen['muertes_hoy']);
    }

    public function test_segunda_correccion_aplica_delta_sin_duplicar_saldo(): void
    {
        [$encargado, $operario, $galpon] = $this->supervisorConOperario([
            'aves_actuales' => 1000,
        ]);

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => 5,
            ]);

        $galpon->decrement('aves_actuales', 5);

        $action = app(CorregirRegistroOperativoAction::class);
        $action->execute($encargado, $registro, 'Primera corrección', ['muertes' => 2]);
        $action->execute($encargado, $registro->fresh(), 'Segunda corrección', ['muertes' => 4]);

        $galpon->refresh();
        $registro->refresh();

        $this->assertSame(4, $registro->muertes);
        $this->assertSame(996, $galpon->aves_actuales);
        $this->assertCount(2, CorreccionRegistroOperativo::query()->get());
    }

    public function test_operario_no_puede_corregir_registros(): void
    {
        [$encargado, $operario, $galpon] = $this->supervisorConOperario();

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 500,
            ]);

        $this->assertFalse(Gate::forUser($operario)->allows('corregir', $registro));
        $this->assertTrue(Gate::forUser($encargado)->allows('corregir', $registro));

        $this->expectException(AuthorizationException::class);

        app(CorregirRegistroOperativoAction::class)->execute(
            $operario,
            $registro,
            'Intento operario',
            ['huevos' => 400, 'huevos_descarte' => 0],
        );
    }

    public function test_no_corrige_registro_anulado(): void
    {
        [$encargado, $operario, $galpon] = $this->supervisorConOperario();

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 100,
                'estado' => RegistroOperativoEstado::Anulado,
                'anulado_at' => now(),
                'anulado_por' => $operario->id,
                'motivo_anulacion' => 'Error de prueba',
            ]);

        $this->assertFalse(Gate::forUser($encargado)->allows('corregir', $registro));

        Livewire::actingAs($encargado)
            ->test(HistorialOperativoIndex::class)
            ->call('abrirDetalle', 'registro-'.$registro->id)
            ->assertDontSee('Corregir registro', false);
    }

    public function test_correccion_rechaza_valores_iguales(): void
    {
        [$encargado, $operario, $galpon] = $this->supervisorConOperario();

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Alimento,
                'huevos' => null,
                'alimento_kg' => 12.5,
            ]);

        Livewire::actingAs($encargado)
            ->test(HistorialOperativoIndex::class)
            ->call('abrirDetalle', 'registro-'.$registro->id)
            ->call('mostrarCorreccion')
            ->set('corregirAlimentoKg', '12.50')
            ->set('motivoCorreccion', 'Sin cambio real')
            ->call('guardarCorreccion')
            ->assertHasErrors(['correccion']);
    }

    /**
     * @param  array<string, mixed>  $galponOverrides
     * @return array{0: User, 1: User, 2: Galpon}
     */
    private function supervisorConOperario(array $galponOverrides = []): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create($galponOverrides);

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'name' => 'Pedro Operario',
        ]);

        return [$encargado, $operario, $galpon];
    }
}
