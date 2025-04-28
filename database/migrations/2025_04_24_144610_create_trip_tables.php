<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('total_distance', 10, 2)->default(0); // in kilometers
            $table->integer('total_duration')->default(0); // in seconds
            $table->decimal('fuel_consumption', 8, 2)->nullable(); // in liters or gallons
            $table->timestamps();
        });

        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('latitude', 10, 7); // Precise coordinates
            $table->decimal('longitude', 10, 7);
            $table->text('address')->nullable();
            $table->integer('order')->default(0); // For ordering destinations
            $table->timestamps();
        });

        Schema::create('route_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('origin_id')->constrained('destinations');
            $table->foreignId('destination_id')->constrained('destinations');
            $table->decimal('distance', 10, 2); // in kilometers
            $table->integer('duration'); // in seconds
            $table->text('polyline')->nullable(); // encoded route path for map display
            $table->timestamps();
        });

        Schema::create('points_of_interest', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_segment_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // Category like "tourist_attraction", "restaurant", etc.
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Order matters for foreign key constraints
        Schema::dropIfExists('points_of_interest');
        Schema::dropIfExists('route_segments');
        Schema::dropIfExists('destinations');
        Schema::dropIfExists('trips');
    }
}