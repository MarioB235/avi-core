<?php

namespace App\Support;

class AuditoriaMetadataSanitizer
{
    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public static function sanitize(array $metadata): array
    {
        return self::sanitizeValue($metadata);
    }

    private static function sanitizeValue(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $sanitized = [];

        foreach ($value as $key => $item) {
            if (self::isSensitiveKey((string) $key)) {
                $sanitized[$key] = '[redactado]';

                continue;
            }

            $sanitized[$key] = self::sanitizeValue($item);
        }

        return $sanitized;
    }

    private static function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach ([
            'password',
            'contraseña',
            'contrasena',
            'plainpassword',
            'plain_password',
            'token',
            'secret',
            'api_key',
            'authorization',
            'remember_token',
            'clave_temporal',
        ] as $fragment) {
            if (str_contains($normalized, $fragment)) {
                return true;
            }
        }

        return false;
    }
}
