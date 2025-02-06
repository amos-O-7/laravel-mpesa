<?php

namespace Tests\Unit\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use Mpesa\Services\C2BService;
use PHPUnit\Framework\TestCase;

class C2BServiceTest extends TestCase
{
    protected $c2bService;
    protected $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock config
        Config::shouldReceive('get')
            ->with('mpesa')
            ->andReturn([
                'base_url' => 'https://sandbox.safaricom.co.ke',
                'consumer_key' => 'test_consumer_key',
                'consumer_secret' => 'test_consumer_secret',
                'shortcode' => '123456',
                'validation_url' => 'https://example.com/api/mpesa/c2b/validation',
                'confirmation_url' => 'https://example.com/api/mpesa/c2b/confirmation',
                'response_type' => 'Completed'
            ]);

        // Create mock handler
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $this->c2bService = new C2BService($client);
    }

    public function testRegisterUrlsSuccess()
    {
        // Mock successful response
        $this->mockHandler->append(
            new Response(200, [], json_encode([
                'ConversationID' => 'AG_20240206_1234567890',
                'OriginatorCoversationID' => 'test_123',
                'ResponseDescription' => 'success'
            ]))
        );

        $result = $this->c2bService->registerUrls(
            'https://example.com/api/mpesa/c2b/validation',
            'https://example.com/api/mpesa/c2b/confirmation',
            'Completed'
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('ResponseDescription', $result);
        $this->assertEquals('success', $result['ResponseDescription']);
    }

    public function testValidateTransactionSuccess()
    {
        $transactionData = [
            'TransactionType' => 'Pay Bill',
            'TransID' => 'TEST123456',
            'TransTime' => '20240206203000',
            'TransAmount' => '1000.00',
            'BusinessShortCode' => '123456',
            'BillRefNumber' => 'INV123',
            'MSISDN' => '254712345678'
        ];

        $result = $this->c2bService->validateTransaction($transactionData);

        $this->assertIsArray($result);
        $this->assertEquals('Accepted', $result['ResultDesc']);
    }

    public function testConfirmTransactionSuccess()
    {
        $transactionData = [
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

        $result = $this->c2bService->confirmTransaction($transactionData);

        $this->assertIsArray($result);
        $this->assertEquals('Success', $result['ResultDesc']);
    }
}
