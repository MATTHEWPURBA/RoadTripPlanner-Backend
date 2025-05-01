<?php

namespace Database\Seeders;

use App\Models\Trip;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create sample trips with realistic data
        Trip::create([
            'name' => 'Pacific Coast Highway Trip',
            'description' => 'A scenic drive along California\'s beautiful coastline',
            'start_date' => '2025-06-15',
            'end_date' => '2025-06-22',
            'total_distance' => 0, // Will be updated when routes are calculated
            'total_duration' => 0, // Will be updated when routes are calculated
            'fuel_consumption' => 0 // Will be updated when routes are calculated
        ]);

        Trip::create([
            'name' => 'National Parks Tour',
            'description' => 'Exploring America\'s most beautiful national parks',
            'start_date' => '2025-07-10',
            'end_date' => '2025-07-24',
            'total_distance' => 0,
            'total_duration' => 0,
            'fuel_consumption' => 0
        ]);

        Trip::create([
            'name' => 'East Coast Fall Foliage',
            'description' => 'Witnessing the beautiful autumn colors in New England',
            'start_date' => '2025-10-05',
            'end_date' => '2025-10-12',
            'total_distance' => 0,
            'total_duration' => 0,
            'fuel_consumption' => 0
        ]);
    }
}


// This file is part of the Laravel framework.
// database/seeders/TripSeeder.php