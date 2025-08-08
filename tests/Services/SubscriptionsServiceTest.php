<?php

namespace NowPayments\Tests\Services;

use PHPUnit\Framework\TestCase;
use NowPayments\Services\SubscriptionsService;
use NowPayments\NowPaymentsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class SubscriptionsServiceTest extends TestCase
{
    private SubscriptionsService $service;
    private NowPaymentsClient $client;

    protected function setUp(): void
    {
        $this->client = new NowPaymentsClient('test-api-key');
        $this->service = new SubscriptionsService($this->client);
    }

    public function testCreatePlan()
    {
        $mockResponse = new Response(200, [], json_encode([
            'plan_id' => 'plan_123',
            'title' => 'Monthly Plan',
            'interval_day' => 30,
            'amount' => 100,
            'currency' => 'USD'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->createPlan([
            'title' => 'Monthly Plan',
            'interval_day' => 30,
            'amount' => 100,
            'currency' => 'USD'
        ]);
        
        $this->assertEquals([
            'plan_id' => 'plan_123',
            'title' => 'Monthly Plan',
            'interval_day' => 30,
            'amount' => 100,
            'currency' => 'USD'
        ], $result);
    }

    public function testCreatePlanWithMissingRequiredFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: interval_day, amount, currency');
        
        $this->service->createPlan([
            'title' => 'Monthly Plan'
            // Missing interval_day, amount, currency
        ]);
    }

    public function testUpdatePlan()
    {
        $mockResponse = new Response(200, [], json_encode([
            'plan_id' => 'plan_123',
            'title' => 'Updated Plan',
            'amount' => 150
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->updatePlan('plan_123', [
            'title' => 'Updated Plan',
            'amount' => 150
        ]);
        
        $this->assertEquals([
            'plan_id' => 'plan_123',
            'title' => 'Updated Plan',
            'amount' => 150
        ], $result);
    }

    public function testGetPlan()
    {
        $mockResponse = new Response(200, [], json_encode([
            'plan_id' => 'plan_123',
            'title' => 'Monthly Plan',
            'interval_day' => 30,
            'amount' => 100,
            'currency' => 'USD'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->getPlan('plan_123');
        
        $this->assertEquals([
            'plan_id' => 'plan_123',
            'title' => 'Monthly Plan',
            'interval_day' => 30,
            'amount' => 100,
            'currency' => 'USD'
        ], $result);
    }

    public function testListPlans()
    {
        $mockResponse = new Response(200, [], json_encode([
            'plans' => [
                [
                    'plan_id' => 'plan_123',
                    'title' => 'Monthly Plan'
                ],
                [
                    'plan_id' => 'plan_124',
                    'title' => 'Yearly Plan'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->listPlans();
        
        $this->assertEquals([
            'plans' => [
                [
                    'plan_id' => 'plan_123',
                    'title' => 'Monthly Plan'
                ],
                [
                    'plan_id' => 'plan_124',
                    'title' => 'Yearly Plan'
                ]
            ]
        ], $result);
    }

    public function testCreateSubscription()
    {
        $mockResponse = new Response(200, [], json_encode([
            'subscription_id' => 'sub_123',
            'plan_id' => 'plan_123',
            'email' => 'test@example.com',
            'status' => 'active'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->create([
            'plan_id' => 'plan_123',
            'email' => 'test@example.com'
        ]);
        
        $this->assertEquals([
            'subscription_id' => 'sub_123',
            'plan_id' => 'plan_123',
            'email' => 'test@example.com',
            'status' => 'active'
        ], $result);
    }

    public function testCreateSubscriptionWithMissingRequiredFields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: email');
        
        $this->service->create([
            'plan_id' => 'plan_123'
            // Missing email
        ]);
    }

    public function testGetSubscription()
    {
        $mockResponse = new Response(200, [], json_encode([
            'subscription_id' => 'sub_123',
            'plan_id' => 'plan_123',
            'email' => 'test@example.com',
            'status' => 'active'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->getSubscription('sub_123');
        
        $this->assertEquals([
            'subscription_id' => 'sub_123',
            'plan_id' => 'plan_123',
            'email' => 'test@example.com',
            'status' => 'active'
        ], $result);
    }

    public function testListSubscriptions()
    {
        $mockResponse = new Response(200, [], json_encode([
            'subscriptions' => [
                [
                    'subscription_id' => 'sub_123',
                    'status' => 'active'
                ],
                [
                    'subscription_id' => 'sub_124',
                    'status' => 'cancelled'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->list();
        
        $this->assertEquals([
            'subscriptions' => [
                [
                    'subscription_id' => 'sub_123',
                    'status' => 'active'
                ],
                [
                    'subscription_id' => 'sub_124',
                    'status' => 'cancelled'
                ]
            ]
        ], $result);
    }

    public function testListSubscriptionsWithFilters()
    {
        $mockResponse = new Response(200, [], json_encode([
            'subscriptions' => [
                [
                    'subscription_id' => 'sub_123',
                    'status' => 'active'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->list(['status' => 'active', 'limit' => 10]);
        
        $this->assertEquals([
            'subscriptions' => [
                [
                    'subscription_id' => 'sub_123',
                    'status' => 'active'
                ]
            ]
        ], $result);
    }

    public function testCancelSubscription()
    {
        $mockResponse = new Response(200, [], json_encode([
            'subscription_id' => 'sub_123',
            'status' => 'cancelled'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->cancel('sub_123');
        
        $this->assertEquals([
            'subscription_id' => 'sub_123',
            'status' => 'cancelled'
        ], $result);
    }

    public function testCreateSubscriptionPlanConvenienceMethod()
    {
        $mockResponse = new Response(200, [], json_encode([
            'plan_id' => 'plan_123',
            'title' => 'Monthly Plan',
            'interval_day' => 30,
            'amount' => 100,
            'currency' => 'USD'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->createSubscriptionPlan('Monthly Plan', 30, 100.0, 'USD', [
            'description' => 'Monthly subscription'
        ]);
        
        $this->assertEquals([
            'plan_id' => 'plan_123',
            'title' => 'Monthly Plan',
            'interval_day' => 30,
            'amount' => 100,
            'currency' => 'USD'
        ], $result);
    }

    public function testCreateSubscriptionConvenienceMethod()
    {
        $mockResponse = new Response(200, [], json_encode([
            'subscription_id' => 'sub_123',
            'plan_id' => 'plan_123',
            'email' => 'test@example.com',
            'status' => 'active'
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->createSubscription('plan_123', 'test@example.com', [
            'name' => 'John Doe'
        ]);
        
        $this->assertEquals([
            'subscription_id' => 'sub_123',
            'plan_id' => 'plan_123',
            'email' => 'test@example.com',
            'status' => 'active'
        ], $result);
    }

    public function testListByPlan()
    {
        $mockResponse = new Response(200, [], json_encode([
            'subscriptions' => [
                [
                    'subscription_id' => 'sub_123',
                    'plan_id' => 'plan_123'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->listByPlan('plan_123', 5, 1);
        
        $this->assertEquals([
            'subscriptions' => [
                [
                    'subscription_id' => 'sub_123',
                    'plan_id' => 'plan_123'
                ]
            ]
        ], $result);
    }

    public function testListByStatus()
    {
        $mockResponse = new Response(200, [], json_encode([
            'subscriptions' => [
                [
                    'subscription_id' => 'sub_123',
                    'status' => 'active'
                ]
            ]
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->listByStatus('active', 5, 1);
        
        $this->assertEquals([
            'subscriptions' => [
                [
                    'subscription_id' => 'sub_123',
                    'status' => 'active'
                ]
            ]
        ], $result);
    }

    public function testUpdatePlanAmount()
    {
        $mockResponse = new Response(200, [], json_encode([
            'plan_id' => 'plan_123',
            'amount' => 150
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->updatePlanAmount('plan_123', 150.0);
        
        $this->assertEquals([
            'plan_id' => 'plan_123',
            'amount' => 150
        ], $result);
    }

    public function testUpdatePlanInterval()
    {
        $mockResponse = new Response(200, [], json_encode([
            'plan_id' => 'plan_123',
            'interval_day' => 60
        ]));
        $mock = new MockHandler([$mockResponse]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);
        
        $client = $this->createMock(NowPaymentsClient::class);
        $client->method('getBaseUrl')->willReturn('https://api.nowpayments.io');
        $client->method('getHttpClient')->willReturn($httpClient);
        
        $service = new SubscriptionsService($client);
        $result = $service->updatePlanInterval('plan_123', 60);
        
        $this->assertEquals([
            'plan_id' => 'plan_123',
            'interval_day' => 60
        ], $result);
    }
} 