<?php

return [
    'driver' => env('DATASET_DRIVER', 'local'),

    'root_path' => env('DATASET_ROOT', ''),

    'storage' => [
        'local' => [
            'root' => env('DATASET_ROOT', ''),
        ],
    ],
];
