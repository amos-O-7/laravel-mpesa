<?php

return [
    /*
    |--------------------------------------------------------------------------
    | M-Pesa Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials and configuration for the Safaricom M-Pesa API
    |
    */

    'base_url' => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),
    
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    
    'shortcode' => env('MPESA_SHORTCODE'),
    
    'validation_url' => env('MPESA_VALIDATION_URL'),
    
    'confirmation_url' => env('MPESA_CONFIRMATION_URL'),
    
    'timeout' => env('MPESA_TIMEOUT', 30),
    
    'verify_ssl' => env('MPESA_VERIFY_SSL', false),
];
