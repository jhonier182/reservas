<?php

return [
    'credentials_path' => env('GOOGLE_CREDENTIALS_PATH', storage_path('app/google/credentials.json')),
    'redirect_uri' => env('GOOGLE_REDIRECT_URI', 'http://127.0.0.1:8000/google/callback'),
    'scopes' => [
        'https://www.googleapis.com/auth/calendar',
        'https://www.googleapis.com/auth/calendar.events',
        'https://www.googleapis.com/auth/calendar.readonly'
    ],
    'impersonate_subject' => env('GOOGLE_IMPERSONATE_SUBJECT', null),
    'access_type' => 'offline',
    'prompt' => 'consent',
    'include_granted_scopes' => true,
];
