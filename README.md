# Laravel M-Pesa Integration

A Laravel package for M-Pesa C2B (Customer to Business) integration.

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

## Features

- Token Generation
- URL Registration
- C2B Payment Simulation (Sandbox)
- Validation & Confirmation Handling
- Error Handling
- Automatic Token Management

## Testing

```bash
composer test
```

## Security

If you discover any security-related issues, please email amosondari7@gmail.com instead of using the issue tracker.

## Credits

- [Amos O](https://github.com/amos-o-7)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
