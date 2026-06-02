<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class IconSvg
{
    public function fileMarkup(string $name): ?string
    {
        $path = resource_path("images/icons/{$name}.svg");

        if (! File::isReadable($path)) {
            return null;
        }

        $contents = File::get($path);

        if (preg_match('/<svg\b[^>]*>(.*)<\/svg>/is', $contents, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
