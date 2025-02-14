<?php

return [

    'guards' => [
        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    'providers' => [
        'ldapusers' => [
            'driver' => 'ldapeloquent',
            'model' => App\Models\User::class,
        ],
    ],

];
