<?php

namespace Mpesa\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Mpesa\Http\Requests\C2BValidationRequest;
use Mpesa\Http\Requests\C2BConfirmationRequest;
use Mpesa\Services\C2BService;
use Mpesa\Traits\HasMpesaResponses;

class C2BController
{
    use HasMpesaResponses;

    protected $c2bService;

    public function __construct(C2BService $c2bService)
    {
        $this->c2bService = $c2bService;
    }

    /**
     * Get M-Pesa access token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken()
    {
        try {
            $token = $this->c2bService->generateToken();
            return Response::json(['access_token' => $token]);
        } catch (\Exception $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle validation callback from M-Pesa
     *
     * @param C2BValidationRequest $request
     * @return Response
     */
    public function validation(C2BValidationRequest $request)
    {
        try {
            $result = $this->c2bService->validateTransaction($request->validated());
            return Response::json($result);
        } catch (\Exception $e) {
            return Response::json($this->errorResponse(
                $this->getErrorCodes()['OTHER_ERROR'],
                $e->getMessage()
            ));
        }
    }

    /**
     * Handle confirmation callback from M-Pesa
     *
     * @param C2BConfirmationRequest $request
     * @return Response
     */
    public function confirmation(C2BConfirmationRequest $request)
    {
        try {
            $result = $this->c2bService->confirmTransaction($request->validated());
            return Response::json($result);
        } catch (\Exception $e) {
            return Response::json($this->errorResponse(
                $this->getErrorCodes()['OTHER_ERROR'],
                $e->getMessage()
            ));
        }
    }

    /**
     * Register URLs for C2B
     *
     * @return Response
     */
    public function registerUrls()
    {
        try {
            $result = $this->c2bService->registerUrls(
                Config::get('mpesa.validation_url'),
                Config::get('mpesa.confirmation_url'),
                Config::get('mpesa.response_type')
            );

            return Response::json($result);
        } catch (\Exception $e) {
            return Response::json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
