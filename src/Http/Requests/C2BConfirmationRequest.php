<?php

namespace Mpesa\Http\Requests;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Mpesa\Exceptions\MpesaException;

class C2BConfirmationRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'TransactionType' => 'required|string',
            'TransID' => 'required|string',
            'TransTime' => 'required|string',
            'TransAmount' => 'required|numeric',
            'BusinessShortCode' => 'required|string',
            'BillRefNumber' => 'required|string',
            'MSISDN' => 'required|string',
            'FirstName' => 'nullable|string',
            'MiddleName' => 'nullable|string',
            'LastName' => 'nullable|string',
            'OrgAccountBalance' => 'nullable|string',
        ];
    }

    /**
     * Get validated data
     *
     * @return array
     * @throws MpesaException
     */
    public function validated(): array
    {
        $validator = Validator::make($this->all(), $this->rules());

        if ($validator->fails()) {
            throw new MpesaException('Invalid C2B confirmation request: ' . $validator->errors()->first());
        }

        return $validator->validated();
    }
}
