<?php

namespace App\Services;

use Illuminate\Support\Str;

class TemporaryPasswordGenerator
{
    public function generate(int $length = 12): string
    {
        return Str::password($length, letters: true, numbers: true, symbols: false, spaces: false);
    }
}
