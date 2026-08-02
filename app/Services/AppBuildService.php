<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class AppBuildService
{
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
        $metadata = $this->metadata();

        if ($metadata === null) {
            return app()->environment('local') ? 'Desarrollo local' : null;
        }

        $formatted = Carbon::parse($metadata['built_at'])
            ->locale(app()->getLocale())
            ->isoFormat('D MMM YYYY, HH:mm');

        if (! empty($metadata['commit'])) {
            return "{$formatted} ({$metadata['commit']})";
        }

        return $formatted;
    }
}
