<?php

return [
    'public_key' => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),
    'subject' => env('VAPID_SUBJECT', env('APP_URL', 'https://i-projects.net')),
    'ttl' => (int) env('VAPID_TTL', 3600),
];
