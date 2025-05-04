<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Destination;
use App\Models\RouteSegment;
use App\Services\RouteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class TripController extends Controller
{
    protected $routeService;

    public function __construct(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    /**
     * Get all trips.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $trips = Trip::all();
            return response()->json($trips);
        } catch (\Exception $e) {
            // Log the actual error for debugging
            Log::error('Trip index error: ' . $e->getMessage());
            return response()->json(['error' => 'Database error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created trip in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $trip = Trip::create($request->all());

        return response()->json($trip, 201);
    }

    /**
     * Display the specified trip with all related data.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $trip = Trip::with([
            'destinations', 
            'routeSegments.origin', 
            'routeSegments.destination', 
            'routeSegments.pointsOfInterest'
        ])->findOrFail($id);
        
        return response()->json($trip);
    }

    /**
     * Update the specified trip in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $trip->update($request->all());

        return response()->json($trip);
    }

    /**
     * Remove the specified trip from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $trip = Trip::findOrFail($id);
        $trip->delete();

        return response()->json(null, 204);
    }

    /**
     * Recalculate the route for the entire trip.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function calculateRoute($id)
{
    try {
        $trip = Trip::with('destinations')->findOrFail($id);
        
        // Check if we have enough destinations
        if ($trip->destinations->count() < 2) {
            return response()->json([
                'error' => 'At least two destinations are required to calculate a route',
                'destinations_count' => $trip->destinations->count()
            ], 400);
        }
        
        // Clear existing route segments
        $trip->routeSegments()->delete();
        
        $totalDistance = 0;
        $totalDuration = 0;
        $routeSegments = [];
        
        // Calculate routes between consecutive destinations
        for ($i = 0; $i < $trip->destinations->count() - 1; $i++) {
            $origin = $trip->destinations[$i];
            $destination = $trip->destinations[$i + 1];
            
            $routeData = $this->routeService->getRouteData(
                [$origin->latitude, $origin->longitude],
                [$destination->latitude, $destination->longitude]
            );
            
            if ($routeData) {
                // Ensure data types match database schema
                $segmentData = [
                    'trip_id' => $trip->id,
                    'origin_id' => $origin->id,
                    'destination_id' => $destination->id,
                    'distance' => (float)$routeData['distance'], // Ensure float
                    'duration' => (int)$routeData['duration'], // Ensure integer
                    'polyline' => $routeData['polyline'] ?? null,
                ];
                
                $segment = RouteSegment::create($segmentData);
                
                $routeSegments[] = $segment;
                $totalDistance += $routeData['distance'];
                $totalDuration += $routeData['duration'];
            }
        }
        
        // Update trip totals (also ensure proper types)
        $fuelConsumption = $this->routeService->calculateFuelConsumption($totalDistance);
        $trip->update([
            'total_distance' => (float)$totalDistance,
            'total_duration' => (int)$totalDuration,
            'fuel_consumption' => (float)$fuelConsumption,
        ]);
        
        $trip->load('routeSegments');
        return response()->json([
            'trip' => $trip,
            'calculated_segments' => count($routeSegments),
            'route_summary' => [
                'distance' => $totalDistance,
                'duration' => $totalDuration,
                'fuel' => $fuelConsumption
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('Route calculation failed', [
            'trip_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => 'Failed to calculate route',
            'message' => $e->getMessage()
        ], 500);
    }
}



}

// This file is part of the Laravel framework.
// app/Http/Controllers/TripController.php