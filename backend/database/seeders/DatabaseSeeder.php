<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            AdminIdSeeder::class,
            CareerAssessmentSeeder::class,
            CareerDetailsSeeder::class,
            CareerTraitsSeeder::class,
            RealCareersSeeder::class,
            TrendingCareersSeeder::class,
        ]);
    }
}
