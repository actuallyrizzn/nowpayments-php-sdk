<?php

namespace NowPayments\Tests\Integration;

use PHPUnit\Framework\TestCase;
use NowPayments\NowPaymentsClient;
use NowPayments\Services\PaymentsService;
use NowPayments\Services\IpnService;
use NowPayments\Services\GeneralService;
use NowPayments\Services\SubscriptionsService;
use NowPayments\Services\PayoutsService;
use NowPayments\Services\CustodyService;
use NowPayments\Services\ConversionsService;
use NowPayments\Exception\ConfigurationException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class NowPaymentsIntegrationTest extends TestCase
{
    private NowPaymentsClient $client;

    protected function setUp(): void
    {
        $this->client = new NowPaymentsClient('test-api-key', [
            'ipn_secret' => 'test-secret',
            'sandbox' => true
        ]);
    }

    public function testClientWithAllServices()
    {
        // Test that all services can be accessed from the client
        $this->assertInstanceOf(\NowPayments\Services\GeneralService::class, $this->client->general());
        $this->assertInstanceOf(\NowPayments\Services\PaymentsService::class, $this->client->payments());
        $this->assertInstanceOf(\NowPayments\Services\IpnService::class, $this->client->ipn());
        $this->assertInstanceOf(\NowPayments\Services\SubscriptionsService::class, $this->client->subscriptions());
        $this->assertInstanceOf(\NowPayments\Services\PayoutsService::class, $this->client->payouts());
        $this->assertInstanceOf(\NowPayments\Services\CustodyService::class, $this->client->custody());
        $this->assertInstanceOf(\NowPayments\Services\ConversionsService::class, $this->client->conversions());
    }

    public function testPaymentCreationAndIpnVerification()
    {
        // Mock payment creation response
        $paymentResponse = new Response(200, [], json_encode([
            'payment_id' => 123,
            'payment_status' => 'waiting',
            'pay_address' => 'test-address',
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_amount' => 0.001,
            'pay_currency' => 'BTC'
        ]));

        // Mock IPN data
        $ipnData = [
            'payment_id' => 123,
            'payment_status' => 'finished',
            'pay_address' => 'test-address',
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_amount' => 0.001,
            'pay_currency' => 'BTC'
        ];

        $mock = new MockHandler([$paymentResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api-sandbox.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        $client->method('getIpnSecret')->willReturn('test-secret');

        // Create payment
        $paymentsService = new PaymentsService($client);
        $payment = $paymentsService->create([
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_currency' => 'BTC'
        ]);

        // Verify IPN signature
        $ipnService = new IpnService($client);
        $requestBody = json_encode($ipnData);
        
        // Create signature
        $data = json_decode($requestBody, true);
        ksort($data);
        $sortedBody = json_encode($data);
        $signature = hash_hmac('sha512', $sortedBody, 'test-secret');

        $verified = $ipnService->verifySignature($requestBody, $signature, 'test-secret');
        $processed = $ipnService->processIpn($requestBody, $signature, 'test-secret');

        $this->assertTrue($verified);
        $this->assertEquals($ipnData, $processed);
        $this->assertTrue($ipnService->isPaymentCompleted($processed));
    }

    public function testGeneralServiceAndPaymentIntegration()
    {
        // Mock general service responses
        $currenciesResponse = new Response(200, [], json_encode([
            'currencies' => ['BTC', 'ETH', 'USDT']
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
            'pay_address' => 'test-address'
        ]));

        $mock = new MockHandler([$currenciesResponse, $estimateResponse, $paymentResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api-sandbox.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);

        // Get available currencies
        $generalService = new GeneralService($client);
        $currencies = $generalService->getCurrencies();

        // Get price estimate
        $estimate = $generalService->getEstimate(100.0, 'USD', 'BTC');

        // Create payment based on estimate
        $paymentsService = new PaymentsService($client);
        $payment = $paymentsService->create([
            'price_amount' => 100,
            'price_currency' => 'USD',
            'pay_currency' => 'BTC'
        ]);

        $this->assertArrayHasKey('currencies', $currencies);
        $this->assertArrayHasKey('estimated_amount', $estimate);
        $this->assertArrayHasKey('payment_id', $payment);
    }

    public function testPayoutAndCustodyIntegration()
    {
        // Mock custody service responses
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

        $payoutResponse = new Response(200, [], json_encode([
            'batch_id' => 'batch_123',
            'status' => 'pending'
        ]));

        $mock = new MockHandler([$userResponse, $balanceResponse, $payoutResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api-sandbox.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);

        // Create user account
        $custodyService = new CustodyService($client);
        $user = $custodyService->createUser(['external_id' => 'ext_123']);

        // Get user balance
        $balance = $custodyService->getBalance(123);

        // Create payout
        $payoutsService = new PayoutsService($client);
        $payout = $payoutsService->create([
            'withdrawals' => [
                [
                    'address' => 'test-address',
                    'currency' => 'BTC',
                    'amount' => 0.001
                ]
            ]
        ]);

        $this->assertArrayHasKey('user_id', $user);
        $this->assertArrayHasKey('balances', $balance);
        $this->assertArrayHasKey('batch_id', $payout);
    }

    public function testSubscriptionAndConversionIntegration()
    {
        // Mock subscription service responses
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
            'status' => 'active'
        ]));

        $conversionResponse = new Response(200, [], json_encode([
            'conversion_id' => 'conv_123',
            'from_currency' => 'USD',
            'to_currency' => 'BTC',
            'amount' => 100,
            'status' => 'pending'
        ]));

        $mock = new MockHandler([$planResponse, $subscriptionResponse, $conversionResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api-sandbox.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);

        // Create subscription plan
        $subscriptionsService = new SubscriptionsService($client);
        $plan = $subscriptionsService->createPlan([
            'title' => 'Monthly Plan',
            'interval_day' => 30,
            'amount' => 100,
            'currency' => 'USD'
        ]);

        // Create subscription
        $subscription = $subscriptionsService->create([
            'plan_id' => 'plan_123',
            'email' => 'test@example.com'
        ]);

        // Create conversion
        $conversionsService = new ConversionsService($client);
        $conversion = $conversionsService->create([
            'from_currency' => 'USD',
            'to_currency' => 'BTC',
            'amount' => 100
        ]);

        $this->assertArrayHasKey('plan_id', $plan);
        $this->assertArrayHasKey('subscription_id', $subscription);
        $this->assertArrayHasKey('conversion_id', $conversion);
    }

    public function testErrorHandlingIntegration()
    {
        // Test configuration error
        $this->expectException(ConfigurationException::class);
        new NowPaymentsClient('');

        // Test API error handling
        $errorResponse = new Response(400, [], json_encode([
            'message' => 'Bad Request',
            'error' => 'Invalid API key'
        ]));

        $mock = new MockHandler([
            new \GuzzleHttp\Exception\RequestException('Bad Request', new \GuzzleHttp\Psr7\Request('GET', 'test'), $errorResponse)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api-sandbox.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);

        $generalService = new GeneralService($client);

        $this->expectException(\NowPayments\Exception\ApiException::class);
        $generalService->getStatus();
    }

    public function testSandboxAndProductionUrls()
    {
        // Test sandbox URL
        $sandboxClient = new NowPaymentsClient('test-api-key', ['sandbox' => true]);
        $this->assertStringContainsString('api-sandbox.nowpayments.io', $sandboxClient->getBaseUrl());

        // Test production URL
        $productionClient = new NowPaymentsClient('test-api-key', ['sandbox' => false]);
        $this->assertStringContainsString('api.nowpayments.io', $productionClient->getBaseUrl());
    }

    public function testCustomHttpClientIntegration()
    {
        $customHttpClient = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Custom-NowPayments-SDK/1.0'
            ]
        ]);

        $client = new NowPaymentsClient('test-api-key', [
            'http_client' => $customHttpClient
        ]);

        $this->assertInstanceOf(\GuzzleHttp\ClientInterface::class, $client->getHttpClient());
    }
} 