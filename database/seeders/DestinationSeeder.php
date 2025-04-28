<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Trip 1: Pacific Coast Highway Trip
        // San Francisco to Los Angeles
        $this->createPacificCoastHighwayDestinations();
        
        // Trip 2: National Parks Tour
        $this->createNationalParksTourDestinations();
        
        // Trip 3: East Coast Fall Foliage
        $this->createEastCoastFallFoliageDestinations();
    }
    
    /**
     * Create destinations for Pacific Coast Highway Trip
     */
    private function createPacificCoastHighwayDestinations()
    {
        // San Francisco to Los Angeles
        Destination::create([
            'trip_id' => 1,
            'name' => 'San Francisco',
            'latitude' => 37.7749,
            'longitude' => -122.4194,
            'address' => 'San Francisco, CA, USA',
            'order' => 0
        ]);
        
        Destination::create([
            'trip_id' => 1,
            'name' => 'Santa Cruz',
            'latitude' => 36.9741,
            'longitude' => -122.0308,
            'address' => 'Santa Cruz, CA, USA',
            'order' => 1
        ]);
        
        Destination::create([
            'trip_id' => 1,
            'name' => 'Monterey',
            'latitude' => 36.6002,
            'longitude' => -121.8947,
            'address' => 'Monterey, CA, USA',
            'order' => 2
        ]);
        
        Destination::create([
            'trip_id' => 1,
            'name' => 'Big Sur',
            'latitude' => 36.2704,
            'longitude' => -121.8081,
            'address' => 'Big Sur, CA, USA',
            'order' => 3
        ]);
        
        Destination::create([
            'trip_id' => 1,
            'name' => 'San Luis Obispo',
            'latitude' => 35.2828,
            'longitude' => -120.6596,
            'address' => 'San Luis Obispo, CA, USA',
            'order' => 4
        ]);
        
        Destination::create([
            'trip_id' => 1,
            'name' => 'Santa Barbara',
            'latitude' => 34.4208,
            'longitude' => -119.6982,
            'address' => 'Santa Barbara, CA, USA',
            'order' => 5
        ]);
        
        Destination::create([
            'trip_id' => 1,
            'name' => 'Los Angeles',
            'latitude' => 34.0522,
            'longitude' => -118.2437,
            'address' => 'Los Angeles, CA, USA',
            'order' => 6
        ]);
    }
    
    /**
     * Create destinations for National Parks Tour
     */
    private function createNationalParksTourDestinations()
    {
        // National Parks Tour
        Destination::create([
            'trip_id' => 2,
            'name' => 'Denver',
            'latitude' => 39.7392,
            'longitude' => -104.9903,
            'address' => 'Denver, CO, USA',
            'order' => 0
        ]);
        
        Destination::create([
            'trip_id' => 2,
            'name' => 'Rocky Mountain National Park',
            'latitude' => 40.3428,
            'longitude' => -105.6836,
            'address' => 'Rocky Mountain National Park, CO, USA',
            'order' => 1
        ]);
        
        Destination::create([
            'trip_id' => 2,
            'name' => 'Grand Teton National Park',
            'latitude' => 43.7904,
            'longitude' => -110.6818,
            'address' => 'Grand Teton National Park, WY, USA',
            'order' => 2
        ]);
        
        Destination::create([
            'trip_id' => 2,
            'name' => 'Yellowstone National Park',
            'latitude' => 44.4280,
            'longitude' => -110.5885,
            'address' => 'Yellowstone National Park, WY, USA',
            'order' => 3
        ]);
        
        Destination::create([
            'trip_id' => 2,
            'name' => 'Glacier National Park',
            'latitude' => 48.7596,
            'longitude' => -113.7870,
            'address' => 'Glacier National Park, MT, USA',
            'order' => 4
        ]);
    }
    
    /**
     * Create destinations for East Coast Fall Foliage
     */
    private function createEastCoastFallFoliageDestinations()
    {
        // East Coast Fall Foliage
        Destination::create([
            'trip_id' => 3,
            'name' => 'Boston',
            'latitude' => 42.3601,
            'longitude' => -71.0589,
            'address' => 'Boston, MA, USA',
            'order' => 0
        ]);
        
        Destination::create([
            'trip_id' => 3,
            'name' => 'White Mountains',
            'latitude' => 44.2701,
            'longitude' => -71.3033,
            'address' => 'White Mountains, NH, USA',
            'order' => 1
        ]);
        
        Destination::create([
            'trip_id' => 3,
            'name' => 'Green Mountains',
            'latitude' => 43.8717,
            'longitude' => -72.4469,
            'address' => 'Green Mountains, VT, USA',
            'order' => 2
        ]);
        
        Destination::create([
            'trip_id' => 3,
            'name' => 'Adirondack Mountains',
            'latitude' => 44.2268,
            'longitude' => -73.9759,
            'address' => 'Adirondack Mountains, NY, USA',
            'order' => 3
        ]);
        
        Destination::create([
            'trip_id' => 3,
            'name' => 'New York City',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'address' => 'New York, NY, USA',
            'order' => 4
        ]);
    }
}