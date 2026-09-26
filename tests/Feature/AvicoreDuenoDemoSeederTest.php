<?php

namespace Tests\Feature;

use App\Models\Lote;
use Database\Seeders\AvicoreAuthSeeder;
use Database\Seeders\AvicoreDuenoDemoSeeder;
use Database\Seeders\AvicoreEstructuraAvicolaSeeder;
use Database\Seeders\AvicoreOperarioDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvicoreDuenoDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_adds_galpon_dos_lote_and_weekly_history(): void
    {
        $this->seed([
            AvicoreAuthSeeder::class,
            AvicoreEstructuraAvicolaSeeder::class,
            AvicoreOperarioDemoSeeder::class,
            AvicoreDuenoDemoSeeder::class,
        ]);

        $this->assertDatabaseHas('lotes', ['codigo' => 'L-2026-02']);

        $this->artisan('db:seed', ['--class' => AvicoreDuenoDemoSeeder::class])
            ->assertSuccessful();

        $this->assertEquals(1, Lote::query()->where('codigo', 'L-2026-02')->count());
    }
}
