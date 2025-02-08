<?php

namespace Mpesa\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mpesa\DataTransferObjects\C2BTransactionData;
use Mpesa\Exceptions\MpesaException;
use Mpesa\Traits\HasMpesaResponses;
use Mpesa\Services\AuthenticationService;

class C2BService
{
    use HasMpesaResponses;

    protected $client;
    protected $config;
    protected $authService;

    public function __construct(Client $client = null)
    {
        $this->config = Config::get('mpesa');
        $this->client = $client ?? new Client([
            'base_uri' => $this->config['base_url'],
            'timeout' => 30,
            'verify' => false
        ]);
        $this->authService = new AuthenticationService($this->config);
    }

    /**
     * Generate new access token
     *
     * @return string
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

    /**
     * Get access token from cache or generate new one
     *
     * @return string
     * @throws MpesaException
     */
    protected function getAccessToken(): string
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
     * Register C2B URLs (Validation and Confirmation)
     *
     * @param string $validationUrl
     * @param string $confirmationUrl
     * @param string $responseType Either 'Completed' or 'Cancelled'
     * @return array
     * @throws MpesaException
     */
    public function registerUrls(string $validationUrl, string $confirmationUrl, string $responseType = 'Completed'): array
    {
        try {
            if (!in_array($responseType, ['Completed', 'Cancelled'])) {
                throw new MpesaException('Response type must be either Completed or Cancelled');
            }

            $response = $this->client->post('/mpesa/c2b/v1/registerurl', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'ShortCode' => $this->config['shortcode'],
                    'ResponseType' => $responseType,
                    'ConfirmationURL' => $confirmationUrl,
                    'ValidationURL' => $validationUrl,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            Log::info('M-Pesa C2B URL Registration', ['result' => $result]);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('M-Pesa C2B URL Registration Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new MpesaException('Failed to register URLs: ' . $e->getMessage());
        }
    }

    /**
     * Validate C2B transaction
     *
     * @param array $request
     * @return array
     */
    public function validateTransaction(array $request): array
    {
        try {
            Log::info('M-Pesa C2B Validation Request', ['data' => $request]);

            $transaction = C2BTransactionData::fromMpesaResponse($request);

            // Validate shortcode
            if ($transaction->businessShortCode !== $this->config['shortcode']) {
                return $this->errorResponse(
                    $this->getErrorCodes()['INVALID_SHORTCODE'],
                    'Invalid business shortcode'
                );
            }

            // Validate amount (example: minimum amount check)
            if ($transaction->amount <= 0) {
                return $this->errorResponse(
                    $this->getErrorCodes()['INVALID_AMOUNT'],
                    'Invalid transaction amount'
                );
            }

            // Add your custom validation logic here
            // For example, check if the account number exists in your system
            
            return $this->successResponse('Accepted');
        } catch (\Exception $e) {
            Log::error('M-Pesa C2B Validation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse(
                $this->getErrorCodes()['OTHER_ERROR'],
                'Validation failed'
            );
        }
    }

    /**
     * Confirm C2B transaction
     *
     * @param array $request
     * @return array
     */
    public function confirmTransaction(array $request): array
    {
        try {
            Log::info('M-Pesa C2B Confirmation Request', ['data' => $request]);

            $transaction = C2BTransactionData::fromMpesaResponse($request);

            // Process the confirmation
            // Here you would typically:
            // 1. Save the transaction to your database
            // 2. Update the user's account balance
            // 3. Send notifications if needed
            // 4. Trigger any other business logic

            return $this->successResponse('Success');
        } catch (\Exception $e) {
            Log::error('M-Pesa C2B Confirmation Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'transaction_data' => $request
            ]);
            return $this->errorResponse(
                $this->getErrorCodes()['OTHER_ERROR'],
                'Confirmation failed'
            );
        }
    }
}
