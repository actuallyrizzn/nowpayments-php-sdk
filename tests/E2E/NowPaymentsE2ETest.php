<?php

namespace NowPayments\Tests\E2E;

use PHPUnit\Framework\TestCase;
use NowPayments\NowPaymentsClient;
use NowPayments\Services\PaymentsService;
use NowPayments\Services\IpnService;
use NowPayments\Services\GeneralService;
use NowPayments\Services\SubscriptionsService;
use NowPayments\Services\PayoutsService;
use NowPayments\Services\CustodyService;
use NowPayments\Services\ConversionsService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class NowPaymentsE2ETest extends TestCase
{
    private NowPaymentsClient $client;

    protected function setUp(): void
    {
        $this->client = new NowPaymentsClient('test-api-key', [
            'ipn_secret' => 'test-secret',
            'sandbox' => true
        ]);
    }

    public function testCompletePaymentWorkflow()
    {
        // Mock responses for a complete payment workflow
        $statusResponse = new Response(200, [], json_encode([
            'message' => 'OK',
            'result' => 'success'
        ]));

        $currenciesResponse = new Response(200, [], json_encode([
            'currencies' => ['BTC', 'ETH', 'USDT'],
            'currencies_names' => [
                'BTC' => 'Bitcoin',
                'ETH' => 'Ethereum',
                'USDT' => 'Tether'
            ]
        ]));

        $estimateResponse = new Response(200, [], json_encode([
            'estimated_amount' => 0.001,
            'currency_from' => 'USD',
            'currency_to' => 'BTC',
            'amount' => 100
        ]));

        $paymentResponse = new Response(200, [], json_encode([
            'payment_id' => 123,
            'payment_status' => 'waiting',
            'pay_address' => 'test-address',
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_amount' => 0.001,
            'pay_currency' => 'BTC'
        ]));

        $paymentStatusResponse = new Response(200, [], json_encode([
            'payment_id' => 123,
            'payment_status' => 'finished',
            'pay_address' => 'test-address',
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_amount' => 0.001,
            'pay_currency' => 'BTC'
        ]));

        $mock = new MockHandler([
            $statusResponse,
            $currenciesResponse,
            $estimateResponse,
            $paymentResponse,
            $paymentStatusResponse
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api-sandbox.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);

        // Step 1: Check API status
        $generalService = new GeneralService($client);
        $status = $generalService->getStatus();
        $this->assertEquals('OK', $status['message']);

        // Step 2: Get available currencies
        $currencies = $generalService->getCurrencies();
        $this->assertArrayHasKey('currencies', $currencies);
        $this->assertContains('BTC', $currencies['currencies']);

        // Step 3: Get price estimate
        $estimate = $generalService->getEstimate(100.0, 'USD', 'BTC');
        $this->assertArrayHasKey('estimated_amount', $estimate);
        $this->assertEquals(100, $estimate['amount']);

        // Step 4: Create payment
        $paymentsService = new PaymentsService($client);
        $payment = $paymentsService->create([
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_currency' => 'BTC'
        ]);
        $this->assertArrayHasKey('payment_id', $payment);
        $this->assertEquals('waiting', $payment['payment_status']);

        // Step 5: Check payment status
        $paymentStatus = $paymentsService->getStatus(123);
        $this->assertEquals('finished', $paymentStatus['payment_status']);
    }

    public function testCompleteSubscriptionWorkflow()
    {
        // Mock responses for subscription workflow
        $planResponse = new Response(200, [], json_encode([
            'plan_id' => 'plan_123',
            'title' => 'Monthly Plan',
            'interval_day' => 30,
            'amount' => 100,
            'currency' => 'USD'
        ]));

        $subscriptionResponse = new Response(200, [], json_encode([
            'subscription_id' => 'sub_123',
            'plan_id' => 'plan_123',
            'email' => 'test@example.com',
            'status' => 'active'
        ]));

        $subscriptionStatusResponse = new Response(200, [], json_encode([
            'subscription_id' => 'sub_123',
            'plan_id' => 'plan_123',
            'email' => 'test@example.com',
            'status' => 'active'
        ]));

        $cancelResponse = new Response(200, [], json_encode([
            'subscription_id' => 'sub_123',
            'status' => 'cancelled'
        ]));

        $mock = new MockHandler([
            $planResponse,
            $subscriptionResponse,
            $subscriptionStatusResponse,
            $cancelResponse
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api-sandbox.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);

        // Step 1: Create subscription plan
        $subscriptionsService = new SubscriptionsService($client);
        $plan = $subscriptionsService->createPlan([
            'title' => 'Monthly Plan',
            'interval_day' => 30,
            'amount' => 100,
            'currency' => 'USD'
        ]);
        $this->assertArrayHasKey('plan_id', $plan);

        // Step 2: Create subscription
        $subscription = $subscriptionsService->create([
            'plan_id' => 'plan_123',
            'email' => 'test@example.com'
        ]);
        $this->assertArrayHasKey('subscription_id', $subscription);
        $this->assertEquals('active', $subscription['status']);

        // Step 3: Check subscription status
        $subscriptionStatus = $subscriptionsService->getSubscription('sub_123');
        $this->assertEquals('active', $subscriptionStatus['status']);

        // Step 4: Cancel subscription
        $cancelled = $subscriptionsService->cancel('sub_123');
        $this->assertEquals('cancelled', $cancelled['status']);
    }

    public function testCompletePayoutWorkflow()
    {
        // Mock responses for payout workflow
        $validateAddressResponse = new Response(200, [], json_encode([
            'address' => 'test-address',
            'currency' => 'BTC',
            'valid' => true
        ]));

        $payoutResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'pending',
            'withdrawals' => [
                [
                    'address' => 'test-address',
                    'currency' => 'BTC',
                    'amount' => 0.001
                ]
            ]
        ]));

        $payoutStatusResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'finished'
        ]));

        $mock = new MockHandler([
            $validateAddressResponse,
            $payoutResponse,
            $payoutStatusResponse  // Call to getStatus from isFinished method
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api-sandbox.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);

        // Step 1: Validate payout address
        $payoutsService = new PayoutsService($client);
        $validation = $payoutsService->validateAddress('test-address', 'BTC');
        $this->assertTrue($validation['valid']);

        // Step 2: Create payout
        $payout = $payoutsService->create([
            'withdrawals' => [
                [
                    'address' => 'test-address',
                    'currency' => 'BTC',
                    'amount' => 0.001
                ]
            ]
        ]);
        $this->assertArrayHasKey('batch_id', $payout);
        $this->assertEquals('pending', $payout['status']);

        // Step 3: Verify payout is finished
        $isFinished = $payoutsService->isFinished('batch_123');
        $this->assertTrue($isFinished, 'Expected payout to be finished but got false');
    }

    public function testCompleteCustodyWorkflow()
    {
        // Mock responses for custody workflow
        $userResponse = new Response(200, [], json_encode([
            'user_id' => 123,
            'external_id' => 'ext_123'
        ]));

        $balanceResponse = new Response(200, [], json_encode([
            'user_id' => 123,
            'balances' => [
                'BTC' => 0.001,
                'ETH' => 0.01
            ]
        ]));

        $paymentResponse = new Response(200, [], json_encode([
            'payment_id' => 'pay_123',
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001
        ]));

        $transferResponse = new Response(200, [], json_encode([
            'transfer_id' => 'trans_123',
            'from_id' => 123,
            'to_id' => 124,
            'currency' => 'BTC',
            'amount' => 0.001
        ]));

        $mock = new MockHandler([
            $userResponse,
            $balanceResponse,
            $paymentResponse,
            $transferResponse
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api-sandbox.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);

        // Step 1: Create user account
        $custodyService = new CustodyService($client);
        $user = $custodyService->createUser(['external_id' => 'ext_123']);
        $this->assertArrayHasKey('user_id', $user);

        // Step 2: Get user balance
        $balance = $custodyService->getBalance(123);
        $this->assertArrayHasKey('balances', $balance);
        $this->assertArrayHasKey('BTC', $balance['balances']);

        // Step 3: Create deposit payment
        $payment = $custodyService->createPayment([
            'user_id' => 123,
            'currency' => 'BTC',
            'amount' => 0.001
        ]);
        $this->assertArrayHasKey('payment_id', $payment);

        // Step 4: Transfer funds between users
        $transfer = $custodyService->transfer([
            'from_id' => 123,
            'to_id' => 124,
            'currency' => 'BTC',
            'amount' => 0.001
        ]);
        $this->assertArrayHasKey('transfer_id', $transfer);
    }

    public function testCompleteConversionWorkflow()
    {
        // Mock responses for conversion workflow
        $conversionResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'from_currency' => 'BTC',
            'to_currency' => 'ETH',
            'amount' => 0.001,
            'status' => 'pending'
        ]));

        $conversionStatusResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'from_currency' => 'BTC',
            'to_currency' => 'ETH',
            'amount' => 0.001,
            'status' => 'completed',
            'rate' => 15.5,
            'to_amount' => 0.0155
        ]));

        $mock = new MockHandler([
            $conversionResponse,
            $conversionStatusResponse  // Call to getStatus from isCompleted method
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api-sandbox.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);

        // Step 1: Create conversion
        $conversionsService = new ConversionsService($client);
        $conversion = $conversionsService->create([
            'from_currency' => 'BTC',
            'to_currency' => 'ETH',
            'amount' => 0.001
        ]);
        $this->assertArrayHasKey('conversion_id', $conversion);
        $this->assertEquals('pending', $conversion['status']);

        // Step 2: Verify conversion is completed and get details
        $isCompleted = $conversionsService->isCompleted('conv_123');
        $this->assertTrue($isCompleted, 'Expected conversion to be completed but got false');

        // Step 3: Test rate and converted amount methods with mock data
        $mockConversionData = [
            'conversion_id' => 'conv_123',
            'from_currency' => 'BTC',
            'to_currency' => 'ETH',
            'amount' => 0.001,
            'status' => 'completed',
            'rate' => 15.5,
            'to_amount' => 0.0155
        ];
        $rate = $conversionsService->getRate($mockConversionData);
        $convertedAmount = $conversionsService->getConvertedAmount($mockConversionData);
        $this->assertEquals(15.5, $rate);
        $this->assertEquals(0.0155, $convertedAmount);
    }

    public function testIPNWebhookWorkflow()
    {
        // Mock IPN data
        $ipnData = [
            'payment_id' => 123,
            'payment_status' => 'finished',
            'pay_address' => 'test-address',
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_amount' => 0.001,
            'pay_currency' => 'BTC',
            'order_id' => 'order-123',
            'created_at' => '2023-01-01T00:00:00Z',
            'updated_at' => '2023-01-01T01:00:00Z'
        ];

        $requestBody = json_encode($ipnData);
        
        // Create signature
        $data = json_decode($requestBody, true);
        ksort($data);
        $sortedBody = json_encode($data);
        $signature = hash_hmac('sha512', $sortedBody, 'test-secret');

        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getIpnSecret')->willReturn('test-secret');

        // Step 1: Verify IPN signature
        $ipnService = new IpnService($client);
        $verified = $ipnService->verifySignature($requestBody, $signature, 'test-secret');
        $this->assertTrue($verified);

        // Step 2: Process IPN data
        $processed = $ipnService->processIpn($requestBody, $signature, 'test-secret');
        $this->assertEquals($ipnData, $processed);

        // Step 3: Extract payment data
        $paymentData = $ipnService->extractPaymentData($processed);
        $this->assertEquals(123, $paymentData['payment_id']);
        $this->assertEquals('finished', $paymentData['payment_status']);

        // Step 4: Check payment status
        $this->assertTrue($ipnService->isPaymentCompleted($processed));
        $this->assertFalse($ipnService->isPaymentWaiting($processed));
        $this->assertFalse($ipnService->isPaymentFailed($processed));
    }

    public function testErrorHandlingE2E()
    {
        // Test various error scenarios
        $errorResponse = new Response(400, [], json_encode([
            'message' => 'Bad Request',
            'error' => 'Invalid parameters'
        ]));

        $mock = new MockHandler([
            new \GuzzleHttp\Exception\RequestException('Bad Request', new \GuzzleHttp\Psr7\Request('GET', 'test'), $errorResponse)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api-sandbox.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);

        $paymentsService = new PaymentsService($client);

        // Test API error handling
        $this->expectException(\NowPayments\Exception\ApiException::class);
        $paymentsService->create([
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_currency' => 'BTC'
        ]);
    }
} 