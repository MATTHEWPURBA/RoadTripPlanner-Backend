<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TripController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\PointOfInterestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Wrap all API routes in a group with common middleware
Route::group(['middleware' => ['cors']], function () {
    // Trip routes
    Route::get('/trips', [TripController::class, 'index']);
    Route::post('/trips', [TripController::class, 'store']);
    Route::get('/trips/{id}', [TripController::class, 'show']);
    Route::put('/trips/{id}', [TripController::class, 'update']);
    Route::delete('/trips/{id}', [TripController::class, 'destroy']);
    Route::post('/trips/{id}/calculate-route', [TripController::class, 'calculateRoute']);

    // Destination routes
    Route::post('/destinations', [DestinationController::class, 'store']);
    Route::put('/destinations/{id}', [DestinationController::class, 'update']);
    Route::delete('/destinations/{id}', [DestinationController::class, 'destroy']);
    Route::get('/trips/{tripId}/destinations', [DestinationController::class, 'getByTrip']);
    Route::post('/trips/{tripId}/destinations/reorder', [DestinationController::class, 'reorder']);
    Route::post('/destinations/geocode', [DestinationController::class, 'geocode']);

    // Points of Interest routes
    Route::get('/route-segments/{segmentId}/points-of-interest', [PointOfInterestController::class, 'findForSegment']);
    Route::get('/route-segments/{segmentId}/accommodation', [PointOfInterestController::class, 'findAccommodation']);
    Route::get('/trips/{tripId}/points-of-interest', [PointOfInterestController::class, 'getByTrip']);
    Route::get('/trips/{tripId}/events', [PointOfInterestController::class, 'findEvents']);
});



// This file is part of the Laravel framework.
// routes/api.php