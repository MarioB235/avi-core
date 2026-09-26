<?php

namespace Tests\Feature\Security;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\IconSvg;
use App\Support\IllustrationSvg;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TechnicalSurfacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_route_uses_web_middleware_group(): void
    {
        $route = app('router')->getRoutes()->getByName('logout');

        $this->assertNotNull($route);
        $this->assertContains('web', $route->gatherMiddleware());
        $this->assertContains('auth', $route->gatherMiddleware());
    }

    public function test_logout_with_csrf_token_succeeds(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::query()->where('documento', '000000000')->firstOrFail();

        $this->actingAs($user)
            ->post(route('logout'), ['_token' => csrf_token()])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_layouts_expose_csrf_meta_tag(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="csrf-token"', false);
    }

    public function test_icon_svg_rejects_path_traversal_names(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(IconSvg::class)->fileMarkup('../secrets');
    }

    public function test_illustration_svg_rejects_path_traversal_names(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(IllustrationSvg::class)->markup('../secrets');
    }

    public function test_user_name_is_escaped_in_admin_users_list(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('documento', '66666666')->firstOrFail();
        $target = User::query()->where('documento', '11111111')->firstOrFail();
        $target->forceFill(['name' => '<script>alert(1)</script>'])->save();

        $this->actingAs($admin)
            ->get(route('administrativo.usuarios.index'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_operario_user_is_redirected_away_from_admin_panel(): void
    {
        $this->seed(DatabaseSeeder::class);

        $operario = User::query()
            ->where('documento', '11111111')
            ->where('rol', UserRole::Operario)
            ->firstOrFail();

        $this->actingAs($operario)
            ->get(route('administrativo.usuarios.index'))
            ->assertRedirect(route('operario.home'));
    }
}
