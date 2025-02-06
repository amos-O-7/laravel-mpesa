<?php

namespace Tests\Feature;

use Orchestra\Testbench\TestCase;
use Mpesa\Providers\MpesaServiceProvider;

class C2BControllerTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [MpesaServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up configuration
        config([
            'mpesa' => [
                'base_url' => 'https://sandbox.safaricom.co.ke',
                'consumer_key' => 'test_consumer_key',
                'consumer_secret' => 'test_consumer_secret',
                'shortcode' => '123456',
                'validation_url' => 'https://example.com/api/mpesa/c2b/validation',
                'confirmation_url' => 'https://example.com/api/mpesa/c2b/confirmation',
                'response_type' => 'Completed'
            ]
        ]);
    }

    public function testValidationEndpoint()
    {
        $payload = [
            'TransactionType' => 'Pay Bill',
            'TransID' => 'TEST123456',
            'TransTime' => '20240206203000',
            'TransAmount' => '1000.00',
            'BusinessShortCode' => '123456',
            'BillRefNumber' => 'INV123',
            'MSISDN' => '254712345678'
        ];

        $response = $this->postJson('/api/mpesa/c2b/validation', $payload);
        $response->assertStatus(200)
                ->assertJson(['ResultDesc' => 'Accepted']);
    }

    public function testConfirmationEndpoint()
    {
        $payload = [
            'TransactionType' => 'Pay Bill',
            'TransID' => 'TEST123456',
            'TransTime' => '20240206203000',
            'TransAmount' => '1000.00',
            'BusinessShortCode' => '123456',
            'BillRefNumber' => 'INV123',
            'MSISDN' => '254712345678',
            'FirstName' => 'John',
            'MiddleName' => 'Doe',
            'LastName' => 'Smith'
        ];

        $response = $this->postJson('/api/mpesa/c2b/confirmation', $payload);
        $response->assertStatus(200)
                ->assertJson(['ResultDesc' => 'Success']);
    }

    public function testRegisterUrlsEndpoint()
    {
        $response = $this->postJson('/api/mpesa/c2b/register');
        $response->assertStatus(200);
    }
}
