<?php

namespace Tests\Unit\Support;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use App\Support\AdminNav;
use App\Support\OperarioNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavTabBarItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_tab_bar_items_match_tabs(): void
    {
        $items = OperarioNav::tabBarItems();

        $this->assertCount(3, $items);
        $this->assertSame(['Inicio', 'Cargar', 'Historial'], array_column($items, 'label'));
        $this->assertSame(['home', 'plus', 'calendar'], array_column($items, 'icon'));

        foreach ($items as $item) {
            $this->assertArrayHasKey('href', $item);
            $this->assertArrayHasKey('active', $item);
            $this->assertIsBool($item['active']);
        }
    }

    public function test_admin_tab_bar_items_match_tabs(): void
    {
        $empresa = Empresa::factory()->create();
        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'must_change_password' => false,
        ]);

        $this->actingAs($administrativo);

        $items = AdminNav::tabBarItems();

        $this->assertCount(4, $items);
        $this->assertSame(['Inicio', 'Resumen', 'Estructura', 'Usuarios'], array_column($items, 'label'));
        $this->assertSame(['home', 'chart', 'layers', 'users'], array_column($items, 'icon'));
        $this->assertSame(route('administrativo.home'), $items[0]['href']);
    }
}
