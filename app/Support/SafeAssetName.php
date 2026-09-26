<?php

namespace App\Support;

use InvalidArgumentException;

class SafeAssetName
{
    public static function assert(string $name): string
    {
        if ($name === '' || ! preg_match('/^[a-z0-9][a-z0-9_-]*$/i', $name)) {
            throw new InvalidArgumentException('Nombre de asset no válido.');
        }

        return $name;
    }
}
