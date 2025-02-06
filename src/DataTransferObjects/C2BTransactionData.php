<?php

namespace Mpesa\DataTransferObjects;

class C2BTransactionData
{
    public string $transactionType;
    public string $transactionId;
    public string $transactionTime;
    public float $amount;
    public string $businessShortCode;
    public string $billRefNumber;
    public string $mobileNumber;
    public ?string $firstName;
    public ?string $middleName;
    public ?string $lastName;
    public ?string $orgAccountBalance;

    public function __construct(array $data)
    {
        $this->transactionType = $data['TransactionType'];
        $this->transactionId = $data['TransID'];
        $this->transactionTime = $data['TransTime'];
        $this->amount = (float) $data['TransAmount'];
        $this->businessShortCode = $data['BusinessShortCode'];
        $this->billRefNumber = $data['BillRefNumber'];
        $this->mobileNumber = $data['MSISDN'];
        $this->firstName = $data['FirstName'] ?? null;
        $this->middleName = $data['MiddleName'] ?? null;
        $this->lastName = $data['LastName'] ?? null;
        $this->orgAccountBalance = $data['OrgAccountBalance'] ?? null;
    }

    /**
     * Create from M-Pesa response
     *
     * @param array $data
     * @return static
     */
    public static function fromMpesaResponse(array $data): self
    {
        return new static($data);
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'transaction_type' => $this->transactionType,
            'transaction_id' => $this->transactionId,
            'transaction_time' => $this->transactionTime,
            'amount' => $this->amount,
            'business_shortcode' => $this->businessShortCode,
            'bill_ref_number' => $this->billRefNumber,
            'mobile_number' => $this->mobileNumber,
            'first_name' => $this->firstName,
            'middle_name' => $this->middleName,
            'last_name' => $this->lastName,
            'org_account_balance' => $this->orgAccountBalance,
        ];
    }
}
