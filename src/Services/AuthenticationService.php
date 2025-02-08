<?php

namespace Mpesa\Services;

use GuzzleHttp\Client;
use Mpesa\Exceptions\MpesaException;

class AuthenticationService
{
    protected Client $client;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => $config['base_url'] ?? 'https://sandbox.safaricom.co.ke',
            'timeout' => 30,
            'verify' => false
        ]);
    }

    /**
     * Get an OAuth access token from M-Pesa
     *
     * @return object
     * @throws MpesaException
     */
    public function getAccessToken(): object
    {
        try {
            // Get credentials from config
            $key = $this->config['consumer_key'] ?? null;
            $secret = $this->config['consumer_secret'] ?? null;

            if (!$key || !$secret) {
                throw new MpesaException("Missing consumer key or secret in configuration");
            }

            // Create Basic Auth credentials
            $credentials = base64_encode($key . ':' . $secret);

            // Make request to get token
            $response = $this->client->get('/oauth/v1/generate', [
                'headers' => [
                    'Authorization' => 'Basic ' . $credentials
                ],
                'query' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            throw new MpesaException(
                "Failed to get access token: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
