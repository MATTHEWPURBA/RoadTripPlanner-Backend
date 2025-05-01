<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roadtrip:seed {--refresh : Whether to refresh the database tables before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Road Trip Planner with demo data for testing';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
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

        return 0;
    }
}

// This file is part of the Laravel framework.
// app/Console/Commands/SeedDemoData.php