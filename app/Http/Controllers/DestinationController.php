<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class DestinationController extends Controller
{
    /**
     * Store a newly created destination in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|exists:trips,id',
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // If order is not provided, place at the end
        if (!$request->has('order')) {
            $maxOrder = Destination::where('trip_id', $request->trip_id)
                ->max('order');
            $request->merge(['order' => ($maxOrder !== null) ? $maxOrder + 1 : 0]);
        }

        $destination = Destination::create($request->all());

        return response()->json($destination, 201);
    }

    /**
     * Update the specified destination in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $destination = Destination::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'address' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $destination->update($request->all());

        return response()->json($destination);
    }

    /**
     * Remove the specified destination from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $destination = Destination::findOrFail($id);
        $tripId = $destination->trip_id;
        $destinationOrder = $destination->order;
        
        // Delete the destination
        $destination->delete();
        
        // Reorder remaining destinations
        Destination::where('trip_id', $tripId)
            ->where('order', '>', $destinationOrder)
            ->decrement('order');
        
        return response()->json(null, 204);
    }

    /**
     * Reorder destinations for a trip.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $tripId
     * @return \Illuminate\Http\Response
     */
    public function reorder(Request $request, $tripId)
    {
        $validator = Validator::make($request->all(), [
            'destinations' => 'required|array',
            'destinations.*.id' => 'required|exists:destinations,id',
            'destinations.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update the order of each destination
        foreach ($request->destinations as $item) {
            Destination::where('id', $item['id'])
                ->where('trip_id', $tripId)
                ->update(['order' => $item['order']]);
        }

        $destinations = Destination::where('trip_id', $tripId)
            ->orderBy('order')
            ->get();

        return response()->json($destinations);
    }
    
    /**
     * Get destinations for a specific trip.
     *
     * @param  int  $tripId
     * @return \Illuminate\Http\Response
     */
    public function getByTrip($tripId)
    {
        $destinations = Destination::where('trip_id', $tripId)
            ->orderBy('order')
            ->get();
            
        return response()->json($destinations);
    }
    
    /**
     * Geocode an address to get coordinates.
     * Uses Geoapify Geocoding API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function geocode(Request $request)
    {
        Log::info('Geocode endpoint called', ['request_data' => $request->all()]);
        
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::warning('Geocode validation failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $address = urlencode($request->address);
        $apiKey = env('GEOAPIFY_API_KEY', '');


        if (empty($apiKey)) {
            Log::error('Geocoding API key is missing');
            return response()->json(['error' => 'Geocoding service misconfigured'], 500);
        }
        
        Log::info('Making request to Geoapify', [
            'address' => $request->address, 
            'encoded_address' => $address
        ]);
        
        try {
            $url = "https://api.geoapify.com/v1/geocode/search?text={$address}&apiKey={$apiKey}";
            Log::debug('Geocoding URL (without API key)', [
                'base_url' => "https://api.geoapify.com/v1/geocode/search?text={$address}&apiKey=API_KEY_HIDDEN"
            ]);
            
            $response = Http::get($url);
            
            Log::info('Geoapify response received', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'has_features' => isset($response->json()['features'])
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data['features'])) {
                    $location = $data['features'][0];
                    $coords = $location['geometry']['coordinates'];
                    $properties = $location['properties'];
                    
                    $result = [
                        'latitude' => $coords[1],
                        'longitude' => $coords[0],
                        'formatted_address' => $properties['formatted'],
                        'name' => $properties['name'] ?? $properties['formatted'],
                    ];
                    
                    Log::info('Geocoding successful', ['result' => $result]);
                    return response()->json($result);
                } else {
                    Log::warning('No geocoding results found', ['data' => $data]);
                }
            } else {
                Log::error('Geocoding service error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
            
            return response()->json(['error' => 'Location not found'], 404);
            
        } catch (\Exception $e) {
            Log::error('Exception during geocoding', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Geocoding service unavailable: ' . $e->getMessage()], 503);
        }
    }
}



// This file is part of the Laravel framework.
// app/Http/Controllers/DestinationController.php