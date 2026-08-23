<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class AppBuildService
{
    public function productVersion(): string
    {
        return (string) config('avicore.version');
    }

    public function metadata(): ?array
    {
        $path = public_path('build/avicore-build.json');

        if (! File::exists($path)) {
            return null;
        }

        $data = json_decode(File::get($path), true);

        if (! is_array($data) || empty($data['built_at'])) {
            return null;
        }

        return $data;
    }

    public function labelForProfile(): ?string
    {
        $version = $this->productVersion();
        $metadata = $this->metadata();

        if ($metadata === null) {
            if (app()->environment('local')) {
                return "{$version} · Desarrollo local";
            }

            return $version !== '' ? $version : null;
        }

        $formatted = Carbon::parse($metadata['built_at'])
            ->locale(app()->getLocale())
            ->isoFormat('D MMM YYYY, HH:mm');

        $buildPart = ! empty($metadata['commit'])
            ? "{$formatted} ({$metadata['commit']})"
            : $formatted;

        return "{$version} · {$buildPart}";
    }
}
