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

class SessionRolResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_change_blocks_previous_panel_on_next_request(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $this->actingAs($encargado)
            ->get(route('encargado.resumen.index'))
            ->assertOk();

        $encargado->update(['rol' => UserRole::Operario]);

        $this->actingAs($encargado)
            ->get(route('encargado.resumen.index'))
            ->assertRedirect(route('operario.home'));
    }

    public function test_password_reset_forces_change_on_next_request(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('operario.cargar'))
            ->assertOk();

        $operario->update(['must_change_password' => true]);

        $this->actingAs($operario)
            ->get(route('operario.cargar'))
            ->assertRedirect(route('password.change'));
    }

    public function test_livewire_action_after_role_change_does_not_persist_data(): void
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

        $operario->update(['rol' => UserRole::Reparto]);

        $payload = $this->livewireGuardarHuevosPayload($snapshot);

        $this->actingAs($operario)
            ->withHeader('X-Livewire', 'true')
            ->postJson(app('livewire')->getUpdateUri(), $payload)
            ->assertRedirect(route('reparto.home'));

        $this->assertSame(0, RegistroOperativo::query()->where('empresa_id', $operario->empresa_id)->count());
    }

    public function test_livewire_action_after_password_reset_redirects_to_change_password(): void
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

        $operario->update(['must_change_password' => true]);

        $payload = $this->livewireGuardarHuevosPayload($snapshot);

        $this->actingAs($operario)
            ->withHeader('X-Livewire', 'true')
            ->postJson(app('livewire')->getUpdateUri(), $payload)
            ->assertRedirect(route('password.change'));

        $this->assertSame(0, RegistroOperativo::query()->where('empresa_id', $operario->empresa_id)->count());
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    private function livewireGuardarHuevosPayload(array $snapshot): array
    {
        return [
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
