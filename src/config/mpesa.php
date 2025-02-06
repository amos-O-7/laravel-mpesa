<?php

return [
    'base_url' => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    'shortcode' => env('MPESA_SHORTCODE'),
    'callback_url' => env('MPESA_CALLBACK_URL'),
    
    // C2B URLs
    'validation_url' => env('MPESA_VALIDATION_URL'),
    'confirmation_url' => env('MPESA_CONFIRMATION_URL'),
    'response_type' => env('MPESA_RESPONSE_TYPE', 'Completed'),
];
