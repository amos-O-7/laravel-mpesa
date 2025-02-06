<?php

namespace Mpesa\Support;

use Illuminate\Support\Facades\Config;
use Mpesa\Exceptions\MpesaException;

class ConfigurationValidator
{
    /**
     * Required configuration keys
     *
     * @var array
     */
    protected static $required = [
        'base_url',
        'consumer_key',
        'consumer_secret',
        'shortcode',
        'validation_url',
        'confirmation_url',
        'response_type'
    ];

    /**
     * Validate M-Pesa configuration
     *
     * @throws MpesaException
     */
    public static function validate(): void
    {
        $config = Config::get('mpesa');

        if (!$config) {
            throw new MpesaException('M-Pesa configuration not found. Please publish the config file.');
        }

        foreach (self::$required as $key) {
            if (!isset($config[$key]) || empty($config[$key])) {
                throw new MpesaException("Required configuration key 'mpesa.{$key}' is missing or empty.");
            }
        }

        if (!in_array($config['response_type'], ['Completed', 'Cancelled'])) {
            throw new MpesaException("Response type must be either 'Completed' or 'Cancelled'.");
        }

        $urls = ['validation_url', 'confirmation_url'];
        foreach ($urls as $url) {
            if (!filter_var($config[$url], FILTER_VALIDATE_URL)) {
                throw new MpesaException("Invalid URL format for 'mpesa.{$url}'.");
            }
        }
    }
}
