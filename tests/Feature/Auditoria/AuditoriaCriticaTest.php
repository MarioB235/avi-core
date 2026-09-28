<?php

namespace Tests\Feature\Auditoria;

use App\Actions\Auditoria\CorregirRegistroOperativoAction;
use App\Actions\Empresa\UpdateEmpresaConfiguracionAction;
use App\Actions\Lote\TransicionarLoteEstadoAction;
use App\Actions\Operacion\AnularRegistroOperativoAction;
use App\Actions\User\CreateUserAction;
use App\Actions\User\ResetUserPasswordAction;
use App\Actions\User\UpdateUserAction;
use App\Enums\AuditoriaCategoria;
use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Models\Auditoria;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaCriticaTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_usuario_registra_auditoria_sin_password(): void
    {
        [$administrativo] = $this->empresaConAdministrativo();

        app(CreateUserAction::class)->execute($administrativo, [
            'name' => 'Nuevo Operario',
            'documento' => '99887766',
            'rol' => UserRole::Operario->value,
        ]);

        $auditoria = Auditoria::query()->sole();

        $this->assertSame(AuditoriaCategoria::Usuario, $auditoria->categoria);
        $this->assertSame('creado', $auditoria->accion);
        $this->assertSame($administrativo->id, $auditoria->actor_id);
        $this->assertSame('99887766', $auditoria->metadata['documento']);
        $this->assertSame('[redactado]', $auditoria->metadata['password']);
    }

    public function test_reset_password_registra_auditoria_sin_clave(): void
    {
        [$administrativo, $operario] = $this->empresaConAdministrativoYOperario();

        app(ResetUserPasswordAction::class)->execute($administrativo, $operario);

        $auditoria = Auditoria::query()
            ->where('accion', 'password_reseteado')
            ->sole();

        $this->assertSame(AuditoriaCategoria::Usuario, $auditoria->categoria);
        $this->assertSame($operario->id, $auditoria->entidad_id);
        $this->assertSame('[redactado]', $auditoria->metadata['plainPassword']);
    }

    public function test_transicion_lote_registra_quien_que_cuando_y_por_que(): void
    {
        [$encargado, $lote] = $this->encargadoConLote();

        app(TransicionarLoteEstadoAction::class)->execute($encargado, $lote, [
            'estado' => LoteEstado::Cerrado->value,
            'motivo' => 'Fin de ciclo productivo',
        ]);

        $auditoria = Auditoria::query()->sole();

        $this->assertSame(AuditoriaCategoria::Lote, $auditoria->categoria);
        $this->assertSame('estado_cambiado', $auditoria->accion);
        $this->assertSame($encargado->id, $auditoria->actor_id);
        $this->assertSame('Fin de ciclo productivo', $auditoria->motivo);
        $this->assertSame(LoteEstado::EnProduccion->value, $auditoria->metadata['estado_anterior']);
        $this->assertSame(LoteEstado::Cerrado->value, $auditoria->metadata['estado_nuevo']);
    }

    public function test_anulacion_y_correccion_registran_auditoria_operativa(): void
    {
        [$encargado, $operario, $galpon] = $this->encargadoConOperario();

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => 5,
            ]);

        $galpon->decrement('aves_actuales', 5);

        app(AnularRegistroOperativoAction::class)->execute($encargado, $registro, 'Doble carga');

        $registroCorregible = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => 4,
            ]);

        $galpon->decrement('aves_actuales', 4);

        app(CorregirRegistroOperativoAction::class)->execute(
            $encargado,
            $registroCorregible,
            'Ajuste supervisor',
            ['muertes' => 2],
        );

        $auditorias = Auditoria::query()->orderBy('id')->get();

        $this->assertCount(2, $auditorias);
        $this->assertSame('anulado', $auditorias[0]->accion);
        $this->assertSame(AuditoriaCategoria::Operacion, $auditorias[0]->categoria);
        $this->assertSame('Doble carga', $auditorias[0]->motivo);
        $this->assertSame('corregido', $auditorias[1]->accion);
        $this->assertSame(AuditoriaCategoria::Correccion, $auditorias[1]->categoria);
        $this->assertSame('Ajuste supervisor', $auditorias[1]->motivo);
        $this->assertArrayHasKey('valores_anteriores', $auditorias[1]->metadata);
    }

    public function test_actualizar_usuario_registra_auditoria_sin_datos_sensibles(): void
    {
        [$administrativo, $operario] = $this->empresaConAdministrativoYOperario();

        app(UpdateUserAction::class)->execute($administrativo, $operario, [
            'name' => 'Operario Renombrado',
            'documento' => $operario->documento,
            'email' => $operario->email,
            'rol' => UserRole::Encargado->value,
            'activo' => true,
        ]);

        $auditoria = Auditoria::query()
            ->where('accion', 'actualizado')
            ->sole();

        $this->assertSame(AuditoriaCategoria::Usuario, $auditoria->categoria);
        $this->assertSame($operario->id, $auditoria->entidad_id);
        $this->assertSame(UserRole::Operario->value, $auditoria->metadata['antes']['rol']);
        $this->assertSame(UserRole::Encargado->value, $auditoria->metadata['despues']['rol']);
        $this->assertArrayNotHasKey('password', $auditoria->metadata);
    }

    public function test_actualizar_configuracion_empresa_registra_auditoria(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create([
            'nombre' => 'Empresa Original',
            'configuracion' => ['estado_historial' => [['motivo' => 'test']]],
        ]);

        app(UpdateEmpresaConfiguracionAction::class)->execute($admin, $empresa, [
            'nombre' => 'Empresa Actualizada',
            'zona_horaria' => 'America/Montevideo',
            'huevos_por_maple' => 30,
            'maples_por_cajon' => 12,
        ]);

        $auditoria = Auditoria::query()
            ->where('accion', 'configuracion_actualizada')
            ->sole();

        $this->assertSame(AuditoriaCategoria::Empresa, $auditoria->categoria);
        $this->assertSame($empresa->id, $auditoria->entidad_id);
        $this->assertSame('Empresa Original', $auditoria->metadata['antes']['nombre']);
        $this->assertSame('Empresa Actualizada', $auditoria->metadata['despues']['nombre']);
        $this->assertSame('America/Montevideo', $auditoria->metadata['despues']['zona_horaria']);
    }

    /**
     * @return array{0: User}
     */
    private function empresaConAdministrativo(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'must_change_password' => false,
        ]);

        return [$administrativo];
    }

    /**
     * @return array{0: User, 1: User}
     */
    private function empresaConAdministrativoYOperario(): array
    {
        [$administrativo] = $this->empresaConAdministrativo();

        $operario = User::factory()->create([
            'empresa_id' => $administrativo->empresa_id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        return [$administrativo, $operario];
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
        ]);

        return [$encargado, $lote];
    }

    /**
     * @return array{0: User, 1: User, 2: Galpon}
     */
    private function encargadoConOperario(): array
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

        return [$encargado, $operario, $galpon];
    }
}
