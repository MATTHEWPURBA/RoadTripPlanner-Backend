<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Register our custom seed command
Artisan::command('roadtrip:seed {--refresh : Whether to refresh the database tables before seeding}', function () {
    if ($this->option('refresh')) {
        $this->info('Refreshing database tables...');
        Artisan::call('migrate:refresh');
        $this->info('Database tables refreshed successfully!');
    }

    $this->info('Seeding database with demo data...');
    Artisan::call('db:seed');
    $this->info('Demo data seeded successfully!');

    $this->info('Road Trip Planner demo data is now ready for testing!');
    $this->info('You can now test the API endpoints with tools like Postman or Insomnia.');
})->purpose('Seed Road Trip Planner with demo data for testing');



// This file is part of the Laravel framework.
// routes/console.php