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

    public function registerC2BUrls()
    {
        try {
            $token = $this->getToken();
            $access_token = $token->access_token;

            $response = $this->client->post('/mpesa/c2b/v1/registerurl', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'ShortCode' => '600987',  // Use your shortcode here
                    'ResponseType' => 'Completed',
                    'ConfirmationURL' => 'https://example.com/confirmation',
                    'ValidationURL' => 'https://example.com/validation'
                ]
            ]);

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function simulateC2BPayment()
    {
        try {
            $token = $this->getToken();
            $access_token = $token->access_token;

            $response = $this->client->post('/mpesa/c2b/v1/simulate', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access_token,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'ShortCode' => '600987',  
                    'CommandID' => 'CustomerPayBillOnline',
                    'Amount' => '100',
                    'Msisdn' => '254727343690',  
                    'BillRefNumber' => 'TEST123'
                ]
            ]);

            return json_decode($response->getBody());
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

// Run the tests
$mpesa = new MpesaTest();

echo "Getting Token:\n";
$token = $mpesa->getToken();
echo json_encode($token, JSON_PRETTY_PRINT) . "\n\n";

echo "Registering C2B URLs:\n";
$urls = $mpesa->registerC2BUrls();
echo json_encode($urls, JSON_PRETTY_PRINT) . "\n\n";

echo "Simulating C2B Payment:\n";
$payment = $mpesa->simulateC2BPayment();
echo json_encode($payment, JSON_PRETTY_PRINT) . "\n";
