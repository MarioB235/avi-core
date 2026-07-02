<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class IllustrationSvg
{
    public function markup(string $name, ?string $class = null): ?string
    {
        $path = resource_path("images/illustrations/{$name}.svg");

        if (! File::isReadable($path)) {
            return null;
        }

        $contents = trim(File::get($path));
        $contents = preg_replace('/<\?xml[^>]*>\s*/i', '', $contents);

        if (! preg_match('/<svg\b([^>]*)>(.*)<\/svg>/is', $contents, $matches)) {
            return null;
        }

        $attrs = $matches[1];
        $inner = $matches[2];

        if (preg_match('/viewBox\s*=\s*["\']([^"\']+)["\']/i', $attrs, $viewBoxMatch)) {
            $viewBox = $viewBoxMatch[1];
        } elseif (
            preg_match('/width\s*=\s*["\'](\d+(?:\.\d+)?)["\']/i', $attrs, $widthMatch)
            && preg_match('/height\s*=\s*["\'](\d+(?:\.\d+)?)["\']/i', $attrs, $heightMatch)
        ) {
            $viewBox = '0 0 '.$widthMatch[1].' '.$heightMatch[1];
        } else {
            $viewBox = '0 0 24 24';
        }

        $classAttr = e($class ?? 'avicore-ui-illustration');

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="'.e($viewBox).'" class="'.$classAttr.'" aria-hidden="true" focusable="false">'.$inner.'</svg>';
    }
}
