<?php

namespace Mpesa\Services;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mpesa\Exceptions\MpesaException;
use Mpesa\Traits\HasMpesaResponses;
use Mpesa\Services\AuthenticationService;

/**
 * Class STKPushService
 * 
 * Handles M-Pesa STK Push (Express Payment) functionality
 * This service enables merchants to initiate online payments on behalf of customers
 * 
 * @package Mpesa\Services
 */
class STKPushService
{
    use HasMpesaResponses;

    /**
     * @var Client HTTP client instance
     */
    protected $client;

    /**
     * @var array M-Pesa configuration
     */
    protected $config;

    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * Initialize the STK Push service
     */
    public function __construct()
    {
        $this->client = new Client([
            'verify' => false,
            'timeout' => 60,
        ]);
        $this->config = Config::get('mpesa');

        $this->authService = new AuthenticationService($this->config);
    }

    /**
     * Initiate an STK Push request to the customer's phone
     * 
     * @param float $amount Amount to be paid
     * @param string $phoneNumber Customer's phone number
     * @param string $accountReference Merchant provided reference
     * @param string $transactionDesc Description of the transaction
     * @param string|null $callbackUrl Optional URL to receive transaction result
     * @return array Response from M-Pesa
     * @throws MpesaException
     */
    public function initiateSTKPush(
        float $amount,
        string $phoneNumber,
        string $accountReference,
        string $transactionDesc,
        string $callbackUrl = null
    ) {
        $timestamp = Carbon::now()->format('YmdHis');
        $shortcode = $this->config['shortcode'];
        $passkey = $this->config['passkey'];
        
        // Generate password (shortcode + passkey + timestamp)
        $password = base64_encode($shortcode . $passkey . $timestamp);

        // Prepare the request payload
        $data = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) $amount,
            'PartyA' => $this->formatPhoneNumber($phoneNumber),
            'PartyB' => $shortcode,
            'PhoneNumber' => $this->formatPhoneNumber($phoneNumber),
            'CallBackURL' => $callbackUrl ?? $this->config['stk_callback_url'],
            'AccountReference' => substr($accountReference, 0, 12), // Max 12 chars
            'TransactionDesc' => substr($transactionDesc, 0, 13), // Max 13 chars
        ];

        try {
            // Make the API request
            $response = $this->client->post($this->config['base_url'] . '/mpesa/stkpush/v1/processrequest', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('STK Push Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Format the phone number to the required format (254XXXXXXXXX)
     * 
     * @param string $phone Phone number to format
     * @return string Formatted phone number
     */
    protected function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 9) {
            $phone = '254' . $phone;
        } elseif (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        } elseif (strlen($phone) === 12 && substr($phone, 0, 3) === '254') {
            return $phone;
        }
        
        return $phone;
    }

    /**
     * Get the OAuth access token for API authentication
     * 
     * @return string Access token
     * @throws MpesaException
     */
    protected function getAccessToken()
    {
        // Try to get token from cache first
        $token = Cache::get('mpesa_access_token');
        
        if ($token) {
            return $token;
        }

        $token = $this->generateToken();
        
        // Cache the token for slightly less than the expiry time (3599 seconds)
        Cache::put('mpesa_access_token', $token, 3500);
        
        return $token;
    }

    /**
     * Generate new access token from M-Pesa API
     * 
     * @return string Access token
     * @throws MpesaException
     */
    public function generateToken(): string
    {
        try {
            $response = $this->authService->getAccessToken();
            return $response->access_token;
        } catch (MpesaException $e) {
            throw $e;
        }
    }
}
