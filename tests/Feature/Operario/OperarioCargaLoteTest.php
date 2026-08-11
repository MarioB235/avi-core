<?php

namespace Tests\Feature\Operario;

use App\Actions\Lote\RegistrarLoteAction;
use App\Enums\EmpresaEstado;
use App\Enums\GalponEstado;
use App\Enums\LoteEstado;
use App\Enums\TipoHuevo;
use App\Enums\UserRole;
use App\Livewire\Operario\CargaLote;
use App\Livewire\Operario\CargarHub;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioCargaLoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_cannot_see_nuevo_lote_tile(): void
    {
        [$operario, $galpon] = $this->createUsuarioConGalpon(UserRole::Operario);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->assertDontSee('Nuevo lote');
    }

    public function test_encargado_can_register_single_lote(): void
    {
        [$encargado, $galpon] = $this->createUsuarioConGalpon(UserRole::Encargado);

        Livewire::actingAs($encargado)
            ->test(CargarHub::class)
            ->assertSee('Nuevo lote')
            ->call('abrirFormularioLote')
            ->assertSet('dialogLoteAbierto', true)
            ->set('loteGalponId', (string) $galpon->id)
            ->set('tipoBlanco', true)
            ->set('cantidadBlanco', '4200')
            ->set('codigoSma', 'L-2024-089')
            ->set('fechaNacimiento', '2026-03-01')
            ->call('guardarLote')
            ->assertSet('dialogLoteAbierto', false)
            ->assertDispatched('snackbar-show');

        $fechaIngreso = Carbon::today()->format('Ymd');
        $codigoEsperado = "{$galpon->codigo}-{$fechaIngreso}-B-1";

        $this->assertDatabaseHas('lotes', [
            'empresa_id' => $encargado->empresa_id,
            'galpon_id' => $galpon->id,
            'codigo' => $codigoEsperado,
            'codigo_sma' => 'L-2024-089',
            'cantidad_inicial' => 4200,
            'tipo_huevo' => TipoHuevo::Blanco->value,
            'estado' => LoteEstado::Activo->value,
        ]);

        $galpon->refresh();
        $this->assertSame(4200, $galpon->aves_actuales);
    }

    public function test_encargado_creates_one_lote_per_selected_tipo(): void
    {
        [$encargado, $galpon] = $this->createUsuarioConGalpon(UserRole::Encargado);

        Livewire::actingAs($encargado)
            ->test(CargarHub::class)
            ->call('abrirFormularioLote')
            ->set('loteGalponId', (string) $galpon->id)
            ->set('tipoBlanco', true)
            ->set('tipoColor', true)
            ->set('cantidadBlanco', '3000')
            ->set('cantidadColor', '2500')
            ->set('fechaNacimiento', '2026-02-15')
            ->call('guardarLote')
            ->assertSet('dialogLoteAbierto', false)
            ->assertDispatched('snackbar-show');

        $fechaIngreso = Carbon::today()->format('Ymd');
        $codigoBlanco = "{$galpon->codigo}-{$fechaIngreso}-B-1";
        $codigoColor = "{$galpon->codigo}-{$fechaIngreso}-C-1";

        $this->assertDatabaseHas('lotes', [
            'codigo' => $codigoBlanco,
            'cantidad_inicial' => 3000,
            'tipo_huevo' => TipoHuevo::Blanco->value,
        ]);

        $this->assertDatabaseHas('lotes', [
            'codigo' => $codigoColor,
            'cantidad_inicial' => 2500,
            'tipo_huevo' => TipoHuevo::Color->value,
        ]);

        $galpon->refresh();
        $this->assertSame(5500, $galpon->aves_actuales);
        $this->assertSame(2, Lote::query()->count());
    }

    public function test_carga_lote_requires_tipo_and_cantidad(): void
    {
        [$encargado, $galpon] = $this->createUsuarioConGalpon(UserRole::Encargado);

        Livewire::actingAs($encargado)
            ->test(CargarHub::class)
            ->call('abrirFormularioLote')
            ->set('loteGalponId', (string) $galpon->id)
            ->set('fechaNacimiento', '2026-03-01')
            ->call('guardarLote')
            ->assertHasErrors(['tiposHuevo']);

        Livewire::actingAs($encargado)
            ->test(CargarHub::class)
            ->call('abrirFormularioLote')
            ->set('loteGalponId', (string) $galpon->id)
            ->set('tipoBlanco', true)
            ->set('fechaNacimiento', '2026-03-01')
            ->call('guardarLote')
            ->assertHasErrors(['cantidadBlanco']);

        $this->assertSame(0, Lote::query()->count());
    }

    public function test_carga_lote_route_redirects_to_hub_with_form_param(): void
    {
        [$encargado, $galpon] = $this->createUsuarioConGalpon(UserRole::Encargado);
        $encargado->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($encargado)
            ->test(CargaLote::class)
            ->assertRedirect(route('operario.cargar', ['form' => 'lote']));
    }

    public function test_cargar_hub_opens_lote_dialog_via_query_param(): void
    {
        [$encargado, $galpon] = $this->createUsuarioConGalpon(UserRole::Encargado);
        $encargado->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($encargado)
            ->withQueryParams(['form' => 'lote'])
            ->test(CargarHub::class)
            ->assertSet('dialogLoteAbierto', true);
    }

    public function test_operario_query_form_lote_does_not_open_dialog(): void
    {
        [$operario, $galpon] = $this->createUsuarioConGalpon(UserRole::Operario);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->withQueryParams(['form' => 'lote'])
            ->test(CargarHub::class)
            ->assertSet('dialogLoteAbierto', false);
    }

    public function test_registrar_lote_action_rejects_operario(): void
    {
        [$operario, $galpon] = $this->createUsuarioConGalpon(UserRole::Operario);

        $this->expectException(AuthorizationException::class);

        app(RegistrarLoteAction::class)->execute(
            $operario,
            $galpon,
            [TipoHuevo::Blanco->value => 1000],
            Carbon::parse('2026-01-01'),
        );
    }

    public function test_registrar_lote_action_rejects_galpon_from_other_empresa(): void
    {
        [$encargado, $galpon] = $this->createUsuarioConGalpon(UserRole::Encargado);

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();

        $this->expectException(AuthorizationException::class);

        app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galponAjeno,
            [TipoHuevo::Blanco->value => 1000],
            Carbon::parse('2026-01-01'),
        );
    }

    public function test_registrar_lote_action_rejects_unavailable_galpon(): void
    {
        [$encargado, $galpon] = $this->createUsuarioConGalpon(UserRole::Encargado);
        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);
        $galpon->refresh();

        $this->expectException(ValidationException::class);

        app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 1000],
            Carbon::parse('2026-01-01'),
        );
    }

    public function test_codigo_sequence_increments_per_galpon_day_and_tipo(): void
    {
        [$encargado, $galpon] = $this->createUsuarioConGalpon(UserRole::Encargado);
        $fechaIngreso = Carbon::today();

        Lote::factory()->forGalpon($galpon)->create([
            'codigo' => "{$galpon->codigo}-{$fechaIngreso->format('Ymd')}-B-1",
            'fecha_ingreso' => $fechaIngreso,
        ]);

        $lotes = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 500],
            Carbon::parse('2026-01-01'),
            $fechaIngreso,
        );

        $this->assertSame("{$galpon->codigo}-{$fechaIngreso->format('Ymd')}-B-2", $lotes->first()->codigo);
    }

    public function test_dueno_can_access_operario_cargar(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get(route('operario.cargar'))
            ->assertOk()
            ->assertSee('Nuevo lote');
    }

    public function test_administrativo_can_access_operario_cargar(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'must_change_password' => false,
        ]);

        $this->actingAs($administrativo)
            ->get(route('operario.cargar'))
            ->assertOk()
            ->assertSee('Nuevo lote');
    }

    public function test_administrativo_can_register_lote(): void
    {
        [$administrativo, $galpon] = $this->createUsuarioConGalpon(UserRole::Administrativo);

        Livewire::actingAs($administrativo)
            ->test(CargarHub::class)
            ->assertSee('Nuevo lote')
            ->call('abrirFormularioLote')
            ->set('loteGalponId', (string) $galpon->id)
            ->set('tipoBlanco', true)
            ->set('cantidadBlanco', '1800')
            ->set('fechaNacimiento', '2026-02-01')
            ->call('guardarLote')
            ->assertSet('dialogLoteAbierto', false)
            ->assertDispatched('snackbar-show');

        $fechaIngreso = Carbon::today()->format('Ymd');
        $codigoEsperado = "{$galpon->codigo}-{$fechaIngreso}-B-1";

        $this->assertDatabaseHas('lotes', [
            'empresa_id' => $administrativo->empresa_id,
            'galpon_id' => $galpon->id,
            'codigo' => $codigoEsperado,
            'cantidad_inicial' => 1800,
        ]);
    }

    public function test_carga_lote_rejects_future_birth_date(): void
    {
        [$encargado, $galpon] = $this->createUsuarioConGalpon(UserRole::Encargado);
        $fechaFutura = Carbon::tomorrow()->toDateString();

        Livewire::actingAs($encargado)
            ->test(CargarHub::class)
            ->call('abrirFormularioLote')
            ->set('loteGalponId', (string) $galpon->id)
            ->set('tipoBlanco', true)
            ->set('cantidadBlanco', '1000')
            ->set('fechaNacimiento', $fechaFutura)
            ->call('guardarLote')
            ->assertHasErrors(['fechaNacimiento']);

        $this->assertSame(0, Lote::query()->count());
    }

    public function test_carga_lote_rejects_unavailable_galpon_via_livewire(): void
    {
        [$encargado, $galpon] = $this->createUsuarioConGalpon(UserRole::Encargado);
        $galpon->update(['estado' => GalponEstado::EnMantenimiento]);

        Livewire::actingAs($encargado)
            ->test(CargarHub::class)
            ->call('abrirFormularioLote')
            ->set('loteGalponId', (string) $galpon->id)
            ->set('tipoBlanco', true)
            ->set('cantidadBlanco', '1000')
            ->set('fechaNacimiento', '2026-03-01')
            ->call('guardarLote')
            ->assertHasErrors(['loteGalponId']);

        $this->assertSame(0, Lote::query()->count());
    }

    public function test_carga_lote_rejects_foreign_galpon_via_livewire(): void
    {
        [$encargado, $galpon] = $this->createUsuarioConGalpon(UserRole::Encargado);

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();

        Livewire::actingAs($encargado)
            ->test(CargarHub::class)
            ->call('abrirFormularioLote')
            ->set('loteGalponId', (string) $galponAjeno->id)
            ->set('tipoBlanco', true)
            ->set('cantidadBlanco', '1000')
            ->set('fechaNacimiento', '2026-03-01')
            ->call('guardarLote')
            ->assertHasErrors(['loteGalponId']);

        $this->assertSame(0, Lote::query()->count());
        $this->assertDatabaseMissing('lotes', [
            'galpon_id' => $galponAjeno->id,
        ]);
        $this->assertDatabaseMissing('lotes', [
            'galpon_id' => $galpon->id,
        ]);
    }

    public function test_operario_guardar_lote_does_not_persist(): void
    {
        [$operario, $galpon] = $this->createUsuarioConGalpon(UserRole::Operario);
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->set('loteGalponId', (string) $galpon->id)
            ->set('tipoBlanco', true)
            ->set('cantidadBlanco', '1000')
            ->set('fechaNacimiento', '2026-03-01')
            ->call('guardarLote')
            ->assertNotDispatched('snackbar-show');

        $this->assertSame(0, Lote::query()->count());
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function createUsuarioConGalpon(UserRole $rol): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create([
            'nombre' => 'Galpón Norte',
            'codigo' => 'GN',
            'aves_actuales' => 0,
        ]);

        $usuario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => $rol,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        return [$usuario, $galpon];
    }
}
