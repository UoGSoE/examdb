<?php

return [

    'connections' => [
        'long-running' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'long-running-queue',
            'retry_after' => 24000,
        ],
    ],

];
