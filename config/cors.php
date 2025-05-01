<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => ['http://localhost:8080'], // Add your frontend URL here
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => true, // Set to true if using cookies/sessions
];


// This file is part of the Laravel framework.
//config/cors.php