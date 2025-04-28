<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\RouteSegment;
use App\Models\PointOfInterest;
use App\Services\PointsOfInterestService;
use Illuminate\Http\Request;

class PointOfInterestController extends Controller
{
    protected $poiService;

    public function __construct(PointsOfInterestService $poiService)
    {
        $this->poiService = $poiService;
    }

    /**
     * Find points of interest along a route segment.
     *
     * @param  int  $segmentId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function findForSegment($segmentId, Request $request)
    {
        $segment = RouteSegment::with(['origin', 'destination'])->findOrFail($segmentId);
        
        $categories = $request->input('categories', ['tourist_attraction', 'natural_feature', 'museum']);
        $radius = $request->input('radius', 5000); // 5km default
        
        // Delete existing POIs for this segment if requested
        if ($request->input('replace', false)) {
            $segment->pointsOfInterest()->delete();
        }
        
        $origin = [$segment->origin->latitude, $segment->origin->longitude];
        $destination = [$segment->destination->latitude, $segment->destination->longitude];
        
        $pois = $this->poiService->findPointsOfInterest($origin, $destination, $categories, $radius);
        
        $savedPois = [];
        foreach ($pois as $poi) {
            $savedPois[] = PointOfInterest::create([
                'route_segment_id' => $segment->id,
                'name' => $poi['name'],
                'type' => $poi['type'],
                'latitude' => $poi['latitude'],
                'longitude' => $poi['longitude'],
                'description' => $poi['description'] ?? null,
                'image_url' => $poi['image_url'] ?? null,
            ]);
        }
        
        return response()->json($savedPois);
    }

    /**
     * Find accommodation options along a route segment.
     *
     * @param  int  $segmentId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function findAccommodation($segmentId, Request $request)
    {
        $segment = RouteSegment::with(['origin', 'destination'])->findOrFail($segmentId);
        
        $maxPrice = $request->input('max_price');
        $minRating = $request->input('min_rating', 3);
        
        $origin = [$segment->origin->latitude, $segment->origin->longitude];
        $destination = [$segment->destination->latitude, $segment->destination->longitude];
        
        // Calculate middle point for long journeys
        $duration = $segment->duration;
        $restPoints = [];
        
        // For journeys over 3 hours, suggest accommodations
        if ($duration > 10800) { // 3 hours in seconds
            $restPoints = $this->poiService->findAccommodation($origin, $destination, $maxPrice, $minRating);
        }
        
        return response()->json($restPoints);
    }
    
    /**
     * Find events along a trip based on dates.
     *
     * @param  int  $tripId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function findEvents($tripId, Request $request)
    {
        $trip = Trip::with('destinations')->findOrFail($tripId);
        
        if ($trip->destinations->count() < 2) {
            return response()->json([
                'error' => 'Trip needs at least two destinations to find events'
            ], 400);
        }
        
        $startDate = $request->input('start_date', $trip->start_date ?? date('Y-m-d'));
        $endDate = $request->input('end_date', $trip->end_date ?? date('Y-m-d', strtotime('+7 days')));
        
        // Get first and last destination
        $origin = [
            $trip->destinations->first()->latitude,
            $trip->destinations->first()->longitude
        ];
        
        $destination = [
            $trip->destinations->last()->latitude,
            $trip->destinations->last()->longitude
        ];
        
        $events = $this->poiService->findEvents($origin, $destination, $startDate, $endDate);
        
        return response()->json($events);
    }
    
    /**
     * Get points of interest for a trip.
     *
     * @param  int  $tripId
     * @return \Illuminate\Http\Response
     */
    public function getByTrip($tripId)
    {
        $trip = Trip::findOrFail($tripId);
        
        $pois = PointOfInterest::whereIn(
            'route_segment_id', 
            $trip->routeSegments()->pluck('id')
        )->get();
        
        return response()->json($pois);
    }
}