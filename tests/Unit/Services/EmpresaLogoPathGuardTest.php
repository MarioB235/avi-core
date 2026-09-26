<?php

namespace Tests\Unit\Services;

use App\Services\EmpresaLogoPathGuard;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EmpresaLogoPathGuardTest extends TestCase
{
    private EmpresaLogoPathGuard $guard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guard = app(EmpresaLogoPathGuard::class);
    }

    public function test_accepts_safe_logo_path(): void
    {
        $this->assertSame(
            'empresas/logos/demo.png',
            $this->guard->assertSafeStoredPath('empresas/logos/demo.png'),
        );
    }

    public function test_null_or_empty_path_returns_null(): void
    {
        $this->assertNull($this->guard->assertSafeStoredPath(null));
        $this->assertNull($this->guard->assertSafeStoredPath('   '));
    }

    #[DataProvider('unsafePathsProvider')]
    public function test_rejects_unsafe_logo_paths(string $path): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->guard->assertSafeStoredPath($path);
    }

    public static function unsafePathsProvider(): array
    {
        return [
            ['/etc/passwd'],
            ['https://evil.test/logo.png'],
            ['empresas/logos/../secret.png'],
            ['logos/demo.png'],
            ['../empresas/logos/demo.png'],
        ];
    }
}
