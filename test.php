<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

class MpesaTest
{
    protected $client;
    protected $config;

    public function __construct()
    {
        $this->config = [
            'base_url' => 'https://sandbox.safaricom.co.ke',
            'consumer_key' => 'VXOTcbLJC0dOeKPSpH2uwhj5BYIN007M9YujSLiEc45WyToZ',
            'consumer_secret' => 'hwnvt0l1etIGFKGMUkHR2NQb6ewHy0O3tsGuX13wqzNABpuyR3MLvzyFeA9PfjVT'
        ];

        $this->client = new Client([
            'base_uri' => $this->config['base_url'],
            'timeout' => 30,
            'verify' => false
        ]);
    }

    public function getToken()
    {
        try {
            // Get credentials from environment
            $key = $this->config['consumer_key'];
            $secret = $this->config['consumer_secret'];

            if (!$key || !$secret) {
                throw new \Exception("Missing consumer key or secret. Please check your .env file");
            }

            // Create Basic Auth credentials
            $credentials = base64_encode($key . ':' . $secret);

            echo "Using credentials: " . $credentials . "\n";

            // Make request exactly as documented
            $response = $this->client->get('/oauth/v1/generate', [
                'headers' => [
                    'Authorization' => 'Basic ' . $credentials
                ],
                'query' => [
                    'grant_type' => 'client_credentials'
                ],
                'debug' => true
            ]);

            $result = json_decode($response->getBody()->getContents());
            return $result;
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'credentials_used' => $credentials ?? null
            ];
        }
    }
}

// Run the test
$mpesa = new MpesaTest();
$result = $mpesa->getToken();
echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
