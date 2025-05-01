<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        // Event => Listeners mappings go here
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}

// This file is part of the Laravel framework.
// app/Providers/EventServiceProvider.php