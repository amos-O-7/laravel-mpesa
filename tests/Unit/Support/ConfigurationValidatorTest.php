<?php

namespace Tests\Unit\Support;

use Illuminate\Support\Facades\Config;
use Mpesa\Exceptions\MpesaException;
use Mpesa\Support\ConfigurationValidator;
use PHPUnit\Framework\TestCase;

class ConfigurationValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testValidConfigurationPasses()
    {
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

        ConfigurationValidator::validate();
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testMissingConfigurationThrowsException()
    {
        $this->expectException(MpesaException::class);
        $this->expectExceptionMessage('M-Pesa configuration not found. Please publish the config file.');

        Config::shouldReceive('get')
            ->with('mpesa')
            ->andReturn(null);

        ConfigurationValidator::validate();
    }

    public function testInvalidResponseTypeThrowsException()
    {
        $this->expectException(MpesaException::class);
        $this->expectExceptionMessage("Response type must be either 'Completed' or 'Cancelled'.");

        Config::shouldReceive('get')
            ->with('mpesa')
            ->andReturn([
                'base_url' => 'https://sandbox.safaricom.co.ke',
                'consumer_key' => 'test_consumer_key',
                'consumer_secret' => 'test_consumer_secret',
                'shortcode' => '123456',
                'validation_url' => 'https://example.com/api/mpesa/c2b/validation',
                'confirmation_url' => 'https://example.com/api/mpesa/c2b/confirmation',
                'response_type' => 'Invalid'
            ]);

        ConfigurationValidator::validate();
    }

    public function testInvalidUrlThrowsException()
    {
        $this->expectException(MpesaException::class);
        $this->expectExceptionMessage("Invalid URL format for 'mpesa.validation_url'.");

        Config::shouldReceive('get')
            ->with('mpesa')
            ->andReturn([
                'base_url' => 'https://sandbox.safaricom.co.ke',
                'consumer_key' => 'test_consumer_key',
                'consumer_secret' => 'test_consumer_secret',
                'shortcode' => '123456',
                'validation_url' => 'invalid-url',
                'confirmation_url' => 'https://example.com/api/mpesa/c2b/confirmation',
                'response_type' => 'Completed'
            ]);

        ConfigurationValidator::validate();
    }
}
