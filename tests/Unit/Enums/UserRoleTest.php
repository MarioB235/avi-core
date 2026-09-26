<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    /**
     * @return array<string, array{0: UserRole}>
     */
    public static function allRolesProvider(): array
    {
        $cases = [];

        foreach (UserRole::cases() as $role) {
            $cases[$role->value] = [$role];
        }

        return $cases;
    }

    #[DataProvider('allRolesProvider')]
    public function test_permission_methods_never_throw_for_any_role(UserRole $role): void
    {
        $role->label();
        $role->routePrefix();
        $role->homeRouteName();
        $role->panelRouteName('home');
        $role->isOperario();
        $role->usesAdminPanel();
        $role->canAccessOperarioMobile();
        $role->canCreateLote();
        $role->canViewUsers();
        $role->canManageUsers();
        $role->canResetUserPassword();
        $role->canViewEstructura();
        $role->canViewResumen();
        $role->canViewEquipo();
        $role->canViewComercial();
        $role->canManageEstructura();
        $role->canManageLotes();
        $role->assignableRoles();

        $this->addToAssertionCount(1);
    }

    public function test_reparto_is_denied_operario_and_admin_capabilities_without_expanding_access(): void
    {
        $role = UserRole::Reparto;

        $this->assertFalse($role->canAccessOperarioMobile());
        $this->assertFalse($role->canViewResumen());
        $this->assertFalse($role->canViewUsers());
        $this->assertFalse($role->canManageUsers());
        $this->assertFalse($role->canViewEstructura());
        $this->assertFalse($role->canViewEquipo());
        $this->assertFalse($role->canViewComercial());
        $this->assertFalse($role->canCreateLote());
        $this->assertSame([], $role->assignableRoles());
        $this->assertTrue($role->usesAdminPanel());
        $this->assertSame('reparto.home', $role->homeRouteName());
    }
}
