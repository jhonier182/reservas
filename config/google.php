<?php
return [
    'credentials_path'        => env('GOOGLE_CREDENTIALS_PATH'), // opcional (Service Account). Déjalo vacío si usas solo OAuth
    'redirect_uri'            => env('GOOGLE_REDIRECT_URI'),
    'scopes'                  => [
        'https://www.googleapis.com/auth/calendar.events',
        // 'https://www.googleapis.com/auth/calendar', // si quieres crear Meet
    ],
    'access_type'             => 'offline',
    'prompt'                  => 'consent',
    'include_granted_scopes'  => true,
    'impersonate_subject'     => env('GOOGLE_IMPERSONATE_SUBJECT'), // opcional

    'calendar_id'             => env('GOOGLE_CALENDAR_ID', 'primary'),
    'timezone'                => env('GOOGLE_TIMEZONE', 'America/Bogota'),
];

