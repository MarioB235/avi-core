<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AvicoreAuthSeeder::class,
            AvicoreEquipoDemoSeeder::class,
            AvicoreEstructuraAvicolaSeeder::class,
            AvicoreOperarioDemoSeeder::class,
            AvicoreDuenoDemoSeeder::class,
        ]);
    }
}
