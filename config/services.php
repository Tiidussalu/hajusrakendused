<?php

return [

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // 1. Ilmateenistus - OpenWeatherMap
    'openweather' => [
        'key' => env('OPENWEATHER_API_KEY'),
    ],

    // 2. Kaart - Radar Maps
    'radar' => [
        'key' => env('RADAR_MAPS_API_KEY'),
    ],

    // 4. E-pood - Stripe
    'stripe' => [
        'key'            => env('STRIPE_KEY'),
        'secret'         => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
