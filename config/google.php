<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Google Calendar Configuration
    |--------------------------------------------------------------------------
    */
    
    'calendar' => [
        'credentials_path' => env('GOOGLE_CALENDAR_CREDENTIALS_PATH', storage_path('app/google/credentials.json')),
        'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),
        'application_name' => env('GOOGLE_APPLICATION_NAME', 'TVS Performance Evaluations'),
        'time_zone' => env('GOOGLE_CALENDAR_TIMEZONE', 'America/Bogota'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | OAuth 2.0 Configuration
    |--------------------------------------------------------------------------
    */
    
    'oauth' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
        'scopes' => [
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.events'
        ]
    ]
];
