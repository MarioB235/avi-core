<?php

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Granja;
use App\Models\User;
use App\Services\EmpresaScopeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpresaScopeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_for_actor_limits_to_same_empresa(): void
    {
        $empresaA = Empresa::factory()->create();
        $empresaB = Empresa::factory()->create();
        $granjaA = Granja::factory()->create(['empresa_id' => $empresaA->id]);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresaB->id]);

        $admin = User::factory()->create([
            'empresa_id' => $empresaA->id,
            'rol' => UserRole::Administrativo,
        ]);

        $service = app(EmpresaScopeService::class);

        $found = $service->findForActor(Granja::query(), $admin, $granjaA->id);
        $this->assertTrue($found->is($granjaA));

        $this->expectException(ModelNotFoundException::class);
        $service->findForActor(Granja::query(), $admin, $granjaB->id);
    }
}
