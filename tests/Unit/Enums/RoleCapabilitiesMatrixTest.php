<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\RoleCapabilitiesMatrix;
use Tests\TestCase;

class RoleCapabilitiesMatrixTest extends TestCase
{
    /**
     * @return array<string, array{0: UserRole, 1: string, 2: bool}>
     */
    public static function capabilityExpectationsProvider(): array
    {
        $cases = [];

        foreach (RoleCapabilitiesMatrix::expectations() as $roleValue => $capabilities) {
            foreach ($capabilities as $method => $expected) {
                $cases["{$roleValue}::{$method}"] = [UserRole::from($roleValue), $method, $expected];
            }
        }

        return $cases;
    }

    #[DataProvider('capabilityExpectationsProvider')]
    public function test_user_role_capability_matches_documented_matrix(
        UserRole $role,
        string $method,
        bool $expected,
    ): void {
        $this->assertSame($expected, $role->{$method}());
    }

    public function test_matrix_covers_every_role_and_capability_method(): void
    {
        $methods = RoleCapabilitiesMatrix::capabilityMethods();

        foreach (UserRole::cases() as $role) {
            $this->assertArrayHasKey($role->value, RoleCapabilitiesMatrix::expectations());

            foreach ($methods as $method) {
                $this->assertArrayHasKey(
                    $method,
                    RoleCapabilitiesMatrix::expectations()[$role->value],
                    "Falta {$method} para {$role->value}",
                );
            }
        }
    }

    public function test_dueno_and_administrativo_are_complementary_not_overlapping_on_office_modules(): void
    {
        $dueno = RoleCapabilitiesMatrix::expectations()[UserRole::Dueno->value];
        $admin = RoleCapabilitiesMatrix::expectations()[UserRole::Administrativo->value];

        $this->assertTrue($dueno['canViewEquipo']);
        $this->assertFalse($admin['canViewEquipo']);

        $this->assertTrue($dueno['canViewComercial']);
        $this->assertFalse($admin['canViewComercial']);

        $this->assertFalse($dueno['canManageEstructura']);
        $this->assertTrue($admin['canManageEstructura']);

        $this->assertFalse($dueno['canManageUsers']);
        $this->assertTrue($admin['canManageUsers']);

        $this->assertTrue($dueno['canViewResumen']);
        $this->assertTrue($admin['canViewResumen']);
    }

    public function test_assignable_roles_match_d02_governance(): void
    {
        $this->assertSame(
            RoleCapabilitiesMatrix::assignableByDueno(),
            UserRole::Dueno->assignableRoles(),
        );

        $this->assertSame(
            RoleCapabilitiesMatrix::assignableByAdministrativo(),
            UserRole::Administrativo->assignableRoles(),
        );

        $this->assertNotContains(UserRole::Dueno, UserRole::Administrativo->assignableRoles());
    }
}
