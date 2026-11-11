<?php

return [
    // none = tanda tangan visual (QR) saja, tanpa API. ssn = pakai API SSN.
    'driver' => env('TTE_DRIVER', 'none'),

    'ssn' => [
        'base_url' => env('TTE_SSN_URL'),
        'username' => env('TTE_SSN_USER'),
        'password' => env('TTE_SSN_PASS'),
        'timeout'  => 30,
    ],
];