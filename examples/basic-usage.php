<?php

require_once __DIR__ . '/../vendor/autoload.php';

use NowPayments\NowPaymentsClient;
use NowPayments\Exception\ApiException;
use NowPayments\Exception\ValidationException;

// Initialize the client
$client = new NowPaymentsClient('YOUR_API_KEY', [
    'ipn_secret' => 'YOUR_IPN_SECRET',
    'sandbox' => true // Set to false for production
]);

try {
    // Check API status
    echo "=== API Status ===\n";
    $status = $client->getStatus();
    print_r($status);

    // Get available currencies
    echo "\n=== Available Currencies ===\n";
    $currencies = $client->getCurrencies();
    print_r($currencies);

    // Get merchant active currencies
    echo "\n=== Merchant Currencies ===\n";
    $merchantCurrencies = $client->getMerchantCurrencies();
    print_r($merchantCurrencies);

    // Get minimum payment amount
    echo "\n=== Minimum Payment Amount ===\n";
    $minAmount = $client->general()->getMinAmount('btc', 'usd');
    print_r($minAmount);

    // Get price estimate
    echo "\n=== Price Estimate ===\n";
    $estimate = $client->general()->getEstimate(100.00, 'usd', 'btc');
    print_r($estimate);

    // Create a payment
    echo "\n=== Create Payment ===\n";
    $payment = $client->payments()->create([
        'price_amount' => 100.00,
        'price_currency' => 'usd',
        'pay_currency' => 'btc',
        'order_id' => 'ORDER-' . time(),
        'order_description' => 'Test payment',
        'ipn_callback_url' => 'https://your-site.com/webhook'
    ]);
    print_r($payment);

    // Get payment status
    if (isset($payment['payment_id'])) {
        echo "\n=== Payment Status ===\n";
        $paymentStatus = $client->payments()->getStatus($payment['payment_id']);
        print_r($paymentStatus);
    }

    // List recent payments
    echo "\n=== Recent Payments ===\n";
    $payments = $client->payments()->list([
        'limit' => 5,
        'page' => 0
    ]);
    print_r($payments);

    // Create a subscription plan
    echo "\n=== Create Subscription Plan ===\n";
    $plan = $client->subscriptions()->createPlan([
        'title' => 'Premium Monthly',
        'interval_day' => 30,
        'amount' => 50,
        'currency' => 'usd'
    ]);
    print_r($plan);

    // Create a subscription
    if (isset($plan['id'])) {
        echo "\n=== Create Subscription ===\n";
        $subscription = $client->subscriptions()->create([
            'plan_id' => $plan['id'],
            'email' => 'customer@example.com',
            'order_id' => 'SUB-' . time()
        ]);
        print_r($subscription);
    }

    // Validate payout address
    echo "\n=== Validate Address ===\n";
    $validation = $client->payouts()->validateAddress(
        '0x742d35Cc6634C0532925a3b8D4C9db96C4b4d8b6',
        'eth'
    );
    print_r($validation);

    // Create a user account (custody)
    echo "\n=== Create User Account ===\n";
    $user = $client->custody()->createUser([
        'external_id' => 'user_' . time(),
        'email' => 'user@example.com'
    ]);
    print_r($user);

    // Get user balance
    if (isset($user['user_id'])) {
        echo "\n=== User Balance ===\n";
        $balance = $client->custody()->getBalance($user['user_id']);
        print_r($balance);
    }

    // Create a conversion
    echo "\n=== Create Conversion ===\n";
    $conversion = $client->conversions()->create([
        'from_currency' => 'btc',
        'to_currency' => 'eth',
        'amount' => 0.001
    ]);
    print_r($conversion);

} catch (ApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    echo "Status Code: " . $e->getCode() . "\n";
    print_r($e->getResponseData());
} catch (ValidationException $e) {
    echo "Validation Error: " . $e->getMessage() . "\n";
    print_r($e->getErrors());
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 