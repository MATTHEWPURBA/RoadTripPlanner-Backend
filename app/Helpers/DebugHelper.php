<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Symfony\Component\VarDumper\VarDumper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;

class DebugHelper
{
    /**
     * Log a debug message with contextual information
     *
     * @param mixed $data The data to log
     * @param string $context Optional context description
     * @return void
     */
    public static function log($data, $context = 'Debug')
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1] ?? $backtrace[0];
        
        $file = isset($caller['file']) ? basename($caller['file']) : 'unknown';
        $line = $caller['line'] ?? 'unknown';
        $function = $caller['function'] ?? 'unknown';
        $class = $caller['class'] ?? 'unknown';
        
        $logEntry = [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'context' => $context,
            'caller' => "{$class}::{$function} in {$file}:{$line}",
            'data' => self::formatData($data)
        ];
        
        Log::debug('DEBUG: ' . $context, $logEntry);
    }
    
    /**
     * Write debug information to a file in storage/logs/debug
     *
     * @param mixed $data The data to log
     * @param string $filename Optional filename (defaults to date-based)
     * @return void
     */
    public static function toFile($data, $filename = null)
    {
        $directory = storage_path('logs/debug');
        
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        
        if ($filename === null) {
            $filename = Carbon::now()->format('Y-m-d') . '.log';
        }
        
        $path = $directory . '/' . $filename;
        
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1] ?? $backtrace[0];
        
        $output = "-----------------------------------------------------\n";
        $output .= "Time: " . Carbon::now()->toDateTimeString() . "\n";
        $output .= "File: " . ($caller['file'] ?? 'unknown') . "\n";
        $output .= "Line: " . ($caller['line'] ?? 'unknown') . "\n";
        $output .= "Function: " . ($caller['class'] ?? '') . ($caller['type'] ?? '') . ($caller['function'] ?? 'unknown') . "\n";
        $output .= "-----------------------------------------------------\n";
        $output .= self::formatDataForFile($data) . "\n\n";
        
        File::append($path, $output);
    }
    
    /**
     * Format data for log output
     *
     * @param mixed $data
     * @return mixed
     */
    private static function formatData($data)
    {
        if (is_object($data) || is_array($data)) {
            try {
                // Limit nested array/object depth for cleaner logs
                return json_decode(json_encode($data, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR), true);
            } catch (\Exception $e) {
                return "Unserializable data type: " . gettype($data);
            }
        }
        
        return $data;
    }
    
    /**
     * Format data for file output
     *
     * @param mixed $data
     * @return string
     */
    private static function formatDataForFile($data)
    {
        if (is_array($data) || is_object($data)) {
            try {
                return print_r($data, true);
            } catch (\Exception $e) {
                return "Unserializable data type: " . gettype($data);
            }
        }
        
        return var_export($data, true);
    }
    
    /**
     * Dump variables and continue execution
     *
     * @param mixed ...$vars
     * @return void
     */
    public static function dump(...$vars)
    {
        foreach ($vars as $var) {
            VarDumper::dump($var);
        }
    }
    
    /**
     * Dump variables and die
     *
     * @param mixed ...$vars
     * @return void
     */
    public static function dd(...$vars)
    {
        foreach ($vars as $var) {
            VarDumper::dump($var);
        }
        
        die(1);
    }
    
    /**
     * Memory usage information
     *
     * @param bool $realUsage
     * @return string
     */
    public static function memoryUsage($realUsage = false)
    {
        $memory = memory_get_usage($realUsage);
        $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $size = @round($memory / pow(1024, ($i = floor(log($memory, 1024)))), 2) . ' ' . $unit[$i];
        
        return "Memory Usage: {$size}";
    }
    
    /**
     * Execution time since request started
     *
     * @return string
     */
    public static function executionTime()
    {
        if (defined('LARAVEL_START')) {
            $time = microtime(true) - LARAVEL_START;
            return "Execution Time: {$time} seconds";
        }
        
        return "LARAVEL_START not defined";
    }
}



// This file is part of the Laravel framework.
// This file should be saved at: app/Helpers/DebugHelper.php