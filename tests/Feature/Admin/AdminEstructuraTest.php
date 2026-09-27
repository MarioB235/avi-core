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
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 0]);

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
            ->assertHasErrors(['granjaDicose']);
    }

    public function test_codigo_must_be_unique_per_company(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();

        Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'codigo' => 'GR-NORTE',
        ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->call('abrirCrearGranja')
            ->set('granjaNombre', 'Granja duplicada')
            ->set('granjaCodigo', 'GR-NORTE')
            ->call('guardarGranja')
            ->assertHasErrors(['granjaCodigo']);
    }

    public function test_dicose_rejects_invalid_format_in_form(): void
    {
        [, $administrativo] = $this->empresaConAdministrativo();

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->call('abrirCrearGranja')
            ->set('granjaNombre', 'Granja inválida')
            ->set('granjaDicose', 'DICOSE-LETRAS')
            ->call('guardarGranja')
            ->assertHasErrors(['granjaDicose']);

        $this->assertDatabaseMissing('granjas', ['nombre' => 'Granja inválida']);
    }

    public function test_administrativo_can_deactivate_granja(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'activa' => true,
        ]);
        $galpon = Galpon::factory()->forGranja($granja)->create([
            'estado' => GalponEstado::Activo,
            'activo' => true,
        ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->call('abrirEditarGranja', $granja->id)
            ->set('granjaActiva', false)
            ->call('guardarGranja')
            ->assertHasNoErrors();

        $this->assertFalse($granja->fresh()->activa);
        $this->assertFalse($galpon->fresh()->activo);
    }

    public function test_administrativo_can_update_galpon_estado(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create([
            'estado' => GalponEstado::Activo,
            'aves_actuales' => 0,
        ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->call('abrirEditarGalpon', $galpon->id)
            ->set('galponEstado', GalponEstado::VacioSanitario->value)
            ->call('guardarGalpon')
            ->assertHasNoErrors();

        $galpon->refresh();
        $this->assertSame(GalponEstado::VacioSanitario, $galpon->estado);
        $this->assertFalse($galpon->activo);
    }

    public function test_administrativo_cannot_set_vacio_sanitario_with_active_lote(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->conLoteActivo()->create([
            'estado' => GalponEstado::Activo,
        ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->call('abrirEditarGalpon', $galpon->id)
            ->set('galponEstado', GalponEstado::VacioSanitario->value)
            ->call('guardarGalpon')
            ->assertHasErrors(['galponEstado']);
    }

    public function test_administrativo_cannot_reassign_galpon_with_operational_history(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granjaA = Granja::factory()->create(['empresa_id' => $empresa->id, 'nombre' => 'Granja Norte']);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresa->id, 'nombre' => 'Granja Sur']);
        $galpon = Galpon::factory()->forGranja($granjaA)->conLoteActivo()->create();

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->call('abrirEditarGalpon', $galpon->id)
            ->assertSee('Granja Norte')
            ->assertSee('No se puede cambiar')
            ->set('galponGranjaId', (string) $granjaB->id)
            ->call('guardarGalpon')
            ->assertHasErrors(['galponGranjaId']);

        $this->assertSame($granjaA->id, $galpon->fresh()->granja_id);
    }

    public function test_administrativo_can_reassign_galpon_without_history(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granjaA = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granjaA)->create(['aves_actuales' => 0]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->call('abrirEditarGalpon', $galpon->id)
            ->set('galponGranjaId', (string) $granjaB->id)
            ->call('guardarGalpon')
            ->assertHasNoErrors();

        $this->assertSame($granjaB->id, $galpon->fresh()->granja_id);
    }

    public function test_codigo_must_be_unique_per_granja(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        Galpon::factory()->forGranja($granja)->create(['codigo' => 'G-NORTE']);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->call('abrirCrearGalpon')
            ->set('galponGranjaId', (string) $granja->id)
            ->set('galponNombre', 'Otro galpón')
            ->set('galponCodigo', 'G-NORTE')
            ->call('guardarGalpon')
            ->assertHasErrors(['galponCodigo']);

        $this->assertSame(1, Galpon::query()->where('granja_id', $granja->id)->count());
    }

    public function test_codigo_can_repeat_across_different_granjas(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granjaA = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresa->id]);
        Galpon::factory()->forGranja($granjaA)->create(['codigo' => 'G1']);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->call('abrirCrearGalpon')
            ->set('galponGranjaId', (string) $granjaB->id)
            ->set('galponNombre', 'Galpón B')
            ->set('galponCodigo', 'G1')
            ->call('guardarGalpon')
            ->assertHasNoErrors();

        $this->assertNotNull(Galpon::query()->where('granja_id', $granjaB->id)->where('codigo', 'G1')->first());
    }

    public function test_cannot_create_galpon_on_inactive_granja(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'activa' => false,
        ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->call('abrirCrearGalpon')
            ->set('galponGranjaId', (string) $granja->id)
            ->set('galponNombre', 'Galpón nuevo')
            ->call('guardarGalpon')
            ->assertHasErrors(['galponGranjaId']);

        $this->assertDatabaseMissing('galpones', ['nombre' => 'Galpón nuevo']);
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

    public function test_operario_cannot_create_lote_in_estructura(): void
    {
        [$empresa] = $this->empresaConDueno();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        Livewire::actingAs($operario)
            ->test(EstructuraIndex::class)
            ->assertForbidden();
    }

    public function test_administrativo_cannot_create_lote_on_unavailable_galpon(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id, 'activa' => false]);
        $galpon = Galpon::factory()->forGranja($granja)->create([
            'estado' => GalponEstado::Activo,
            'activo' => true,
        ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'lotes')
            ->call('abrirCrearLote')
            ->set('loteGalponId', (string) $galpon->id)
            ->set('loteTipoHuevo', TipoHuevo::Blanco->value)
            ->set('loteCantidad', '800')
            ->set('loteFechaNacimiento', now()->subWeeks(18)->format('Y-m-d'))
            ->call('guardarLoteCrear')
            ->assertHasErrors(['loteGalponId']);

        $this->assertDatabaseMissing('lotes', ['galpon_id' => $galpon->id]);
    }

    public function test_administrativo_can_create_lote_with_codigo_sma(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create([
            'codigo' => 'G-SMA',
            'aves_actuales' => 0,
        ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'lotes')
            ->call('abrirCrearLote')
            ->set('loteGalponId', (string) $galpon->id)
            ->set('loteCodigoSma', 'SMA-2026-001')
            ->set('loteTipoHuevo', TipoHuevo::Color->value)
            ->set('loteCantidad', '1500')
            ->set('loteFechaNacimiento', now()->subWeeks(16)->format('Y-m-d'))
            ->call('guardarLoteCrear')
            ->assertHasNoErrors();

        $fechaIngreso = now()->format('Ymd');

        $this->assertDatabaseHas('lotes', [
            'galpon_id' => $galpon->id,
            'codigo_sma' => 'SMA-2026-001',
            'codigo' => "G-SMA-{$fechaIngreso}-C-1",
            'cantidad_inicial' => 1500,
            'tipo_huevo' => TipoHuevo::Color->value,
        ]);
    }

    public function test_administrativo_can_update_lote_metadata_without_changing_estado(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();
        $lote = Lote::factory()
            ->forGalpon($galpon)
            ->create([
                'estado' => LoteEstado::EnProduccion,
                'observacion' => null,
                'linea_raza' => null,
            ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'lotes')
            ->call('abrirEditarLote', $lote->id)
            ->set('loteLineaRaza', 'Hy-Line Brown')
            ->set('loteObservacion', 'Lote en revisión')
            ->call('guardarLoteEditar')
            ->assertHasNoErrors();

        $lote->refresh();
        $this->assertSame(LoteEstado::EnProduccion, $lote->estado);
        $this->assertSame('Hy-Line Brown', $lote->linea_raza);
        $this->assertSame('Lote en revisión', $lote->observacion);
    }

    public function test_administrativo_can_transition_lote_estado_with_motivo(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();
        $lote = Lote::factory()
            ->forGalpon($galpon)
            ->create([
                'estado' => LoteEstado::EnProduccion,
            ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'lotes')
            ->call('abrirEditarLote', $lote->id)
            ->call('abrirTransicionLote')
            ->set('loteTransicionEstado', LoteEstado::Cerrado->value)
            ->set('loteTransicionMotivo', 'Cierre por rotación')
            ->call('guardarLoteTransicion')
            ->assertHasNoErrors();

        $lote->refresh();
        $this->assertSame(LoteEstado::Cerrado, $lote->estado);
        $this->assertCount(1, $lote->estado_historial);
        $this->assertSame('Cierre por rotación', $lote->estado_historial[0]['motivo']);
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
            ->assertHasErrors(['galponGranjaId']);

        $this->assertNull(Galpon::query()->where('nombre', 'Galpón ilegal')->first());
    }

    public function test_lotes_list_filters_by_estado_tipo_and_scoped_granja(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granjaA = Granja::factory()->create(['empresa_id' => $empresa->id, 'nombre' => 'Granja A']);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresa->id, 'nombre' => 'Granja B']);
        $galponA = Galpon::factory()->forGranja($granjaA)->create(['codigo' => 'GA']);
        $galponB = Galpon::factory()->forGranja($granjaB)->create(['codigo' => 'GB']);

        $loteVisible = Lote::factory()->forGalpon($galponA)->create([
            'codigo' => 'GA-LOTE-1',
            'estado' => LoteEstado::EnProduccion,
            'tipo_huevo' => TipoHuevo::Blanco,
        ]);

        Lote::factory()->forGalpon($galponA)->create([
            'codigo' => 'GA-LOTE-2',
            'estado' => LoteEstado::Cerrado,
            'tipo_huevo' => TipoHuevo::Color,
        ]);

        Lote::factory()->forGalpon($galponB)->create([
            'codigo' => 'GB-LOTE-1',
            'estado' => LoteEstado::EnProduccion,
            'tipo_huevo' => TipoHuevo::Blanco,
        ]);

        $component = Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'lotes')
            ->set('filtroGranjaId', (string) $granjaA->id)
            ->set('filtroLoteEstado', LoteEstado::EnProduccion->value)
            ->set('filtroLoteTipo', TipoHuevo::Blanco->value);

        $codigos = $component->viewData('lotes')->pluck('codigo')->all();

        $this->assertSame(['GA-LOTE-1'], $codigos);
    }

    public function test_foreign_granja_filter_does_not_leak_other_company_lotes(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaAjena = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($granjaAjena)->create(['codigo' => 'AJENO']);

        Lote::factory()->forGalpon($galponAjeno)->create(['codigo' => 'LOTE-AJENO-1']);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'lotes')
            ->set('filtroGranjaId', (string) $granjaAjena->id)
            ->assertDontSee('LOTE-AJENO-1')
            ->call('limpiarFiltros')
            ->assertSet('filtroGranjaId', '');
    }

    public function test_galpones_list_filters_by_estado_operativo(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        Galpon::factory()->forGranja($granja)->create([
            'nombre' => 'Galpón filtro activo',
            'codigo' => 'GAL-FILTRO-OK',
            'estado' => GalponEstado::Activo,
        ]);
        Galpon::factory()->forGranja($granja)->create([
            'nombre' => 'Galpón filtro pausa',
            'codigo' => 'GAL-FILTRO-PAUSA',
            'estado' => GalponEstado::EnMantenimiento,
        ]);

        $component = Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->set('filtroGalponEstado', GalponEstado::Activo->value);

        $galpones = $component->viewData('galpones');
        $codigos = $galpones->pluck('codigo')->all();

        $this->assertContains('GAL-FILTRO-OK', $codigos);
        $this->assertNotContains('GAL-FILTRO-PAUSA', $codigos);
    }

    public function test_administrativo_can_open_galpon_and_lote_ficha(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $granja = Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Granja ficha',
            'dicose' => '0999888777',
        ]);
        $galpon = Galpon::factory()->forGranja($granja)->create([
            'nombre' => 'Galpón ficha',
            'codigo' => 'GAL-FICHA',
            'aves_actuales' => 1500,
        ]);
        $lote = Lote::factory()->forGalpon($galpon)->create([
            'codigo' => 'LOTE-FICHA-01',
            'estado' => LoteEstado::EnProduccion,
            'cantidad_inicial' => 1500,
            'estado_historial' => [[
                'fecha' => now()->subDay()->toIso8601String(),
                'estado_anterior' => LoteEstado::Activo->value,
                'estado_nuevo' => LoteEstado::EnProduccion->value,
                'motivo' => 'Inicio producción',
                'actor_name' => $administrativo->name,
            ]],
        ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->assertSee('Ver ficha')
            ->call('abrirFichaGalpon', $galpon->id)
            ->assertSet('dialogGalponFichaAbierto', true)
            ->assertSee('Galpón ficha')
            ->assertSee('0999888777')
            ->assertSee('LOTE-FICHA-01')
            ->call('cerrarFichaGalpon')
            ->assertSet('dialogGalponFichaAbierto', false);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'lotes')
            ->call('abrirFichaLote', $lote->id)
            ->assertSet('dialogLoteFichaAbierto', true)
            ->assertSee('LOTE-FICHA-01')
            ->assertSee('Inicio producción')
            ->assertSee('Métricas de hoy');
    }

    public function test_encargado_can_view_ficha_but_not_edit_galpon(): void
    {
        [$empresa] = $this->empresaConDueno();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['nombre' => 'Galpón encargado']);

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        Livewire::actingAs($encargado)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->assertSee('Ver ficha')
            ->assertDontSee('wire:click="abrirEditarGalpon"', false)
            ->call('abrirFichaGalpon', $galpon->id)
            ->assertSet('dialogGalponFichaAbierto', true)
            ->assertSee('Galpón encargado');
    }

    public function test_foreign_lote_ficha_is_forbidden(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaAjena = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($granjaAjena)->create();
        $loteAjeno = Lote::factory()->forGalpon($galponAjeno)->create(['codigo' => 'LOTE-AJENO-FICHA']);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'lotes')
            ->call('abrirFichaLote', $loteAjeno->id)
            ->assertNotFound();
    }

    public function test_foreign_lote_does_not_expose_transition_options_when_editing_id_is_tampered(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaAjena = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($granjaAjena)->create();
        $loteAjeno = Lote::factory()->forGalpon($galponAjeno)->create(['estado' => LoteEstado::EnProduccion]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'lotes')
            ->set('editingLoteId', $loteAjeno->id)
            ->set('dialogLoteTransicionAbierto', true)
            ->assertViewHas('loteTransicionEstadoOptions', []);
    }

    public function test_granjas_list_filters_by_activa_and_shows_empty_hint(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();
        Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Granja filtro activa',
            'codigo' => 'GRJ-ACTIVA',
            'activa' => true,
        ]);
        Granja::factory()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Granja filtro pausada',
            'codigo' => 'GRJ-PAUSADA',
            'activa' => false,
        ]);

        Livewire::actingAs($administrativo)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'granjas')
            ->set('filtroGranjaActiva', '0')
            ->assertSee('GRJ-PAUSADA')
            ->assertDontSee('GRJ-ACTIVA')
            ->set('busqueda', 'ZZZ-SIN-RESULTADOS')
            ->assertSee('No hay resultados con los filtros actuales');
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
