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
            'consumer_key' => '31rGZ4SymgLEH7O8T0waqW4jiYACF9LGkIYjZIbxV9YfyECZ',
            'consumer_secret' => 'Tlx65J6TjRWJWiMkt98VJsquH4obqPz0rFeJDrFjoPNZy5Zd9YiyTcSKKOAlv5AJ',
            'shortcode' => '174379',
            'passkey' => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
            'callback_url' => 'https://example.com/callback'
        ];

        $this->client = new Client([
            'base_uri' => $this->config['base_url'],
            'verify' => false,
            'timeout' => 60,
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

    /**
     * Simulates a C2B (Customer to Business) payment transaction in the M-Pesa sandbox environment.
     *
     * This method sends a request to the M-Pesa API to simulate a payment transaction,
     * using predefined values for ShortCode, CommandID, Amount, Msisdn, and BillRefNumber.
     * It requires a valid access token, which is retrieved using the getToken() method.
     *
     * @return mixed Returns the response from the M-Pesa API if successful, or an error message
     *               if the request fails.
     */
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

    public function testSTKPush()
    {
        try {
            // First get the access token
            $token = $this->getToken();
            
            // Generate password
            $timestamp = date('YmdHis');
            $password = base64_encode($this->config['shortcode'] . $this->config['passkey'] . $timestamp);

            // Prepare STK Push request data
            $data = [
                'BusinessShortCode' => $this->config['shortcode'],
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => 1,
                'PartyA' => '254727343690', // Replace with your phone number
                'PartyB' => $this->config['shortcode'],
                'PhoneNumber' => '254727343690', // Replace with your phone number
                'CallBackURL' => $this->config['callback_url'],
                'AccountReference' => 'Test Account',
                'TransactionDesc' => 'Test Payment'
            ];

            // Make the STK Push request
            $response = $this->client->post('/mpesa/stkpush/v1/processrequest', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->access_token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            echo "STK Push Response:\n";
            print_r($result);
            return $result;

        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            if (method_exists($e, 'getResponse')) {
                $response = $e->getResponse();
                if ($response) {
                    echo "Response Body: " . $response->getBody()->getContents() . "\n";
                }
            }
            return null;
        }
    }
}

// Run the tests
$mpesa = new MpesaTest();

echo "Getting Token:\n";
$token = $mpesa->getToken();
print_r($token);

echo "\nTesting STK Push:\n";
$mpesa->testSTKPush();
