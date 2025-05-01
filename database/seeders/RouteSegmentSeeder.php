<?php

namespace Database\Seeders;

use App\Models\Trip;
use App\Models\Destination;
use App\Models\RouteSegment;
use Illuminate\Database\Seeder;

class RouteSegmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create route segments for all trips
        $this->createRouteSegmentsForTrip(1);
        $this->createRouteSegmentsForTrip(2);
        $this->createRouteSegmentsForTrip(3);
        
        // Update trip totals
        $this->updateTripTotals();
    }
    
    /**
     * Create route segments for a specific trip
     * 
     * @param int $tripId
     */
    private function createRouteSegmentsForTrip($tripId)
    {
        $destinations = Destination::where('trip_id', $tripId)
            ->orderBy('order')
            ->get();
        
        // Create route segments between consecutive destinations
        for ($i = 0; $i < count($destinations) - 1; $i++) {
            $origin = $destinations[$i];
            $destination = $destinations[$i + 1];
            
            // Calculate distance (approximated)
            $distance = $this->calculateDistance(
                $origin->latitude, $origin->longitude,
                $destination->latitude, $destination->longitude
            );
            
            // Estimate duration based on average speed of 60 km/h
            // Convert to integer for PostgreSQL compatibility
            $duration = (int)round($distance * 60); // seconds
            
            RouteSegment::create([
                'trip_id' => $tripId,
                'origin_id' => $origin->id,
                'destination_id' => $destination->id,
                'distance' => $distance,
                'duration' => $duration,
                'polyline' => null // No polyline for test data
            ]);
        }
    }
    
    /**
     * Calculate the distance between two points using Haversine formula
     * 
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in kilometers
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        return $distance;
    }
    
    /**
     * Update trip totals based on route segments
     */
    private function updateTripTotals()
    {
        $trips = Trip::all();
        
        foreach ($trips as $trip) {
            $routeSegments = RouteSegment::where('trip_id', $trip->id)->get();
            
            $totalDistance = $routeSegments->sum('distance');
            $totalDuration = $routeSegments->sum('duration');
            
            // Calculate fuel consumption (assuming 8L/100km)
            $fuelConsumption = $totalDistance * 0.08;
            
            $trip->update([
                'total_distance' => $totalDistance,
                'total_duration' => $totalDuration,
                'fuel_consumption' => $fuelConsumption
            ]);
        }
    }
}

// This file is part of the Laravel framework.
// database/seeders/routeSegmentSeeder.php