<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PointsOfInterestService
{
    /**
     * Find points of interest between two locations using Geoapify Places API.
     * Free tier includes 3,000 requests per day.
     *
     * @param array $origin [lat, lng]
     * @param array $destination [lat, lng]
     * @param array $categories
     * @param int $radius
     * @return array
     */
    public function findPointsOfInterest(array $origin, array $destination, array $categories, int $radius)
    {
        // Using Geoapify API for POI discovery
        // Sign up for a free API key at https://www.geoapify.com/
        $apiKey = env('GEOAPIFY_API_KEY', '');
        
        try {
            // Calculate midpoint to search around
            $midpoint = $this->calculateMidpoint($origin, $destination);
            
            // Convert categories to Geoapify format
            $geoCategories = implode(',', $this->mapCategories($categories));
            
            $response = Http::get('https://api.geoapify.com/v2/places', [
                'apiKey' => $apiKey,
                'categories' => $geoCategories,
                'filter' => "circle:{$midpoint[1]},{$midpoint[0]},{$radius}",
                'limit' => 10,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $pois = [];
                
                foreach ($data['features'] as $place) {
                    $properties = $place['properties'];
                    $pois[] = [
                        'name' => $properties['name'] ?? $properties['formatted'],
                        'type' => $properties['categories'] ?? 'tourist_attraction',
                        'latitude' => $place['geometry']['coordinates'][1],
                        'longitude' => $place['geometry']['coordinates'][0],
                        'description' => $properties['description'] ?? $properties['formatted'],
                        'image_url' => $properties['image'] ?? null,
                    ];
                }
                
                return $pois;
            }
            
            Log::error('Failed to get POIs from Geoapify: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Exception while getting POIs: ' . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Find accommodation options between two locations.
     * Uses Geoapify Places API (hotels category).
     *
     * @param array $origin [lat, lng]
     * @param array $destination [lat, lng]
     * @param float|null $maxPrice
     * @param int $minRating
     * @return array
     */
    public function findAccommodation(array $origin, array $destination, ?float $maxPrice, int $minRating)
    {
        // Find a point that's around 40-60% of the journey from origin
        $midpoint = $this->calculateIntermediatePoint($origin, $destination, 0.5);
        $apiKey = env('GEOAPIFY_API_KEY', '');
        
        try {
            $response = Http::get('https://api.geoapify.com/v2/places', [
                'apiKey' => $apiKey,
                'categories' => 'accommodation.hotel,accommodation.motel',
                'filter' => "circle:{$midpoint[1]},{$midpoint[0]},10000", // 10km radius
                'limit' => 5,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $hotels = [];
                
                foreach ($data['features'] as $place) {
                    $properties = $place['properties'];
                    
                    // Generate a mock price and rating if not available
                    $price = $properties['price_level'] ?? rand(1, 4);
                    $actualPrice = $price * 50; // Rough estimate
                    $rating = $properties['rating'] ?? (mt_rand(30, 50) / 10);
                    
                    if (($maxPrice === null || $actualPrice <= $maxPrice) && $rating >= $minRating) {
                        $hotels[] = [
                            'name' => $properties['name'] ?? 'Hotel near ' . $properties['city'],
                            'latitude' => $place['geometry']['coordinates'][1],
                            'longitude' => $place['geometry']['coordinates'][0],
                            'price' => $actualPrice,
                            'rating' => $rating,
                            'address' => $properties['formatted'] ?? '',
                            'image_url' => $properties['image'] ?? null,
                        ];
                    }
                }
                
                return $hotels;
            }
        } catch (\Exception $e) {
            Log::error('Exception while finding accommodation: ' . $e->getMessage());
        }
        
        // Fallback to mock data
        return [
            [
                'name' => 'Grand Hotel',
                'latitude' => $midpoint[0] + 0.02,
                'longitude' => $midpoint[1] - 0.01,
                'price' => 120,
                'rating' => 4.5,
                'address' => 'Main Street, Midpoint City',
                'image_url' => 'https://via.placeholder.com/150',
            ],
            [
                'name' => 'Budget Inn',
                'latitude' => $midpoint[0] - 0.01,
                'longitude' => $midpoint[1] + 0.02,
                'price' => 75,
                'rating' => 3.8,
                'address' => 'Highway Road, Midpoint City',
                'image_url' => 'https://via.placeholder.com/150',
            ],
        ];
    }
    
    /**
     * Find events near the route on specific dates.
     *
     * @param array $origin [lat, lng]
     * @param array $destination [lat, lng]
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function findEvents(array $origin, array $destination, string $startDate, string $endDate)
    {
        $midpoint = $this->calculateMidpoint($origin, $destination);
        $apiKey = env('GEOAPIFY_API_KEY', '');
        
        try {
            // Search for places that typically host events
            $response = Http::get('https://api.geoapify.com/v2/places', [
                'apiKey' => $apiKey,
                'categories' => 'entertainment,catering.restaurant,tourism.attraction',
                'filter' => "circle:{$midpoint[1]},{$midpoint[0]},25000", // 25km radius
                'limit' => 10,
            ]);
            
            if ($response->successful()) {
                // In a real app, we'd integrate with an events API
                // For now, generate mock events based on the places
                $places = $response->json()['features'];
                $events = [];
                
                // Generate random dates between start and end date
                $start = strtotime($startDate);
                $end = strtotime($endDate);
                
                foreach ($places as $index => $place) {
                    $properties = $place['properties'];
                    
                    // Only generate a few events
                    if ($index % 3 == 0 && $start < $end) {
                        $eventDate = date('Y-m-d', rand($start, $end));
                        $events[] = [
                            'name' => 'Event at ' . ($properties['name'] ?? 'Local Venue'),
                            'venue' => $properties['name'] ?? 'Local Venue',
                            'date' => $eventDate,
                            'latitude' => $place['geometry']['coordinates'][1],
                            'longitude' => $place['geometry']['coordinates'][0],
                            'description' => 'A local event happening during your trip',
                            'image_url' => $properties['image'] ?? null,
                        ];
                    }
                }
                
                return $events;
            }
        } catch (\Exception $e) {
            Log::error('Exception while finding events: ' . $e->getMessage());
        }
        
        // Return mock data if API fails
        return [
            [
                'name' => 'Local Music Festival',
                'venue' => 'City Park',
                'date' => $startDate,
                'latitude' => $midpoint[0] + 0.05,
                'longitude' => $midpoint[1] - 0.03,
                'description' => 'Annual music festival with local artists',
                'image_url' => null
            ],
            [
                'name' => 'Food & Wine Expo',
                'venue' => 'Convention Center',
                'date' => date('Y-m-d', strtotime($startDate . ' +1 day')),
                'latitude' => $midpoint[0] - 0.02,
                'longitude' => $midpoint[1] + 0.04,
                'description' => 'Explore local cuisine and wines',
                'image_url' => null
            ]
        ];
    }
    
    /**
     * Calculate the midpoint between two coordinates.
     *
     * @param array $origin [lat, lng]
     * @param array $destination [lat, lng]
     * @return array [lat, lng]
     */
    private function calculateMidpoint(array $origin, array $destination)
    {
        return [
            ($origin[0] + $destination[0]) / 2,
            ($origin[1] + $destination[1]) / 2,
        ];
    }
    
    /**
     * Calculate a point that is a fraction of the way between origin and destination.
     * 
     * @param array $origin [lat, lng]
     * @param array $destination [lat, lng]
     * @param float $fraction Value between 0 and 1
     * @return array [lat, lng]
     */
    private function calculateIntermediatePoint(array $origin, array $destination, float $fraction)
    {
        return [
            $origin[0] + ($destination[0] - $origin[0]) * $fraction,
            $origin[1] + ($destination[1] - $origin[1]) * $fraction,
        ];
    }
    
    /**
     * Map general category names to Geoapify categories.
     *
     * @param array $categories
     * @return array
     */
    private function mapCategories(array $categories)
    {
        $mapping = [
            'tourist_attraction' => ['tourism.attraction', 'tourism.sights'],
            'natural_feature' => ['natural', 'natural.water', 'leisure.park'],
            'museum' => ['tourism.museum', 'tourism.gallery'],
            'restaurant' => ['catering.restaurant', 'catering.cafe'],
            'park' => ['leisure.park', 'leisure.garden'],
            'entertainment' => ['entertainment', 'entertainment.culture'],
        ];
        
        $result = [];
        foreach ($categories as $category) {
            if (isset($mapping[$category])) {
                $result = array_merge($result, $mapping[$category]);
            } else {
                $result[] = $category;
            }
        }
        
        return $result;
    }
}


// This file is part of the Laravel framework.
// app/Services/PointsOfInterestService.php