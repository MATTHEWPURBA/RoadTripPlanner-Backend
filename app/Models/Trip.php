<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    // Mass assignable attributes
    protected $fillable = [
        'name', 
        'description', 
        'start_date', 
        'end_date', 
        'total_distance',
        'total_duration',
        'fuel_consumption'
    ];

    // Auto-casting attributes to appropriate types
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_distance' => 'float',
        'total_duration' => 'integer',
        'fuel_consumption' => 'float',
    ];

    /**
     * Get the destinations for the trip.
     * Ordered by the 'order' field to maintain destination sequence.
     */
    public function destinations()
    {
        return $this->hasMany(Destination::class)->orderBy('order');
    }

    /**
     * Get the route segments for the trip.
     */
    public function routeSegments()
    {
        return $this->hasMany(RouteSegment::class);
    }
    
    /**
     * Get all points of interest for this trip by combining from all segments.
     */
    public function getAllPointsOfInterest()
    {
        return PointOfInterest::whereIn('route_segment_id', $this->routeSegments->pluck('id'));
    }
}

// This file is part of the Laravel framework.
// app/Models/Trip.php
