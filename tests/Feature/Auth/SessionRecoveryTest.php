<?php

namespace Tests\Feature\Auth;

use App\Actions\User\ResetUserPasswordAction;
use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Livewire\Admin\Usuarios\Index;
use App\Livewire\Auth\ChangePassword;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Tests\TestCase;

class SessionRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'database']);
    }

    public function test_logout_invalidates_session_and_redirects_to_login(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno);

        $this->get(route('dueno.home'))
            ->assertOk();

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_admin_password_reset_invalidates_existing_sessions(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '31313131',
            'rol' => UserRole::Operario,
            'password' => 'OldSecret123!',
            'must_change_password' => false,
        ]);

        DB::table('sessions')->insert([
            [
                'id' => 'remote-session',
                'user_id' => $operario->id,
                'ip_address' => '10.0.0.2',
                'user_agent' => 'other-device',
                'payload' => 'payload',
                'last_activity' => now()->timestamp,
            ],
        ]);

        app(ResetUserPasswordAction::class)->execute($administrativo, $operario);

        $this->assertDatabaseMissing('sessions', ['user_id' => $operario->id]);
        $this->assertTrue($operario->fresh()->must_change_password);
    }

    public function test_password_reset_does_not_log_plain_password(): void
    {
        Log::spy();

        [$empresa, $administrativo] = $this->empresaConAdministrativo();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '32323232',
            'rol' => UserRole::Operario,
            'password' => 'OldSecret123!',
            'must_change_password' => false,
        ]);

        $result = app(ResetUserPasswordAction::class)->execute($administrativo, $operario);

        Log::shouldNotHaveReceived('info');
        Log::shouldNotHaveReceived('debug');
        Log::shouldNotHaveReceived('warning');
        Log::shouldNotHaveReceived('error');

        $this->assertNotEmpty($result['plainPassword']);
    }

    public function test_voluntary_password_change_invalidates_other_sessions_only(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '34343434',
            'password' => 'Temporal2026!',
            'rol' => UserRole::Operario,
            'must_change_password' => true,
        ]);

        DB::table('sessions')->insert([
            [
                'id' => 'other-device',
                'user_id' => $operario->id,
                'ip_address' => '10.0.0.3',
                'user_agent' => 'phone',
                'payload' => 'payload',
                'last_activity' => now()->timestamp,
            ],
        ]);

        Livewire::actingAs($operario)
            ->test(ChangePassword::class)
            ->set('current_password', 'Temporal2026!')
            ->set('password', 'NuevaClave2026!')
            ->set('password_confirmation', 'NuevaClave2026!')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('operario.home'));

        $this->assertDatabaseMissing('sessions', ['id' => 'other-device']);
    }

    public function test_deactivating_user_removes_database_sessions(): void
    {
        [$empresa, $administrativo] = $this->empresaConAdministrativo('35353535');

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '36363636',
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
        ]);

        DB::table('sessions')->insert([
            [
                'id' => 'operario-session',
                'user_id' => $operario->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'payload',
                'last_activity' => now()->timestamp,
            ],
        ]);

        Livewire::actingAs($administrativo)
            ->test(Index::class)
            ->call('toggleActivo', $operario->id)
            ->assertDispatched('snackbar-show');

        $this->assertDatabaseMissing('sessions', ['user_id' => $operario->id]);
        $this->assertFalse($operario->fresh()->activo);
    }

    /**
     * @return array{0: Empresa, 1: User}
     */
    private function empresaConAdministrativo(string $documento = '30303030'): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => $documento,
            'rol' => UserRole::Administrativo,
            'must_change_password' => false,
        ]);

        return [$empresa, $administrativo];
    }
}
