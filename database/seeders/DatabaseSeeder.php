<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the seeders in the correct order for relationships
        $this->call([
            TripSeeder::class,
            DestinationSeeder::class,
            RouteSegmentSeeder::class,
            PointOfInterestSeeder::class,
        ]);
    }
}