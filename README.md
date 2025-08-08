# NowPayments PHP SDK

Official PHP SDK for the NowPayments API. Accept cryptocurrency payments, manage subscriptions, handle payouts, and more with ease.

## Features

- ✅ **Complete API Coverage** - All NowPayments API endpoints
- ✅ **Payment Processing** - Create and manage crypto payments
- ✅ **Subscription Management** - Recurring payments and subscriptions
- ✅ **Mass Payouts** - Batch withdrawals to multiple addresses
- ✅ **Custody Management** - Sub-account and balance management
- ✅ **Currency Conversions** - Internal crypto-to-crypto conversions
- ✅ **IPN Support** - Instant Payment Notifications with signature verification
- ✅ **2FA Integration** - Two-factor authentication for secure operations
- ✅ **Error Handling** - Comprehensive error handling and validation
- ✅ **Type Safety** - Full type hints and documentation

## Installation

```bash
composer require nowpayments/php-sdk
```

## Quick Start

```php
<?php

use NowPayments\NowPaymentsClient;

// Initialize the client
$client = new NowPaymentsClient('YOUR_API_KEY');

// Check API status
$status = $client->getStatus();

// Create a payment
$payment = $client->payments()->create([
    'price_amount' => 100.00,
    'price_currency' => 'usd',
    'pay_currency' => 'btc',
    'order_id' => 'ORDER-123',
    'order_description' => 'Test payment'
]);

echo "Payment ID: " . $payment['payment_id'];
echo "Pay Address: " . $payment['pay_address'];
```

## API Credentials

You'll need the following credentials from your NowPayments account:

- **API Key** - For authentication
- **IPN Secret Key** - For verifying webhook signatures
- **Public Key** - For certain operations

## Documentation

### Core Features

#### Payments
```php
// Create a payment
$payment = $client->payments()->create([
    'price_amount' => 100.00,
    'price_currency' => 'usd',
    'pay_currency' => 'btc',
    'ipn_callback_url' => 'https://your-site.com/webhook',
    'order_id' => 'ORDER-123'
]);

// Get payment status
$status = $client->payments()->getStatus($payment['payment_id']);

// List payments
$payments = $client->payments()->list([
    'limit' => 10,
    'page' => 0,
    'payment_status' => 'finished'
]);
```

#### Subscriptions
```php
// Create a subscription plan
$plan = $client->subscriptions()->createPlan([
    'title' => 'Premium Monthly',
    'interval_day' => 30,
    'amount' => 50,
    'currency' => 'usd'
]);

// Create a subscription
$subscription = $client->subscriptions()->create([
    'plan_id' => $plan['id'],
    'email' => 'customer@example.com',
    'order_id' => 'SUB-001'
]);
```

#### Payouts
```php
// Create a mass payout
$payout = $client->payouts()->create([
    'withdrawals' => [
        [
            'address' => '0x123...',
            'currency' => 'eth',
            'amount' => 0.1
        ]
    ]
]);

// Verify with 2FA
$client->payouts()->verify($payout['batch_id'], '123456');
```

#### Custody (Sub-Accounts)
```php
// Create a user account
$user = $client->custody()->createUser([
    'external_id' => 'user_123',
    'email' => 'user@example.com'
]);

// Get user balance
$balance = $client->custody()->getBalance($user['user_id']);

// Generate deposit address
$deposit = $client->custody()->createPayment([
    'user_id' => $user['user_id'],
    'currency' => 'btc',
    'amount' => 0.01
]);
```

### IPN (Instant Payment Notifications)

```php
// Verify IPN signature
$isValid = $client->ipn()->verifySignature(
    $requestBody,
    $signature,
    'YOUR_IPN_SECRET'
);

if ($isValid) {
    // Process the payment update
    $paymentData = json_decode($requestBody, true);
    // Handle payment status change
}
```

### Error Handling

```php
try {
    $payment = $client->payments()->create([
        'price_amount' => 100,
        'price_currency' => 'usd',
        'pay_currency' => 'btc'
    ]);
} catch (NowPayments\Exception\ApiException $e) {
    echo "API Error: " . $e->getMessage();
    echo "Status Code: " . $e->getCode();
} catch (NowPayments\Exception\ValidationException $e) {
    echo "Validation Error: " . $e->getMessage();
}
```

## Configuration

### Environment Variables

```php
// Using environment variables
$client = new NowPaymentsClient(
    $_ENV['NOWPAYMENTS_API_KEY'],
    [
        'ipn_secret' => $_ENV['NOWPAYMENTS_IPN_SECRET'],
        'sandbox' => $_ENV['NOWPAYMENTS_SANDBOX'] ?? false
    ]
);
```

### Custom HTTP Client

```php
use GuzzleHttp\Client;

$httpClient = new Client([
    'timeout' => 30,
    'connect_timeout' => 10
]);

$client = new NowPaymentsClient('YOUR_API_KEY', [
    'http_client' => $httpClient
]);
```

## Testing

```bash
# Run tests
composer test

# Run with coverage
composer test -- --coverage-html coverage/
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- **Documentation**: [NowPayments API Docs](https://documenter.getpostman.com/view/6634280/S1ETRGzt)
- **Email**: support@nowpayments.io
- **Issues**: [GitHub Issues](https://github.com/nowpayments/php-sdk/issues) 