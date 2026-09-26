<?php

namespace Tests\Unit\Support;

use App\Support\SafeAssetName;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SafeAssetNameTest extends TestCase
{
    #[DataProvider('validNamesProvider')]
    public function test_accepts_safe_asset_names(string $name): void
    {
        $this->assertSame($name, SafeAssetName::assert($name));
    }

    public static function validNamesProvider(): array
    {
        return [
            ['bird'],
            ['operario-home'],
            ['icon_v2'],
        ];
    }

    #[DataProvider('invalidNamesProvider')]
    public function test_rejects_unsafe_asset_names(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        SafeAssetName::assert($name);
    }

    public static function invalidNamesProvider(): array
    {
        return [
            [''],
            ['../secrets'],
            ['foo/bar'],
            ['foo.svg'],
            ['foo bar'],
        ];
    }
}
