<?php

namespace Tests\Unit\Support;

use App\Support\AuditoriaMetadataSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuditoriaMetadataSanitizerTest extends TestCase
{
    #[DataProvider('sensitiveMetadataProvider')]
    public function test_redacta_claves_sensibles_sin_perder_contexto(array $input, array $expected): void
    {
        $this->assertSame($expected, AuditoriaMetadataSanitizer::sanitize($input));
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>}>
     */
    public static function sensitiveMetadataProvider(): array
    {
        return [
            'password plano' => [
                ['documento' => '123', 'password' => 'Secr3t0!', 'rol' => 'operario'],
                ['documento' => '123', 'password' => '[redactado]', 'rol' => 'operario'],
            ],
            'anidado' => [
                ['antes' => ['plainPassword' => 'abc'], 'despues' => ['activo' => true]],
                ['antes' => ['plainPassword' => '[redactado]'], 'despues' => ['activo' => true]],
            ],
            'idempotencia permitida' => [
                ['idempotencia_clave' => 'uuid-123', 'tipo' => 'huevos'],
                ['idempotencia_clave' => 'uuid-123', 'tipo' => 'huevos'],
            ],
        ];
    }
}
