<?php

namespace Mpesa\Http\Controllers;

use Mpesa\Services\STKPushService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Class STKPushController
 * 
 * Handles HTTP requests for M-Pesa STK Push operations
 * Provides endpoints for initiating payments and receiving callbacks
 * 
 * @package Mpesa\Http\Controllers
 */
class STKPushController extends Controller
{
    /**
     * @var STKPushService
     */
    protected $stkPushService;

    /**
     * Initialize controller with required services
     * 
     * @param STKPushService $stkPushService
     */
    public function __construct(STKPushService $stkPushService)
    {
        $this->stkPushService = $stkPushService;
    }

    /**
     * Initiate an STK Push payment request
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone' => 'required|string',
            'account_reference' => 'required|string|max:12',
            'transaction_desc' => 'required|string|max:13',
            'callback_url' => 'nullable|url'
        ]);

        try {
            $response = $this->stkPushService->initiateSTKPush(
                $request->amount,
                $request->phone,
                $request->account_reference,
                $request->transaction_desc,
                $request->callback_url ?? null
            );

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle the callback from M-Pesa after payment processing
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        // Log the callback for debugging
        Log::info('STK Push Callback received:', $request->all());

        $callbackData = $request->input('Body.stkCallback');
        
        if (!$callbackData) {
            return response()->json(['error' => 'Invalid callback data'], 400);
        }

        // Handle the callback data as needed
        // You can emit events, update database records, or perform other actions here
        
        return response()->json(['success' => true]);
    }
}
