<?php

namespace Mpesa\Traits;

trait HasMpesaResponses
{
    /**
     * Generate a success response for M-Pesa
     *
     * @param string $message
     * @return array
     */
    protected function successResponse(string $message = 'Accepted'): array
    {
        return [
            'ResultCode' => '0',
            'ResultDesc' => $message
        ];
    }

    /**
     * Generate an error response for M-Pesa
     *
     * @param string $code
     * @param string $message
     * @return array
     */
    protected function errorResponse(string $code, string $message = 'Rejected'): array
    {
        return [
            'ResultCode' => $code,
            'ResultDesc' => $message
        ];
    }

    /**
     * Get standard M-Pesa error codes
     *
     * @return array
     */
    protected function getErrorCodes(): array
    {
        return [
            'INVALID_MSISDN' => 'C2B00011',
            'INVALID_ACCOUNT' => 'C2B00012',
            'INVALID_AMOUNT' => 'C2B00013',
            'INVALID_KYC' => 'C2B00014',
            'INVALID_SHORTCODE' => 'C2B00015',
            'OTHER_ERROR' => 'C2B00016'
        ];
    }
}
