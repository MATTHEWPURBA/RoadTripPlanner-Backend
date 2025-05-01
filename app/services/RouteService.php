<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RouteService
{
    /**
     * Get route data between two points using OpenRouteService API.
     * 
     * @param array $origin [lat, lng]
     * @param array $destination [lat, lng]
     * @return array|null
     */
    public function getRouteData(array $origin, array $destination)
    {
        // Using OpenRouteService API for routing
        // Sign up for a free API key at https://openrouteservice.org/
        // Free tier: 2,000 requests per day
        $apiKey = env('OPENROUTE_SERVICE_API_KEY', '');
        
        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
                'Accept' => 'application/json, application/geo+json, application/gpx+xml',
                'Content-Type' => 'application/json',
            ])->post('https://api.openrouteservice.org/v2/directions/driving-car', [
                'coordinates' => [
                    [$origin[1], $origin[0]], // OpenRouteService expects [longitude, latitude]
                    [$destination[1], $destination[0]],
                ],
                'format' => 'geojson',
                'instructions' => false,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Extract the relevant information from the response
                $route = $data['features'][0] ?? null;
                
                if ($route) {
                    return [
                        'distance' => $route['properties']['segments'][0]['distance'] / 1000, // Convert to km
                        'duration' => $route['properties']['segments'][0]['duration'], // In seconds
                        'polyline' => json_encode($route['geometry']), // Store the route path as GeoJSON
                    ];
                }
            }
            
            Log::error('Failed to get route data from OpenRouteService: ' . $response->body());
            
            // Fallback to a simple direct line calculation if API fails
            return $this->calculateDirectDistance($origin, $destination);
            
        } catch (\Exception $e) {
            Log::error('Exception while getting route data: ' . $e->getMessage());
            
            // Fallback to a simple direct line calculation
            return $this->calculateDirectDistance($origin, $destination);
        }
    }
    
    /**
     * Calculate a direct distance between two points (as the crow flies).
     * This is a fallback when the routing API fails.
     *
     * @param array $origin [lat, lng]
     * @param array $destination [lat, lng]
     * @return array
     */
    private function calculateDirectDistance(array $origin, array $destination)
    {
        // Earth's radius in km
        $earthRadius = 6371;
        
        $lat1 = deg2rad($origin[0]);
        $lon1 = deg2rad($origin[1]);
        $lat2 = deg2rad($destination[0]);
        $lon2 = deg2rad($destination[1]);
        
        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;
        
        $a = sin($dLat / 2) * sin($dLat / 2) + cos($lat1) * cos($lat2) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        // Estimate duration based on average speed of 60 km/h
        $duration = $distance * 60; // seconds
        
        return [
            'distance' => $distance,
            'duration' => $duration,
            'polyline' => null, // No polyline in direct calculation
        ];
    }
    
    /**
     * Calculate estimated fuel consumption based on distance and vehicle efficiency
     *
     * @param float $distance Distance in kilometers
     * @param float $efficiency Fuel efficiency in L/100km (default 8.0)
     * @return float Fuel consumption in liters
     */
    public function calculateFuelConsumption(float $distance, float $efficiency = 8.0)
    {
        // Formula: distance (km) * efficiency (L/100km) / 100
        return ($distance * $efficiency) / 100;
    }
}


// This file is part of the Laravel framework.
// app/Services/RouteService.php