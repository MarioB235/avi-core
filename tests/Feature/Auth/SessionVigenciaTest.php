<?php

namespace Tests\Feature\Auth;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Drawer\Utils;
use Tests\TestCase;

class SessionVigenciaTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivated_user_is_logged_out_on_next_request(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
            'activo' => true,
        ]);

        $this->actingAs($dueno)
            ->get(route('dueno.home'))
            ->assertOk();

        $dueno->update(['activo' => false]);

        $this->actingAs($dueno)
            ->get(route('dueno.home'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_suspended_empresa_logs_out_existing_session(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('operario.home'))
            ->assertOk();

        $empresa->update(['estado' => EmpresaEstado::Suspendida]);

        $this->actingAs($operario)
            ->get(route('operario.cargar'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_livewire_action_after_deactivation_does_not_persist_data(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalpon();
        $operario->forceFill(['ultimo_galpon_id' => $galpon->id])->save();

        $pageResponse = $this->actingAs($operario)
            ->get(route('operario.cargar'));

        $pageResponse->assertOk();

        $snapshot = Utils::extractAttributeDataFromHtml(
            $pageResponse->getContent(),
            'wire:snapshot',
        );

        $operario->update(['activo' => false]);

        $payload = [
            'components' => [
                [
                    'snapshot' => json_encode($snapshot),
                    'calls' => [
                        [
                            'method' => 'guardarHuevos',
                            'params' => [],
                            'path' => '',
                        ],
                    ],
                    'updates' => [
                        'dialogHuevosAbierto' => true,
                        'huevos' => '100',
                        'huevosDescarte' => '0',
                    ],
                ],
            ],
        ];

        $this->actingAs($operario)
            ->withHeader('X-Livewire', 'true')
            ->postJson(app('livewire')->getUpdateUri(), $payload)
            ->assertRedirect(route('login'));

        $this->assertSame(0, RegistroOperativo::query()->where('empresa_id', $operario->empresa_id)->count());
        $this->assertGuest();
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function createOperarioConGalpon(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->conLoteActivo()->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        return [$operario, $galpon];
    }
}
