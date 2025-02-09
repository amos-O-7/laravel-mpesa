# Laravel M-Pesa Integration

A Laravel package for M-Pesa integration supporting both C2B (Customer to Business) and STK Push operations.

## Installation

You can install the package via composer:

```bash
composer require amos-o-7/laravel-mpesa
```

## Configuration

1. Publish the configuration file:

```bash
php artisan vendor:publish --provider="Mpesa\Providers\MpesaServiceProvider" --tag="config"
```

2. Add these variables to your `.env` file:

```env
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_SHORTCODE=your_shortcode
MPESA_PASSKEY=your_passkey
MPESA_CALLBACK_URL=https://your-domain.com/api/mpesa/callback
MPESA_VALIDATION_URL=https://your-domain.com/api/mpesa/validate
MPESA_CONFIRMATION_URL=https://your-domain.com/api/mpesa/confirm
```

## Usage

### Basic Usage

```php
use Mpesa\Services\C2BService;

class PaymentController extends Controller
{
    protected $mpesa;

    public function __construct(C2BService $mpesa)
    {
        $this->mpesa = $mpesa;
    }

    // Register URLs (only needs to be done once)
    public function registerUrls()
    {
        try {
            $response = $this->mpesa->registerUrls();
            return response()->json($response);
        } catch (MpesaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Simulate a C2B payment (sandbox only)
    public function simulatePayment()
    {
        try {
            $response = $this->mpesa->simulateTransaction([
                'Amount' => 100,
                'BillRefNumber' => 'INV001',
                'PhoneNumber' => '254727343690'
            ]);
            return response()->json($response);
        } catch (MpesaException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

### Handling Callbacks

```php
// Validation callback
public function validation(Request $request)
{
    Log::info('M-Pesa Validation', $request->all());
    return response()->json([
        'ResultCode' => 0,
        'ResultDesc' => 'Accepted'
    ]);
}

// Confirmation callback
public function confirmation(Request $request)
{
    Log::info('M-Pesa Confirmation', $request->all());
    return response()->json([
        'ResultCode' => 0,
        'ResultDesc' => 'Success'
    ]);
}
```

### STK Push (M-Pesa Express)

The STK Push feature allows you to initiate M-Pesa payments by sending a payment prompt to the customer's phone.

#### 1. Basic Usage

```php
use Mpesa\Services\STKPushService;

class PaymentController extends Controller
{
    protected $stkPush;

    public function __construct(STKPushService $stkPush)
    {
        $this->stkPush = $stkPush;
    }

    public function initiatePayment()
    {
        try {
            $response = $this->stkPush->initiateSTKPush(
                amount: 100, // Amount in KES
                phoneNumber: '254712345678',
                accountReference: 'INV001',
                transactionDesc: 'Payment for Invoice 001'
            );

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

#### 2. Using the Built-in Controller

The package comes with a pre-built controller. Just make a POST request to `/api/mpesa/stk/push` with the following parameters:

```json
{
    "amount": 100,
    "phone": "254712345678",
    "account_reference": "INV001",
    "transaction_desc": "Payment for Invoice 001",
    "callback_url": "https://your-domain.com/custom-callback" // Optional
}
```

#### 3. Handling Callbacks

Create a callback handler to process M-Pesa payment notifications:

```php
use Illuminate\Support\Facades\Log;

public function handleCallback(Request $request)
{
    $callbackData = $request->input('Body.stkCallback');
    
    if ($callbackData['ResultCode'] == 0) {
        // Payment successful
        $amount = $callbackData['CallbackMetadata']['Item'][0]['Value'];
        $mpesaReceiptNumber = $callbackData['CallbackMetadata']['Item'][1]['Value'];
        $transactionDate = $callbackData['CallbackMetadata']['Item'][2]['Value'];
        $phoneNumber = $callbackData['CallbackMetadata']['Item'][3]['Value'];
        
        // Process the payment...
    } else {
        // Payment failed
        $resultDesc = $callbackData['ResultDesc'];
        // Handle the failure...
    }
}
```

### Response Format

#### Successful Initiation
```json
{
    "MerchantRequestID": "29115-34620561-1",
    "CheckoutRequestID": "ws_CO_191220191020363925",
    "ResponseCode": "0",
    "ResponseDescription": "Success. Request accepted for processing",
    "CustomerMessage": "Success. Request accepted for processing"
}
```

#### Successful Payment Callback
```json
{
    "Body": {
        "stkCallback": {
            "MerchantRequestID": "29115-34620561-1",
            "CheckoutRequestID": "ws_CO_191220191020363925",
            "ResultCode": 0,
            "ResultDesc": "The service request is processed successfully.",
            "CallbackMetadata": {
                "Item": [
                    {
                        "Name": "Amount",
                        "Value": 100.00
                    },
                    {
                        "Name": "MpesaReceiptNumber",
                        "Value": "NLJ7RT61SV"
                    },
                    {
                        "Name": "TransactionDate",
                        "Value": 20191219102115
                    },
                    {
                        "Name": "PhoneNumber",
                        "Value": 254712345678
                    }
                ]
            }
        }
    }
}
```

### Error Handling

The package throws `MpesaException` for M-Pesa API-related errors. Common error scenarios:

1. Invalid phone number format
2. Insufficient balance
3. Invalid credentials
4. Network errors

Always wrap your API calls in try-catch blocks to handle these errors gracefully.

### Testing

```bash
composer test
```

## Security

If you discover any security-related issues, please email amosondari7@gmail.com instead of using the issue tracker.

## Features

- Token Generation
- URL Registration
- C2B Payment Simulation (Sandbox)
- STK Push (M-Pesa Express)
- Validation & Confirmation Handling
- Error Handling
- Automatic Token Management

## Credits

- [Amos O](https://github.com/amos-o-7)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
