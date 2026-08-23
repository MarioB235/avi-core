<?php

namespace Tests\Feature\Admin;

use App\Enums\EmpresaEstado;
use App\Enums\GalponEstado;
use App\Enums\LoteEstado;
use App\Enums\TipoHuevo;
use App\Enums\UserRole;
use App\Livewire\Admin\Estructura\Index as EstructuraIndex;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminEstructuraTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrativo_can_create_granja_galpon_and_lote(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();

        $this->actingAs($administrativo)
            ->get(route('administrativo.estructura.index'))
            ->assertOk()
            ->assertSee('Estructura')
            ->assertSee('Nueva granja');

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->call('abrirCrearGranja')
            ->set('granjaNombre', 'Granja Sur')
            ->set('granjaDicose', '0201234567')
            ->set('granjaUbicacion', 'Canelones')
            ->call('guardarGranja')
            ->assertHasNoErrors()
            ->assertDispatched('snackbar-show');

        $granja = Granja::query()->where('nombre', 'Granja Sur')->first();
        $this->assertNotNull($granja);
        $this->assertSame('0201234567', $granja->dicose);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->call('abrirCrearGalpon')
            ->set('galponGranjaId', (string) $granja->id)
            ->set('galponNombre', 'Galpón 1')
            ->set('galponCodigo', 'G1')
            ->call('guardarGalpon')
            ->assertHasNoErrors();

        $galpon = Galpon::query()->where('nombre', 'Galpón 1')->first();
        $this->assertNotNull($galpon);
        $this->assertSame(0, $galpon->aves_actuales);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'lotes')
            ->call('abrirCrearLote')
            ->set('loteGalponId', (string) $galpon->id)
            ->set('loteTipoHuevo', TipoHuevo::Blanco->value)
            ->set('loteCantidad', '1200')
            ->set('loteFechaNacimiento', now()->subWeeks(18)->format('Y-m-d'))
            ->call('guardarLoteCrear')
            ->assertHasNoErrors();

        $lote = Lote::query()->where('galpon_id', $galpon->id)->first();
        $this->assertNotNull($lote);
        $galpon->refresh();
        $this->assertSame(1200, $galpon->aves_actuales);
    }

    public function test_dueno_cannot_access_estructura_panel(): void
    {
        [$empresa, $dueno] = $this->empresaConDueno();

        $this->actingAs($dueno)
            ->get(route('dueno.estructura.index'))
            ->assertForbidden();
    }

    public function test_encargado_can_view_and_create_lote_but_not_granja(): void
    {
        [$empresa] = $this->empresaConDueno();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $this->actingAs($encargado)
            ->get(route('encargado.estructura.index'))
            ->assertOk()
            ->assertDontSee('wire:click="abrirCrearGranja"', false);

        Livewire::actingAs($encargado)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'lotes')
            ->call('abrirCrearLote')
            ->set('loteGalponId', (string) $galpon->id)
            ->set('loteTipoHuevo', TipoHuevo::Color->value)
            ->set('loteCantidad', '800')
            ->set('loteFechaNacimiento', now()->subWeeks(20)->format('Y-m-d'))
            ->call('guardarLoteCrear')
            ->assertHasNoErrors();

        $this->assertSame(1, Lote::query()->where('galpon_id', $galpon->id)->count());
    }

    public function test_operario_cannot_access_estructura(): void
    {
        [$empresa] = $this->empresaConDueno();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('administrativo.estructura.index'))
            ->assertRedirect(route('operario.home'));
    }

    public function test_administrativo_cannot_see_other_company_granjas(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        Granja::factory()->create([
            'empresa_id' => $otraEmpresa->id,
            'nombre' => 'Granja Ajena',
        ]);

        Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Granja Propia',
        ]);

        $this->actingAs($administrativo)
            ->get(route('administrativo.estructura.index'))
            ->assertOk()
            ->assertSee('Granja Propia')
            ->assertDontSee('Granja Ajena');
    }

    public function test_dicose_must_be_unique_per_company(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();

        Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'dicose' => '0201111111',
        ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->call('abrirCrearGranja')
            ->set('granjaNombre', 'Otra granja')
            ->set('granjaDicose', '0201111111')
            ->call('guardarGranja')
            ->assertHasErrors(['dicose']);
    }

    public function test_administrativo_can_update_galpon_estado(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create([
            'estado' => GalponEstado::Activo,
        ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->call('abrirEditarGalpon', $galpon->id)
            ->set('galponEstado', GalponEstado::VacioSanitario->value)
            ->call('guardarGalpon')
            ->assertHasNoErrors();

        $this->assertSame(GalponEstado::VacioSanitario, $galpon->fresh()->estado);
    }

    public function test_administrativo_can_update_granja(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Granja Vieja',
            'ubicacion' => 'Antes',
        ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->call('abrirEditarGranja', $granja->id)
            ->set('granjaNombre', 'Granja Nueva')
            ->set('granjaUbicacion', 'Después')
            ->call('guardarGranja')
            ->assertHasNoErrors();

        $granja->refresh();
        $this->assertSame('Granja Nueva', $granja->nombre);
        $this->assertSame('Después', $granja->ubicacion);
    }

    public function test_administrativo_can_update_lote(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();
        $lote = Lote::factory()
            ->forGalpon($galpon)
            ->create([
                'estado' => LoteEstado::EnProduccion,
                'observacion' => null,
            ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'lotes')
            ->call('abrirEditarLote', $lote->id)
            ->set('loteEstado', LoteEstado::Cerrado->value)
            ->set('loteObservacion', 'Cierre por rotación')
            ->call('guardarLoteEditar')
            ->assertHasNoErrors();

        $lote->refresh();
        $this->assertSame(LoteEstado::Cerrado, $lote->estado);
        $this->assertSame('Cierre por rotación', $lote->observacion);
    }

    public function test_administrativo_cannot_create_galpon_in_other_company_granja(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaAjena = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->call('abrirCrearGalpon')
            ->set('galponGranjaId', (string) $granjaAjena->id)
            ->set('galponNombre', 'Galpón ilegal')
            ->call('guardarGalpon')
            ->assertHasErrors(['granja_id']);

        $this->assertNull(Galpon::query()->where('nombre', 'Galpón ilegal')->first());
    }

    /**
     * @return array{0: Empresa, 1: User}
     */
    private function empresaConDueno(): array
    {
        $empresa = Empresa::factory()->create([
            'nombre' => 'Avícola Demo',
            'estado' => EmpresaEstado::Activa,
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        return [$empresa, $dueno];
    }

    /**
     * @return array{0: Empresa, 1: User}
     */
    private function empresaConAdministrativo(): array
    {
        $empresa = Empresa::factory()->create([
            'nombre' => 'Avícola Demo',
            'estado' => EmpresaEstado::Activa,
        ]);

        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'must_change_password' => false,
        ]);

        return [$empresa, $administrativo];
    }
}
