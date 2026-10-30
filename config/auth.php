<?php

use App\Models\ClientPortalAccount;
use App\Models\User;

return [
    'defaults' => ['guard' => 'web', 'passwords' => 'users'],
    'guards' => [
        'web' => ['driver' => 'session', 'provider' => 'users'],
        'api' => ['driver' => 'token', 'provider' => 'users', 'hash' => false],
        'client' => [
            'driver' => 'session',
            'provider' => 'clients_portal',
        ],
    ],
    'providers' => [
        'users' => ['driver' => 'eloquent', 'model' => User::class],
        'clients_portal' => [
            'driver' => 'eloquent',
            'model' => ClientPortalAccount::class,
        ],
    ],
    'passwords' => [
        'users' => ['provider' => 'users', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 60],
    ],
    'password_timeout' => 10800,
];
