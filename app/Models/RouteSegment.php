<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'origin_id',
        'destination_id',
        'distance',
        'duration',
        'polyline'
    ];

    protected $casts = [
        'distance' => 'float',
        'duration' => 'integer',
    ];

    /**
     * Get the trip that owns the route segment.
     */
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Get the origin destination.
     */
    public function origin()
    {
        return $this->belongsTo(Destination::class, 'origin_id');
    }

    /**
     * Get the destination.
     */
    public function destination()
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }

    /**
     * Get the points of interest for this route segment.
     */
    public function pointsOfInterest()
    {
        return $this->hasMany(PointOfInterest::class);
    }
    
    /**
     * Format duration to human-readable string
     * E.g., "2 hours 30 minutes"
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        
        $formatted = '';
        if ($hours > 0) {
            $formatted .= $hours . ' hour' . ($hours > 1 ? 's' : '');
        }
        
        if ($minutes > 0) {
            if ($formatted) {
                $formatted .= ' ';
            }
            $formatted .= $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        }
        
        return $formatted ?: '0 minutes';
    }
    
    /**
     * Format distance to readable format with km unit
     */
    public function getFormattedDistanceAttribute()
    {
        return number_format($this->distance, 1) . ' km';
    }
}