<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| Allows the React Native mobile app and ESP32 to reach the Laravel API
| from a different origin (different IP / port).
|
| For production, replace '*' with your actual domain or IP.
|
*/

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],
    // For production use specific origins, e.g.:
    // 'allowed_origins' => ['http://192.168.1.10:8000', 'exp://192.168.1.5:8081'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    /*
    | Credentials (cookies/session) — set to true only if using
    | Sanctum cookie-based auth for SPA. For mobile Bearer token, false is fine.
    */
    'supports_credentials' => false,

];