<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Server Key
    |--------------------------------------------------------------------------
    |
    | Your Midtrans server key for API authentication.
    |
    */
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Client Key
    |--------------------------------------------------------------------------
    |
    | Your Midtrans client key for Snap.js integration.
    |
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Production Mode
    |--------------------------------------------------------------------------
    |
    | Set to true for production environment, false for sandbox.
    |
    */
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Sanitize Input
    |--------------------------------------------------------------------------
    |
    | Enable input sanitization for security.
    |
    */
    'is_sanitized' => true,

    /*
    |--------------------------------------------------------------------------
    | 3DS Authentication
    |--------------------------------------------------------------------------
    |
    | Enable 3D Secure authentication for credit card transactions.
    |
    */
    'is_3ds' => true,

    /*
    |--------------------------------------------------------------------------
    | Payment Deadline
    |--------------------------------------------------------------------------
    |
    | Default payment deadline in minutes.
    |
    */
    'payment_deadline_minutes' => env('MIDTRANS_PAYMENT_DEADLINE', 60),

    /*
    |--------------------------------------------------------------------------
    | Snap URL
    |--------------------------------------------------------------------------
    |
    | Snap.js URL based on environment.
    |
    */
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',
];
