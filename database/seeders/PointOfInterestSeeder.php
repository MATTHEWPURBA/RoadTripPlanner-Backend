<?php

namespace Database\Seeders;

use App\Models\RouteSegment;
use App\Models\PointOfInterest;
use Illuminate\Database\Seeder;

class PointOfInterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create POIs for all route segments
        $routeSegments = RouteSegment::all();
        
        foreach ($routeSegments as $segment) {
            $this->createPointsOfInterestForSegment($segment);
        }
    }
    
    /**
     * Create points of interest for a specific route segment
     * 
     * @param RouteSegment $segment
     */
    private function createPointsOfInterestForSegment($segment)
    {
        // Calculate midpoint between origin and destination
        $midLat = ($segment->origin->latitude + $segment->destination->latitude) / 2;
        $midLng = ($segment->origin->longitude + $segment->destination->longitude) / 2;
        
        // Add slight randomization to spread POIs around
        $latVariation = 0.05;
        $lngVariation = 0.05;
        
        // Trip 1: Pacific Coast Highway - Create relevant coastal POIs
        if ($segment->trip_id == 1) {
            $this->createCoastalPOIs($segment, $midLat, $midLng, $latVariation, $lngVariation);
        }
        // Trip 2: National Parks - Create nature and outdoor POIs
        else if ($segment->trip_id == 2) {
            $this->createNaturePOIs($segment, $midLat, $midLng, $latVariation, $lngVariation);
        }
        // Trip 3: Fall Foliage - Create New England themed POIs
        else if ($segment->trip_id == 3) {
            $this->createNewEnglandPOIs($segment, $midLat, $midLng, $latVariation, $lngVariation);
        }
    }
    
    /**
     * Create coastal-themed POIs for Pacific Coast Highway
     */
    private function createCoastalPOIs($segment, $midLat, $midLng, $latVar, $lngVar)
    {
        // Beach
        PointOfInterest::create([
            'route_segment_id' => $segment->id,
            'name' => 'Scenic Beach Overlook',
            'type' => 'natural.beach',
            'latitude' => $midLat + (mt_rand(-10, 10) / 100) * $latVar,
            'longitude' => $midLng + (mt_rand(-10, 10) / 100) * $lngVar,
            'description' => 'Beautiful sandy beach with stunning ocean views',
            'image_url' => null
        ]);
        
        // Seafood restaurant
        PointOfInterest::create([
            'route_segment_id' => $segment->id,
            'name' => 'Coastal Seafood Restaurant',
            'type' => 'catering.restaurant',
            'latitude' => $midLat + (mt_rand(-10, 10) / 100) * $latVar,
            'longitude' => $midLng + (mt_rand(-10, 10) / 100) * $lngVar,
            'description' => 'Fresh seafood with ocean views',
            'image_url' => null
        ]);
        
        // Lighthouse
        PointOfInterest::create([
            'route_segment_id' => $segment->id,
            'name' => 'Historic Lighthouse',
            'type' => 'tourism.sights',
            'latitude' => $midLat + (mt_rand(-10, 10) / 100) * $latVar,
            'longitude' => $midLng + (mt_rand(-10, 10) / 100) * $lngVar,
            'description' => 'Historic lighthouse with guided tours and spectacular views',
            'image_url' => null
        ]);
    }
    
    /**
     * Create nature-themed POIs for National Parks Tour
     */
    private function createNaturePOIs($segment, $midLat, $midLng, $latVar, $lngVar)
    {
        // Hiking Trail
        PointOfInterest::create([
            'route_segment_id' => $segment->id,
            'name' => 'Scenic Hiking Trail',
            'type' => 'leisure.park',
            'latitude' => $midLat + (mt_rand(-10, 10) / 100) * $latVar,
            'longitude' => $midLng + (mt_rand(-10, 10) / 100) * $lngVar,
            'description' => 'Beautiful hiking trail with diverse wildlife',
            'image_url' => null
        ]);
        
        // Waterfall
        PointOfInterest::create([
            'route_segment_id' => $segment->id,
            'name' => 'Mountain Waterfall',
            'type' => 'natural.water',
            'latitude' => $midLat + (mt_rand(-10, 10) / 100) * $latVar,
            'longitude' => $midLng + (mt_rand(-10, 10) / 100) * $lngVar,
            'description' => 'Majestic waterfall with viewing platform',
            'image_url' => null
        ]);
        
        // Wildlife Viewing Area
        PointOfInterest::create([
            'route_segment_id' => $segment->id,
            'name' => 'Wildlife Viewing Area',
            'type' => 'tourism.attraction',
            'latitude' => $midLat + (mt_rand(-10, 10) / 100) * $latVar,
            'longitude' => $midLng + (mt_rand(-10, 10) / 100) * $lngVar,
            'description' => 'Great spot for viewing local wildlife',
            'image_url' => null
        ]);
    }
    
    /**
     * Create New England themed POIs for Fall Foliage trip
     */
    private function createNewEnglandPOIs($segment, $midLat, $midLng, $latVar, $lngVar)
    {
        // Historic Covered Bridge
        PointOfInterest::create([
            'route_segment_id' => $segment->id,
            'name' => 'Historic Covered Bridge',
            'type' => 'tourism.sights',
            'latitude' => $midLat + (mt_rand(-10, 10) / 100) * $latVar,
            'longitude' => $midLng + (mt_rand(-10, 10) / 100) * $lngVar,
            'description' => 'Picturesque covered bridge dating back to the 1800s',
            'image_url' => null
        ]);
        
        // Apple Orchard
        PointOfInterest::create([
            'route_segment_id' => $segment->id,
            'name' => 'New England Apple Orchard',
            'type' => 'entertainment',
            'latitude' => $midLat + (mt_rand(-10, 10) / 100) * $latVar,
            'longitude' => $midLng + (mt_rand(-10, 10) / 100) * $lngVar,
            'description' => 'Pick your own apples and enjoy fresh cider',
            'image_url' => null
        ]);
        
        // Maple Syrup Farm
        PointOfInterest::create([
            'route_segment_id' => $segment->id,
            'name' => 'Maple Syrup Farm',
            'type' => 'tourism.attraction',
            'latitude' => $midLat + (mt_rand(-10, 10) / 100) * $latVar,
            'longitude' => $midLng + (mt_rand(-10, 10) / 100) * $lngVar,
            'description' => 'Traditional maple syrup production with tours and tastings',
            'image_url' => null
        ]);
    }
}


// This file is part of the Laravel framework.
// database/seeders/pointOfInterestSeeder.php