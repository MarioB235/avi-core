<?php

namespace Tests\Unit\Services;

use App\Services\AppBuildService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AppBuildServiceTest extends TestCase
{
    private string $buildMetaPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMetaPath = public_path('build/avicore-build.json');
    }

    protected function tearDown(): void
    {
        if (File::exists($this->buildMetaPath)) {
            File::delete($this->buildMetaPath);
        }

        parent::tearDown();
    }

    public function test_product_version_returns_config_value(): void
    {
        config(['avicore.version' => '0.2.0']);

        $this->assertSame('0.2.0', app(AppBuildService::class)->productVersion());
    }

    public function test_metadata_returns_null_when_file_missing(): void
    {
        if (File::exists($this->buildMetaPath)) {
            File::delete($this->buildMetaPath);
        }

        $this->assertNull(app(AppBuildService::class)->metadata());
    }

    public function test_metadata_returns_null_when_json_invalid(): void
    {
        File::ensureDirectoryExists(public_path('build'));
        File::put($this->buildMetaPath, 'not-json');

        $this->assertNull(app(AppBuildService::class)->metadata());
    }

    public function test_metadata_returns_null_when_built_at_missing(): void
    {
        File::ensureDirectoryExists(public_path('build'));
        File::put($this->buildMetaPath, json_encode([
            'commit' => 'abc1234',
        ], JSON_THROW_ON_ERROR));

        $this->assertNull(app(AppBuildService::class)->metadata());
    }

    public function test_metadata_returns_array_when_valid(): void
    {
        File::ensureDirectoryExists(public_path('build'));
        File::put($this->buildMetaPath, json_encode([
            'built_at' => '2026-08-01T14:30:00+00:00',
            'commit' => 'abc1234',
        ], JSON_THROW_ON_ERROR));

        $metadata = app(AppBuildService::class)->metadata();

        $this->assertIsArray($metadata);
        $this->assertSame('2026-08-01T14:30:00+00:00', $metadata['built_at']);
        $this->assertSame('abc1234', $metadata['commit']);
    }

    public function test_label_for_profile_formats_build_metadata(): void
    {
        File::ensureDirectoryExists(public_path('build'));
        File::put($this->buildMetaPath, json_encode([
            'built_at' => '2026-08-01T14:30:00+00:00',
            'commit' => 'abc1234',
        ], JSON_THROW_ON_ERROR));

        $label = app(AppBuildService::class)->labelForProfile();

        $this->assertNotNull($label);
        $this->assertStringStartsWith(config('avicore.version').' · ', $label);
        $this->assertStringContainsString('2026', $label);
        $this->assertStringContainsString('(abc1234)', $label);
    }

    public function test_label_for_profile_formats_date_without_commit(): void
    {
        File::ensureDirectoryExists(public_path('build'));
        File::put($this->buildMetaPath, json_encode([
            'built_at' => '2026-08-01T14:30:00+00:00',
        ], JSON_THROW_ON_ERROR));

        $label = app(AppBuildService::class)->labelForProfile();

        $this->assertNotNull($label);
        $this->assertStringStartsWith(config('avicore.version').' · ', $label);
        $this->assertStringContainsString('2026', $label);
        $this->assertStringNotContainsString('(', $label);
    }

    public function test_label_for_profile_returns_version_only_in_production_without_build_file(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        if (File::exists($this->buildMetaPath)) {
            File::delete($this->buildMetaPath);
        }

        config(['avicore.version' => '0.1.0']);

        $label = app(AppBuildService::class)->labelForProfile();

        $this->assertSame('0.1.0', $label);
    }

    public function test_label_for_profile_returns_local_hint_without_build_file_in_local(): void
    {
        $this->app->detectEnvironment(fn () => 'local');

        if (File::exists($this->buildMetaPath)) {
            File::delete($this->buildMetaPath);
        }

        $label = app(AppBuildService::class)->labelForProfile();

        $this->assertSame(config('avicore.version').' · Desarrollo local', $label);
    }
}
