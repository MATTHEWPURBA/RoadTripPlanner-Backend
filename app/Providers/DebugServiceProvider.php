<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DebugServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register a global 'debug' function for easy access to DebugHelper
        $this->app->singleton('debug', function () {
            return new \App\Helpers\DebugHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register debug helper functions
        if (!function_exists('debug_log')) {
            function debug_log($data, $context = 'Debug') {
                \App\Helpers\DebugHelper::log($data, $context);
            }
        }
        
        if (!function_exists('debug_to_file')) {
            function debug_to_file($data, $filename = null) {
                \App\Helpers\DebugHelper::toFile($data, $filename);
            }
        }
        
        if (!function_exists('debug_memory')) {
            function debug_memory($realUsage = false) {
                return \App\Helpers\DebugHelper::memoryUsage($realUsage);
            }
        }
        
        if (!function_exists('debug_time')) {
            function debug_time() {
                return \App\Helpers\DebugHelper::executionTime();
            }
        }
    }
}


// This file is part of the Laravel framework.
// This file should be saved at: app/Providers/DebugServiceProvider.php