<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'name',
        'latitude',
        'longitude',
        'address',
        'order'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'order' => 'integer',
    ];

    /**
     * Get the trip that owns the destination.
     */
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Get the route segments where this destination is the origin.
     */
    public function outgoingRoutes()
    {
        return $this->hasMany(RouteSegment::class, 'origin_id');
    }

    /**
     * Get the route segments where this destination is the destination.
     */
    public function incomingRoutes()
    {
        return $this->hasMany(RouteSegment::class, 'destination_id');
    }
    
    /**
     * Get the next destination in sequence (if any).
     */
    public function getNextDestination()
    {
        return Destination::where('trip_id', $this->trip_id)
            ->where('order', $this->order + 1)
            ->first();
    }
    
    /**
     * Get the previous destination in sequence (if any).
     */
    public function getPreviousDestination()
    {
        return Destination::where('trip_id', $this->trip_id)
            ->where('order', $this->order - 1)
            ->first();
    }
}


// This file is part of the Laravel framework.
// app/Models/Destination.php