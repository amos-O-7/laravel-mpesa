# Laravel M-Pesa Integration

A Laravel package for M-Pesa integration, currently supporting C2B (Customer to Business) transactions.

## Installation

You can install the package via composer:

```bash
composer require amos-o-7/laravel-mpesa
```

## Configuration

1. Publish the config file:
```bash
php artisan vendor:publish --provider="Mpesa\Providers\MpesaServiceProvider"
```

2. Add these variables to your `.env` file:
```env
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_SHORTCODE=your_shortcode
MPESA_VALIDATION_URL=https://your-domain.com/api/mpesa/c2b/validation
MPESA_CONFIRMATION_URL=https://your-domain.com/api/mpesa/c2b/confirmation
MPESA_RESPONSE_TYPE=Completed
```

## Usage

### Getting Access Token

```php
use Mpesa\Services\C2BService;

public function getToken(C2BService $c2bService)
{
    $token = $c2bService->generateToken();
    return response()->json(['access_token' => $token]);
}
```

### Registering URLs

```php
use Mpesa\Services\C2BService;

public function register(C2BService $c2bService)
{
    $result = $c2bService->registerUrls();
    return response()->json($result);
}
```

### Handling C2B Transactions

The package automatically sets up these endpoints:
- Validation: `/api/mpesa/c2b/validation`
- Confirmation: `/api/mpesa/c2b/confirmation`

## Available Routes

- `GET /api/mpesa/c2b/token` - Generate access token
- `POST /api/mpesa/c2b/register` - Register validation and confirmation URLs
- `POST /api/mpesa/c2b/validation` - Handle validation callbacks
- `POST /api/mpesa/c2b/confirmation` - Handle confirmation callbacks

## Testing

```bash
composer test
```

## Security

If you discover any security related issues, please email amosondari7@gmail.com instead of using the issue tracker.

## Credits

- [Amos O](https://github.com/amos-o-7)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
