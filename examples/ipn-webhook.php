<?php

require_once __DIR__ . '/../vendor/autoload.php';

use NowPayments\NowPaymentsClient;

// Initialize the client with IPN secret
$client = new NowPaymentsClient('YOUR_API_KEY', [
    'ipn_secret' => 'YOUR_IPN_SECRET'
]);

// Get the raw request body
$requestBody = file_get_contents('php://input');

// Get the signature from headers
$signature = $_SERVER['HTTP_X_NOWPAYMENTS_SIG'] ?? '';

// Verify the signature
$isValid = $client->ipn()->verifySignatureWithClientSecret($requestBody, $signature);

if (!$isValid) {
    http_response_code(400);
    echo "Invalid signature";
    exit;
}

// Process the IPN data
$ipnData = json_decode($requestBody, true);

if ($ipnData === null) {
    http_response_code(400);
    echo "Invalid JSON";
    exit;
}

// Extract payment information
$paymentData = $client->ipn()->extractPaymentData($ipnData);

// Handle different payment statuses
switch ($paymentData['payment_status']) {
    case 'waiting':
        echo "Payment is waiting for customer to pay\n";
        // Update your database to show payment is pending
        break;
        
    case 'confirming':
        echo "Payment received and confirming on blockchain\n";
        // Update your database to show payment is confirming
        break;
        
    case 'confirmed':
        echo "Payment confirmed on blockchain\n";
        // Update your database to show payment is confirmed
        break;
        
    case 'finished':
        echo "Payment completed successfully!\n";
        echo "Payment ID: " . $paymentData['payment_id'] . "\n";
        echo "Amount Paid: " . $paymentData['actually_paid'] . " " . $paymentData['pay_currency'] . "\n";
        echo "Order ID: " . $paymentData['order_id'] . "\n";
        
        // Update your database to mark order as paid
        // Send confirmation email to customer
        // Update inventory, etc.
        break;
        
    case 'partially_paid':
        echo "Payment partially paid\n";
        echo "Expected: " . $paymentData['pay_amount'] . " " . $paymentData['pay_currency'] . "\n";
        echo "Actually Paid: " . $paymentData['actually_paid'] . " " . $paymentData['pay_currency'] . "\n";
        // Handle partial payment (maybe wait for more or refund)
        break;
        
    case 'failed':
        echo "Payment failed\n";
        // Update your database to show payment failed
        // Maybe send email to customer about failed payment
        break;
        
    case 'expired':
        echo "Payment expired\n";
        // Update your database to show payment expired
        break;
        
    default:
        echo "Unknown payment status: " . $paymentData['payment_status'] . "\n";
        break;
}

// Log the IPN data for debugging
error_log("NowPayments IPN: " . json_encode($ipnData));

// Return 200 OK to acknowledge receipt
http_response_code(200);
echo "OK"; 