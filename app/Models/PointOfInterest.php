<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointOfInterest extends Model
{
    use HasFactory;

    // Explicitly define the table name to match your migration
    protected $table = 'points_of_interest';


    protected $fillable = [
        'route_segment_id',
        'name',
        'type',
        'latitude',
        'longitude',
        'description',
        'image_url'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Get the route segment that owns the point of interest.
     */
    public function routeSegment()
    {
        return $this->belongsTo(RouteSegment::class);
    }
    
    /**
     * Get category-specific icon class
     */
    public function getIconClassAttribute()
    {
        // Map POI types to Font Awesome or other icon classes
        $iconMap = [
            'tourist_attraction' => 'fa-landmark',
            'natural_feature' => 'fa-mountain',
            'museum' => 'fa-museum',
            'restaurant' => 'fa-utensils',
            'park' => 'fa-tree',
            'hotel' => 'fa-bed',
            'entertainment' => 'fa-theater-masks',
            'default' => 'fa-map-marker-alt'
        ];
        
        $typeParts = explode(',', $this->type);
        $primaryType = trim($typeParts[0]);
        
        return 'fas ' . ($iconMap[$primaryType] ?? $iconMap['default']);
    }
}